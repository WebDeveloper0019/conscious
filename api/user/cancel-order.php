<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/notifications.php';
require_once __DIR__ . '/../lib/audit.php';
require_once __DIR__ . '/../lib/commerce.php';
require_once __DIR__ . '/../lib/idempotency.php';
require_once __DIR__ . '/../lib/razorpay.php';
require_once __DIR__ . '/../lib/rewards.php';

send_cors_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

$payload = require_auth();
$userId  = auth_user_id($payload);
require_csrf();

if (rate_limit('cancel-order:' . $userId, 5, 300)) {
    json_response(429, ['success' => false, 'message' => 'Too many cancellation requests. Please try again later.']);
}

$body        = get_json_body();
$orderNumber = trim((string)($body['orderNumber'] ?? ''));
$reason      = trim((string)($body['reason'] ?? ''));
$scope       = strtolower(trim((string)($body['scope'] ?? 'full')));
$itemIdsRaw  = $body['itemIds'] ?? [];

// ── Input validation ──
if ($orderNumber === '' || strlen($orderNumber) > 100) {
    json_response(422, ['success' => false, 'message' => 'A valid order number is required.']);
}
if (strlen($reason) > 500) {
    json_response(422, ['success' => false, 'message' => 'Reason must not exceed 500 characters.']);
}
if (!in_array($scope, ['full', 'item'], true)) {
    json_response(422, ['success' => false, 'message' => 'Cancellation scope must be "full" or "item".']);
}

$itemIds = is_array($itemIdsRaw)
    ? array_values(array_unique(array_filter(array_map('intval', $itemIdsRaw), fn($v) => $v > 0)))
    : [];
if ($scope === 'item' && !$itemIds) {
    json_response(422, ['success' => false, 'message' => 'No items selected for cancellation.']);
}

// ── Idempotency: same key + same payload = same response ──
$idem = begin_idempotent_request(
    'order.cancel',
    ['type' => 'customer', 'id' => $userId],
    ['orderNumber' => $orderNumber, 'scope' => $scope, 'itemIds' => $itemIds, 'reason' => $reason]
);

$db = get_db();

try {
    $db->beginTransaction();

    // ────────── 1. Lock order row FIRST (serializes all cancels for this order) ──────────
    $stmt = $db->prepare('SELECT * FROM orders WHERE order_number = :num AND user_id = :uid LIMIT 1 FOR UPDATE');
    $stmt->execute(['num' => $orderNumber, 'uid' => $userId]);
    $order = $stmt->fetch();

    if (!$order) {
        $db->rollBack();
        json_response(404, ['success' => false, 'message' => 'Order not found.']);
    }

    $status        = strtolower((string)$order['status']);
    $paymentStatus = strtolower((string)$order['payment_status']);

    if ($status === 'cancelled' || !empty($order['cancelled_at'])) {
        $db->rollBack();
        json_response(409, ['success' => false, 'message' => 'This order has already been cancelled.']);
    }

    $cancellableStatuses = ['pending', 'processing', 'confirmed', 'awaiting_acceptance', 'accepted'];
    if (!in_array($status, $cancellableStatuses, true)) {
        $db->rollBack();
        json_response(409, ['success' => false, 'message' => 'This order is no longer eligible for cancellation. Please raise a return request.']);
    }

    // ────────── 2. Enforce 24h window ──────────
    $placedAt = $order['placed_at'] ?? $order['created_at'];
    try {
        $createdAt = new DateTimeImmutable((string)$placedAt, new DateTimeZone('UTC'));
        $now       = new DateTimeImmutable('now', new DateTimeZone('UTC'));
    } catch (Throwable $e) {
        $db->rollBack();
        json_response(409, ['success' => false, 'message' => 'Order placement time could not be verified.']);
    }
    if ($createdAt > $now || ($now->getTimestamp() - $createdAt->getTimestamp()) / 3600 > 24) {
        $db->rollBack();
        json_response(409, ['success' => false, 'message' => 'The 24-hour cancellation window has passed. Please raise a return after delivery.']);
    }

    // ────────── 3. Load & lock all items + financials ──────────
    $itemStmt = $db->prepare(
        'SELECT oi.*, f.vendor_net_amount
         FROM order_items oi
         LEFT JOIN order_item_financials f ON f.order_item_id = oi.id
         WHERE oi.order_id = :oid
         ORDER BY oi.id
         FOR UPDATE'
    );
    $itemStmt->execute(['oid' => $order['id']]);
    $allItems = $itemStmt->fetchAll();

    $itemsToCancel = [];
    foreach ($allItems as $it) {
        if (in_array($it['status'], ['cancelled', 'delivered', 'returned'], true)) continue;
        if ($scope === 'item' && !in_array((int)$it['id'], $itemIds, true)) continue;
        $itemsToCancel[] = $it;
    }
    if (!$itemsToCancel) {
        $db->rollBack();
        json_response(409, ['success' => false, 'message' => 'No eligible items found for cancellation.']);
    }

    // ────────── 4. Process each item atomically ──────────
    $refundAmount     = 0.0;
    $vendorReversals  = [];
    $cancelledItemIds = [];

    foreach ($itemsToCancel as $it) {
        $qty = (int)$it['quantity'];
        $refundAmount += (float)$it['line_total'];
        $cancelledItemIds[] = (int)$it['id'];

        // 4a. Stock restore — guard against double-restore via unique key
        if (!empty($it['variant_id'])) {
            // Restore inventory level
            $db->prepare(
                'UPDATE inventory_levels
                 SET stock_quantity = stock_quantity + :qty, updated_at = UTC_TIMESTAMP()
                 WHERE variant_id = :vid'
            )->execute(['qty' => $qty, 'vid' => $it['variant_id']]);

            // Snapshot after update
            $lvl = $db->prepare('SELECT stock_quantity, reserved_quantity FROM inventory_levels WHERE variant_id = :vid');
            $lvl->execute(['vid' => $it['variant_id']]);
            $after = $lvl->fetch() ?: ['stock_quantity' => 0, 'reserved_quantity' => 0];

            // Insert movement — unique key prevents duplicates from double-cancel
            try {
                $db->prepare(
                    'INSERT INTO inventory_movements
                       (variant_id, vendor_id, movement_type, quantity_delta, reserved_delta,
                        stock_after, reserved_after, reference_type, reference_id, note,
                        actor_type, actor_id, created_at)
                     VALUES
                       (:vid, :vendor, "cancellation", :qty, 0,
                        :stock, :reserved, "order_item", :ref, "Customer cancellation",
                        "customer", :actor, UTC_TIMESTAMP())'
                )->execute([
                    'vid'      => $it['variant_id'],
                    'vendor'   => $it['vendor_id'],
                    'qty'      => $qty,
                    'stock'    => $after['stock_quantity'],
                    'reserved' => $after['reserved_quantity'],
                    'ref'      => (string)$it['id'],
                    'actor'    => $userId,
                ]);
            } catch (PDOException $e) {
                if ((string)$e->getCode() === '23000') {
                    // Already restored — treat as idempotent success, skip
                    error_log('Skipping duplicate stock restore for item ' . $it['id']);
                } else {
                    throw $e;
                }
            }
        }

        // 4b. Accumulate vendor ledger reversal
        if (!empty($it['vendor_id']) && isset($it['vendor_net_amount']) && (float)$it['vendor_net_amount'] != 0.0) {
            $vendorReversals[$it['vendor_id']] =
                ($vendorReversals[$it['vendor_id']] ?? 0.0) + (float)$it['vendor_net_amount'];
        }

        // 4c. Mark item cancelled
        $db->prepare(
            'UPDATE order_items SET status = "cancelled", cancelled_quantity = quantity WHERE id = :id'
        )->execute(['id' => $it['id']]);
    }

    // ────────── 5. Vendor ledger — mark original reversed + insert reversal ──────────
    if ($vendorReversals) {
        $itemIdList = implode(',', array_map('intval', $cancelledItemIds));

        // 5a. Mark original order_earning entries as reversed (audit trail)
        $db->exec(
            "UPDATE vendor_ledger_entries
             SET status = 'reversed', reversed_at = UTC_TIMESTAMP()
             WHERE reference_type = 'order_item'
               AND reference_id IN ($itemIdList)
               AND entry_type = 'order_earning'
               AND status = 'active'"
        );

        // 5b. Insert reversal entries (unique key prevents dupes)
        foreach ($vendorReversals as $vendorId => $netAmount) {
            try {
                $db->prepare(
                    'INSERT INTO vendor_ledger_entries
                       (id, vendor_id, entry_type, amount, currency, balance_bucket,
                        status, reference_type, reference_id, description,
                        available_at, created_at)
                     VALUES
                       (:id, :vendor, "order_cancellation", :amt, "INR", "reversed",
                        "active", "order", :ref, :desc, NULL, UTC_TIMESTAMP())'
                )->execute([
                    'id'     => uuid_string(),
                    'vendor' => $vendorId,
                    'amt'    => -1 * abs($netAmount),
                    'ref'    => (string)$order['id'],
                    'desc'   => 'Cancellation reversal · order ' . $order['order_number'],
                ]);
            } catch (PDOException $e) {
                if ((string)$e->getCode() === '23000') {
                    error_log('Skipping duplicate ledger reversal for vendor ' . $vendorId);
                } else {
                    throw $e;
                }
            }
        }
    }

    // ────────── 6. Determine full vs partial ──────────
    $rem = $db->prepare('SELECT COUNT(*) FROM order_items WHERE order_id = :oid AND status NOT IN ("cancelled","returned")');
    $rem->execute(['oid' => $order['id']]);
    $isFullCancel = ((int)$rem->fetchColumn() === 0);

    // ────────── 7. Vendor group status update ──────────
    $affectedGroups = array_unique(array_filter(array_column($itemsToCancel, 'vendor_group_id')));
    foreach ($affectedGroups as $gid) {
        $gc = $db->prepare(
            'SELECT COUNT(*) AS total,
                    SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) AS cancelled_count
             FROM order_items WHERE vendor_group_id = :gid'
        );
        $gc->execute(['gid' => $gid]);
        $row = $gc->fetch();
        if ($row && (int)$row['total'] === (int)$row['cancelled_count']) {
            $db->prepare('UPDATE vendor_order_groups SET status = "cancelled", updated_at = UTC_TIMESTAMP() WHERE id = :gid')
               ->execute(['gid' => $gid]);
        }
    }

    // ────────── 8. Order status update ──────────
    if ($isFullCancel) {
        $db->prepare(
            'UPDATE orders
             SET status = "cancelled",
                 cancelled_at = UTC_TIMESTAMP(),
                 cancelled_reason = :reason,
                 updated_at = UTC_TIMESTAMP()
             WHERE id = :oid'
        )->execute(['reason' => $reason !== '' ? $reason : null, 'oid' => $order['id']]);
    } else {
        $db->prepare('UPDATE orders SET updated_at = UTC_TIMESTAMP() WHERE id = :oid')
           ->execute(['oid' => $order['id']]);
    }

    // ────────── 9. Coupon reversal — soft delete ──────────
    if ($isFullCancel) {
        $db->prepare(
            "UPDATE coupon_redemptions
             SET status = 'reversed', reversed_at = UTC_TIMESTAMP(), reversed_reason = 'order_cancelled'
             WHERE order_id = :oid AND status = 'active'"
        )->execute(['oid' => $order['id']]);
    }

    // ────────── 10. Release quote reservations (unpaid only) ──────────
    if ($paymentStatus !== 'paid' && !empty($order['checkout_quote_id'])) {
        try { release_quote_reservations((string)$order['checkout_quote_id']); }
        catch (Throwable $e) { error_log('release_quote_reservations failed: ' . $e->getMessage()); }
    }

    // ────────── 11. Rewards refund/clawback BEFORE refund API call ──────────
    if ($isFullCancel) {
        reward_refund_for_order($userId, (int)$order['id'], (string)$order['order_number']);
        reward_clawback_for_order($userId, (int)$order['id'], (string)$order['order_number']);
    }

    // ────────── 12. Refund row (intent) — BEFORE Razorpay call ──────────
    $refund = ['method' => 'none', 'status' => 'not_applicable', 'amount' => 0.0];

    if ($paymentStatus === 'paid') {
        // Cap refund to what was actually paid
        $paidStmt = $db->prepare(
            'SELECT COALESCE(SUM(amount), 0) FROM payment_attempts
             WHERE order_id = :oid AND status = "paid"'
        );
        $paidStmt->execute(['oid' => $order['id']]);
        $amountPaid = (float)$paidStmt->fetchColumn();

        // Subtract any already-refunded amount for this order
        $alreadyStmt = $db->prepare(
            'SELECT COALESCE(SUM(amount), 0) FROM refunds
             WHERE order_id = :oid AND source = "cancellation"
               AND status IN ("processing", "processed", "recommended")'
        );
        $alreadyStmt->execute(['oid' => $order['id']]);
        $alreadyRefunded = (float)$alreadyStmt->fetchColumn();

        $maxRefundable = max(0.0, $amountPaid - $alreadyRefunded);
        $refundAmount  = min($refundAmount, $maxRefundable);

        if ($refundAmount <= 0.0) {
            $refund = ['method' => 'none', 'status' => 'nothing_to_refund', 'amount' => 0.0];
        } else {
            $pay = $db->prepare(
                'SELECT * FROM payment_attempts
                 WHERE order_id = :oid AND status = "paid"
                 ORDER BY created_at DESC LIMIT 1'
            );
            $pay->execute(['oid' => $order['id']]);
            $payment = $pay->fetch();

            $refundId = uuid_string();

            if ($payment && $payment['provider'] === 'razorpay' && !empty($payment['provider_payment_id'])) {
                // Insert refund intent — unique key prevents duplicate cancel refund
                $db->prepare(
                    'INSERT INTO refunds
                       (id, return_request_id, order_id, order_item_id, amount, currency,
                        method, source, status, provider_payment_id, reason,
                        recommended_by_type, recommended_by_id, created_at, updated_at)
                     VALUES
                       (:id, NULL, :oid, NULL, :amt, "INR",
                        "razorpay", "cancellation", "processing", :ppid, :reason,
                        "customer", :uid, UTC_TIMESTAMP(), UTC_TIMESTAMP())'
                )->execute([
                    'id'     => $refundId,
                    'oid'    => $order['id'],
                    'amt'    => $refundAmount,
                    'ppid'   => $payment['provider_payment_id'],
                    'reason' => $reason !== '' ? $reason : 'Customer cancelled',
                    'uid'    => $userId,
                ]);

                $refund = ['method' => 'razorpay', 'status' => 'processing',
                           'amount' => $refundAmount, 'refundId' => $refundId];
            } else {
                // Manual refund for non-Razorpay paid orders
                $db->prepare(
                    'INSERT INTO refunds
                       (id, return_request_id, order_id, order_item_id, amount, currency,
                        method, source, status, reason,
                        recommended_by_type, recommended_by_id, created_at, updated_at)
                     VALUES
                       (:id, NULL, :oid, NULL, :amt, "INR",
                        "manual", "cancellation", "recommended", :reason,
                        "customer", :uid, UTC_TIMESTAMP(), UTC_TIMESTAMP())'
                )->execute([
                    'id'     => $refundId,
                    'oid'    => $order['id'],
                    'amt'    => $refundAmount,
                    'reason' => $reason !== '' ? $reason : 'Customer cancelled',
                    'uid'    => $userId,
                ]);
                $refund = ['method' => 'manual', 'status' => 'recommended',
                           'amount' => $refundAmount, 'refundId' => $refundId];
            }
        }
    } elseif ($paymentStatus === 'pending') {
        $refund = ['method' => 'cod', 'status' => 'not_applicable', 'amount' => 0.0];
    }

    // ────────── 13. Status history + audit ──────────
    record_status_change(
        'order',
        (string)$order['id'],
        $status,
        $isFullCancel ? 'cancelled' : 'partially_cancelled',
        $reason !== '' ? $reason : 'Customer requested cancellation'
    );

    audit_event('customer.order_cancelled', 'order', (string)$order['id'], [
        'order_number' => $order['order_number'],
        'scope'        => $scope,
        'item_ids'     => $cancelledItemIds,
        'reason'       => $reason,
        'refund'       => $refund,
    ]);

    // ────────── 14. Notifications ──────────
    create_notification(
        'customer',
        $userId,
        'order_update',
        'Order cancellation confirmed',
        'Your order ' . $order['order_number'] . ' has been cancelled.' .
            ($refund['amount'] > 0 ? ' A refund of ₹' . number_format($refund['amount'], 2) . ' has been initiated.' : ''),
        '/customer/order-details.html?orderNumber=' . urlencode((string)$order['order_number']),
        ['order_number' => $order['order_number'], 'refund' => $refund]
    );

    foreach ($vendorReversals as $vendorId => $net) {
        create_notification(
            'vendor',
            $vendorId,
            'order_cancelled',
            'Order cancelled',
            'Order ' . $order['order_number'] . ' was cancelled. Ledger reversal of ₹' . number_format(abs($net), 2) . ' applied.',
            '/vendor/orders.html',
            ['order_number' => $order['order_number'], 'reversal' => -1 * abs($net)]
        );
    }

    // ────────── 15. Commit BEFORE the Razorpay API call ──────────
    //     The refunds row is already written; the API call is a side-effect
    //     that we do after commit so the transaction isn't held open during
    //     a slow HTTP request.
    $response = [
        'success'        => true,
        'message'        => $isFullCancel ? 'Order cancelled successfully.' : 'Selected items cancelled.',
        'orderNumber'    => $order['order_number'],
        'status'         => $isFullCancel ? 'cancelled' : 'partially_cancelled',
        'cancelledItems' => $cancelledItemIds,
        'refund'         => $refund,
    ];

    finish_idempotent_request((int)$idem['id'], 200, $response);
    $db->commit();

    // ────────── 16. Razorpay refund API (post-commit side effect) ──────────
    if ($refund['method'] === 'razorpay' && !empty($refund['refundId'])) {
        try {
            $refundRow = $db->prepare('SELECT provider_payment_id FROM refunds WHERE id = :id');
            $refundRow->execute(['id' => $refund['refundId']]);
            $ppid = (string)$refundRow->fetchColumn();

            if ($ppid !== '' && razorpay_is_configured()) {
                $api = razorpay_request(
                    'POST',
                    'payments/' . $ppid . '/refund',
                    [
                        'amount' => (int)round($refund['amount'] * 100),
                        'notes'  => [
                            'order_number' => (string)$order['order_number'],
                            'reason'       => $reason !== '' ? $reason : 'Customer cancelled',
                        ],
                    ]
                );

                if (!empty($api['id'])) {
                    $db->prepare(
                        'UPDATE refunds
                         SET status = "processed",
                             provider_reference = :rid,
                             processed_at = UTC_TIMESTAMP(),
                             updated_at = UTC_TIMESTAMP()
                         WHERE id = :id'
                    )->execute(['rid' => $api['id'], 'id' => $refund['refundId']]);

                    $response['refund']['status'] = 'processed';
                    $response['refund']['providerRefundId'] = $api['id'];
                }
            }
        } catch (Throwable $e) {
            // Refund API failed — mark row failed, notify admin, but DO NOT roll back the cancel
            error_log('Razorpay refund failed for order ' . $order['order_number'] . ': ' . $e->getMessage());

            $db->prepare(
                'UPDATE refunds
                 SET status = "failed", failure_message = :msg, updated_at = UTC_TIMESTAMP()
                 WHERE id = :id'
            )->execute([
                'msg' => mb_substr($e->getMessage(), 0, 500),
                'id'  => $refund['refundId'],
            ]);

            $response['refund']['status']  = 'failed';
            $response['refund']['message'] = 'Refund will be processed manually by support.';

            // Notify admin
            create_notification(
                'admin',
                '00000000-0000-0000-0000-000000000000',
                'refund_failed',
                'Razorpay refund failed',
                'Order ' . $order['order_number'] . ' refund failed: ' . $e->getMessage(),
                null,
                ['order_id' => $order['id'], 'refund_id' => $refund['refundId']]
            );
        }
    }

    json_response(200, $response);

} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    error_log('Customer cancellation error: ' . $e->getMessage() . ' :: ' . $e->getTraceAsString());
    json_response(500, ['success' => false, 'message' => 'Unable to process the cancellation request.']);
}