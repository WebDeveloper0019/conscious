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
require_once __DIR__ . '/../lib/refund-queue.php';

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
$itemsRaw    = $body['items'] ?? null;   // [{"itemId":123, "quantity":2}, ...]

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

/**
 * Normalize items input:
 *   [
 *     {"itemId": 123, "quantity": 2},
 *     {"itemId": 456}                     // no quantity → cancel full quantity
 *   ]
 * Returns map: [itemId => quantity|null]
 */
$requestedItems = [];
if ($scope === 'item') {
    if (!is_array($itemsRaw) || !$itemsRaw) {
        json_response(422, ['success' => false, 'message' => 'No items selected for cancellation.']);
    }
    foreach ($itemsRaw as $entry) {
        if (!is_array($entry) || !isset($entry['itemId'])) continue;
        $iid = (int)$entry['itemId'];
        if ($iid <= 0) continue;
        $qty = isset($entry['quantity']) ? (int)$entry['quantity'] : null;
        if ($qty !== null && $qty < 1) {
            json_response(422, ['success' => false, 'message' => "Invalid quantity for item {$iid}."]);
        }
        $requestedItems[$iid] = $qty;   // null = cancel all remaining
    }
    if (!$requestedItems) {
        json_response(422, ['success' => false, 'message' => 'No valid items selected for cancellation.']);
    }
}

// ── Idempotency ──
$idem = begin_idempotent_request(
    'order.cancel',
    ['type' => 'customer', 'id' => $userId],
    [
        'orderNumber' => $orderNumber,
        'scope'       => $scope,
        'items'       => $requestedItems,
        'reason'      => $reason,
    ]
);

$db = get_db();

try {
    $db->beginTransaction();

    // ────────── 1. Lock order ──────────
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

    // ────────── 2. 24h window ──────────
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

    // ────────── 3. Load + lock items ──────────
    $itemStmt = $db->prepare(
        'SELECT oi.*, f.vendor_net_amount, f.gross_amount
         FROM order_items oi
         LEFT JOIN order_item_financials f ON f.order_item_id = oi.id
         WHERE oi.order_id = :oid
         ORDER BY oi.id
         FOR UPDATE'
    );
    $itemStmt->execute(['oid' => $order['id']]);
    $allItems = $itemStmt->fetchAll();

    // Build the cancel plan
    $cancelPlan = [];      // [['item' => row, 'quantity' => int, 'line_amount' => float, 'is_full' => bool]]
    $refundAmount = 0.0;
    $vendorReversals = [];
    $affectedItemIds = [];
    $isPartialQuantityCancel = false;

    foreach ($allItems as $it) {
        if (in_array($it['status'], ['cancelled', 'delivered', 'returned'], true)) continue;

        $alreadyCancelled = (int)$it['cancelled_quantity'];
        $remaining = (int)$it['quantity'] - $alreadyCancelled;
        if ($remaining <= 0) continue;

        // Determine requested quantity for this item
        $requestQty = null;
        if ($scope === 'full') {
            $requestQty = $remaining;   // everything left
        } else {
            if (!array_key_exists((int)$it['id'], $requestedItems)) continue;
            $requestedQty = $requestedItems[(int)$it['id']];
            $requestQty = ($requestedQty === null) ? $remaining : min($requestedQty, $remaining);
        }

        if ($requestQty <= 0) continue;
        if ($requestQty > $remaining) $requestQty = $remaining;

        // Compute proportional refund amount for this item's cancelled portion
        $lineTotal   = (float)$it['line_total'];
        $unitLineAmt = $remaining > 0 ? $lineTotal / $remaining : 0.0;
        $lineRefund  = round($unitLineAmt * $requestQty, 2);

        $isFullItem = ($requestQty === $remaining) && ($alreadyCancelled === 0);
        if (!$isFullItem) $isPartialQuantityCancel = true;

        $refundAmount += $lineRefund;
        $affectedItemIds[] = (int)$it['id'];

        // Vendor ledger reversal proportional to quantity
        $netAmount = (float)($it['vendor_net_amount'] ?? 0);
        if ($netAmount > 0 && (int)$it['quantity'] > 0) {
            $netPerUnit = $netAmount / (int)$it['quantity'];
            $vendorReversals[(string)$it['vendor_id']] =
                ($vendorReversals[(string)$it['vendor_id']] ?? 0.0) + ($netPerUnit * $requestQty);
        }

        $cancelPlan[] = [
            'item'        => $it,
            'quantity'    => $requestQty,
            'line_amount' => $lineRefund,
            'is_full'     => $isFullItem,
        ];
    }

    if (!$cancelPlan) {
        $db->rollBack();
        json_response(409, ['success' => false, 'message' => 'No eligible items found for cancellation.']);
    }

    // ────────── 4. Apply item-level changes ──────────
    foreach ($cancelPlan as $entry) {
        $it  = $entry['item'];
        $qty = $entry['quantity'];

        // 4a. Stock restore for the cancelled quantity
        if (!empty($it['variant_id'])) {
            $db->prepare(
                'UPDATE inventory_levels
                 SET stock_quantity = stock_quantity + ?, updated_at = UTC_TIMESTAMP()
                 WHERE variant_id = ?'
            )->execute([$qty, $it['variant_id']]);

            $lvl = $db->prepare('SELECT stock_quantity, reserved_quantity FROM inventory_levels WHERE variant_id = ?');
            $lvl->execute([$it['variant_id']]);
            $after = $lvl->fetch() ?: ['stock_quantity' => 0, 'reserved_quantity' => 0];

                      try {
                $db->prepare(
                    'INSERT INTO inventory_movements
                       (variant_id, vendor_id, movement_type, quantity_delta, reserved_delta,
                        stock_after, reserved_after, reference_type, reference_id, note,
                        actor_type, actor_id, created_at)
                     VALUES (?, ?, "cancellation", ?, 0, ?, ?, "order_item", ?, ?, "customer", ?, UTC_TIMESTAMP())'
                )->execute([
                    $it['variant_id'],
                    $it['vendor_id'],
                    $qty,
                    $after['stock_quantity'],
                    $after['reserved_quantity'],
                    (string)$it['id'],
                    "Partial qty cancel ({$qty} of " . (int)$it['quantity'] . ")",
                    $userId,
                ]);
                $movementId = (int)$db->lastInsertId();

                // Anomaly check — cancellation is legit, but this catches
                // cases where cancel runs twice or stock has drifted
                require_once __DIR__ . '/../lib/inventory-anomaly.php';
                inventory_check_anomaly(
                    (string)$it['variant_id'],
                    (string)$it['vendor_id'],
                    'cancellation',
                    $qty,
                    (int)$after['stock_quantity'],
                    $movementId,
                    (int)$after['stock_quantity'] - $qty
                );
            } catch (PDOException $e) {
                if ((string)$e->getCode() !== '23000') throw $e;
            }
        }

        // 4b. Update order_item
        $newCancelledQty = (int)$it['cancelled_quantity'] + $qty;
        $newCancelledAmt = (float)($it['cancelled_amount'] ?? 0) + (float)$entry['line_amount'];
        $newStatus = $newCancelledQty >= (int)$it['quantity'] ? 'cancelled' : $it['status'];

        $db->prepare(
            'UPDATE order_items
             SET status = ?,
                 cancelled_quantity = ?,
                 cancelled_amount = ?
             WHERE id = ?'
        )->execute([$newStatus, $newCancelledQty, $newCancelledAmt, $it['id']]);
    }

    // ────────── 5. Vendor ledger reversals ──────────
    foreach ($vendorReversals as $vendorId => $netAmount) {
        if ($netAmount <= 0) continue;
        try {
            $db->prepare(
                'INSERT INTO vendor_ledger_entries
                   (id, vendor_id, entry_type, amount, currency, balance_bucket,
                    status, reference_type, reference_id, description, available_at, created_at)
                 VALUES (?, ?, "order_cancellation", ?, "INR", "reversed",
                         "active", "order", ?, ?, NULL, UTC_TIMESTAMP())'
            )->execute([
                uuid_string(),
                $vendorId,
                -1 * abs($netAmount),
                (string)$order['id'],
                'Cancellation reversal · order ' . $order['order_number'] . ' · ' . count($cancelPlan) . ' item(s)',
            ]);
        } catch (PDOException $e) {
            if ((string)$e->getCode() !== '23000') throw $e;
        }
    }

    // Mark original earning entries as reversed (only for fully cancelled items)
    foreach ($cancelPlan as $entry) {
        if ($entry['is_full']) {
            $db->prepare(
                'UPDATE vendor_ledger_entries
                 SET status = "reversed", reversed_at = UTC_TIMESTAMP()
                 WHERE reference_type = "order_item"
                   AND reference_id = ?
                   AND entry_type = "order_earning"
                   AND status = "active"'
            )->execute([(string)$entry['item']['id']]);
        }
    }

    // ────────── 6. Determine full vs partial ──────────
    $rem = $db->prepare('SELECT COUNT(*) FROM order_items WHERE order_id = ? AND status NOT IN ("cancelled","returned")');
    $rem->execute([$order['id']]);
    $isFullCancel = ((int)$rem->fetchColumn() === 0);

    // ────────── 7. Shipping refund — only on full order cancel ──────────
    if ($isFullCancel && (float)$order['shipping_amount'] > 0) {
        $refundAmount += (float)$order['shipping_amount'];
    }

    // ────────── 8. Vendor group status ──────────
    $affectedGroups = array_unique(array_filter(array_map(fn($e) => $e['item']['vendor_group_id'], $cancelPlan)));
    foreach ($affectedGroups as $gid) {
        $gc = $db->prepare(
            'SELECT COUNT(*) AS total,
                    SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) AS cancelled_count
             FROM order_items WHERE vendor_group_id = ?'
        );
        $gc->execute([$gid]);
        $row = $gc->fetch();
        if ($row && (int)$row['total'] === (int)$row['cancelled_count']) {
            $db->prepare('UPDATE vendor_order_groups SET status = "cancelled", updated_at = UTC_TIMESTAMP() WHERE id = ?')
               ->execute([$gid]);
        }
    }

    // ────────── 9. Order status ──────────
    $newOrderStatus = $isFullCancel ? 'cancelled' : 'partially_cancelled';
    if ($isFullCancel) {
        $db->prepare(
            'UPDATE orders
             SET status = "cancelled",
                 cancelled_at = UTC_TIMESTAMP(),
                 cancelled_reason = ?,
                 updated_at = UTC_TIMESTAMP()
             WHERE id = ?'
        )->execute([$reason !== '' ? $reason : null, $order['id']]);
    } else {
        $db->prepare('UPDATE orders SET status = "partially_cancelled", updated_at = UTC_TIMESTAMP() WHERE id = ?')
           ->execute([$order['id']]);
    }

    // ────────── 10. Coupon reversal — only on full cancel ──────────
    if ($isFullCancel) {
        $db->prepare(
            'UPDATE coupon_redemptions
             SET status = "reversed", reversed_at = UTC_TIMESTAMP(), reversed_reason = "order_cancelled"
             WHERE order_id = ? AND status = "active"'
        )->execute([$order['id']]);
    }

    // ────────── 11. Release quote reservations (unpaid only) ──────────
    if ($paymentStatus !== 'paid' && !empty($order['checkout_quote_id'])) {
        try { release_quote_reservations((string)$order['checkout_quote_id']); }
        catch (Throwable $e) { error_log('release_quote_reservations failed: ' . $e->getMessage()); }
    }

    // ────────── 12. Rewards — only on full cancel ──────────
    if ($isFullCancel) {
        reward_refund_for_order($userId, (int)$order['id'], (string)$order['order_number']);
        reward_clawback_for_order($userId, (int)$order['id'], (string)$order['order_number']);
    }

    // ────────── 13. Refund record ──────────
    $refund = ['method' => 'none', 'status' => 'not_applicable', 'amount' => 0.0];

    if ($paymentStatus === 'paid' && $refundAmount > 0) {
        // Cap
        $paidStmt = $db->prepare('SELECT COALESCE(SUM(amount),0) FROM payment_attempts WHERE order_id = ? AND status = "paid"');
        $paidStmt->execute([$order['id']]);
        $amountPaid = (float)$paidStmt->fetchColumn();

        $alreadyStmt = $db->prepare(
            'SELECT COALESCE(SUM(amount),0) FROM refunds
             WHERE order_id = ? AND source = "cancellation"
               AND status IN ("processing","processed","recommended")'
        );
        $alreadyStmt->execute([$order['id']]);
        $alreadyRefunded = (float)$alreadyStmt->fetchColumn();

        $maxRefundable = max(0.0, $amountPaid - $alreadyRefunded);
        $refundAmount = min($refundAmount, $maxRefundable);

        if ($refundAmount > 0) {
            $pay = $db->prepare('SELECT * FROM payment_attempts WHERE order_id = ? AND status = "paid" ORDER BY created_at DESC LIMIT 1');
            $pay->execute([$order['id']]);
            $payment = $pay->fetch();

            $refundId = uuid_string();
            $details = [
                'items' => array_map(fn($e) => [
                    'item_id'  => (int)$e['item']['id'],
                    'name'     => $e['item']['product_name'],
                    'quantity' => (int)$e['quantity'],
                    'amount'   => (float)$e['line_amount'],
                ], $cancelPlan),
                'shipping_refunded' => $isFullCancel ? (float)$order['shipping_amount'] : 0,
                'scope'             => $isFullCancel ? 'full' : 'partial',
            ];

            if ($payment && $payment['provider'] === 'razorpay' && !empty($payment['provider_payment_id'])) {
                $db->prepare(
                    'INSERT INTO refunds
                       (id, order_id, order_item_id, amount, currency, method, source, status,
                        provider_payment_id, reason, details_json,
                        recommended_by_type, recommended_by_id, created_at, updated_at)
                     VALUES (?, ?, NULL, ?, "INR", "razorpay", "cancellation", "processing",
                             ?, ?, ?,
                             "customer", ?, UTC_TIMESTAMP(), UTC_TIMESTAMP())'
                )->execute([
                    $refundId,
                    $order['id'],
                    $refundAmount,
                    $payment['provider_payment_id'],
                    $reason !== '' ? $reason : 'Customer cancelled',
                    json_encode($details, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    $userId,
                ]);

                // Enqueue async retry job
                refund_queue_enqueue($refundId, (int)$order['id']);

                $refund = [
                    'method'   => 'razorpay',
                    'status'   => 'processing',
                    'amount'   => $refundAmount,
                    'refundId' => $refundId,
                ];
            } else {
                $db->prepare(
                    'INSERT INTO refunds
                       (id, order_id, order_item_id, amount, currency, method, source, status,
                        reason, details_json,
                        recommended_by_type, recommended_by_id, created_at, updated_at)
                     VALUES (?, ?, NULL, ?, "INR", "manual", "cancellation", "recommended",
                             ?, ?,
                             "customer", ?, UTC_TIMESTAMP(), UTC_TIMESTAMP())'
                )->execute([
                    $refundId,
                    $order['id'],
                    $refundAmount,
                    $reason !== '' ? $reason : 'Customer cancelled',
                    json_encode($details, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    $userId,
                ]);
                $refund = [
                    'method'   => 'manual',
                    'status'   => 'recommended',
                    'amount'   => $refundAmount,
                    'refundId' => $refundId,
                ];
            }
        }
    } elseif ($paymentStatus === 'pending') {
        $refund = ['method' => 'cod', 'status' => 'not_applicable', 'amount' => 0.0];
    }

    // ────────── 14. Status history + audit ──────────
    record_status_change(
        'order',
        (string)$order['id'],
        $status,
        $newOrderStatus,
        $reason !== '' ? $reason : 'Customer requested cancellation'
    );

    audit_event('customer.order_cancelled', 'order', (string)$order['id'], [
        'order_number'    => $order['order_number'],
        'scope'           => $isFullCancel ? 'full' : 'partial',
        'quantity_level'  => $isPartialQuantityCancel,
        'items'           => array_map(fn($e) => [
            'item_id'  => (int)$e['item']['id'],
            'quantity' => (int)$e['quantity'],
        ], $cancelPlan),
        'reason'          => $reason,
        'refund'          => $refund,
    ]);

    // ────────── 15. Notifications ──────────
    create_notification(
        'customer',
        $userId,
        'order_update',
        $isFullCancel ? 'Order cancelled' : 'Partial cancellation confirmed',
        $isFullCancel
            ? 'Your order ' . $order['order_number'] . ' has been cancelled.'
              . ($refund['amount'] > 0 ? ' Refund of ₹' . number_format($refund['amount'], 2) . ' initiated.' : '')
            : count($cancelPlan) . ' item(s) cancelled from order ' . $order['order_number'] . '.'
              . ($refund['amount'] > 0 ? ' Refund of ₹' . number_format($refund['amount'], 2) . ' initiated.' : ''),
        '/customer/order-details.html?orderNumber=' . urlencode((string)$order['order_number']),
        ['order_number' => $order['order_number'], 'refund' => $refund]
    );

    foreach ($vendorReversals as $vendorId => $net) {
        if ($net <= 0) continue;
        create_notification(
            'vendor',
            $vendorId,
            'order_cancelled',
            $isFullCancel ? 'Order cancelled' : 'Partial cancellation',
            'Order ' . $order['order_number'] . ': reversal of ₹' . number_format($net, 2) . ' applied.',
            '/vendor/orders.html',
            ['order_number' => $order['order_number'], 'reversal' => -1 * abs($net)]
        );
    }

    // ────────── 16. Commit ──────────
    $response = [
        'success'        => true,
        'message'        => $isFullCancel ? 'Order cancelled successfully.' : 'Selected items cancelled.',
        'orderNumber'    => $order['order_number'],
        'status'         => $newOrderStatus,
        'cancelledItems' => array_map(fn($e) => [
            'itemId'   => (int)$e['item']['id'],
            'quantity' => (int)$e['quantity'],
            'amount'   => (float)$e['line_amount'],
        ], $cancelPlan),
        'refund'         => $refund,
        'async'          => $refund['method'] === 'razorpay',
    ];

    finish_idempotent_request((int)$idem['id'], 200, $response);
    $db->commit();

    // ────────── 17. Try refund inline; worker will retry on failure ──────────
    if ($refund['method'] === 'razorpay' && !empty($refund['refundId'])) {
        try {
            $refundRow = $db->prepare('SELECT provider_payment_id FROM refunds WHERE id = ?');
            $refundRow->execute([$refund['refundId']]);
            $ppid = (string)$refundRow->fetchColumn();

            if ($ppid !== '' && razorpay_is_configured()) {
                $api = razorpay_request(
                    'POST',
                    'payments/' . $ppid . '/refund',
                    [
                        'amount' => (int)round($refund['amount'] * 100),
                        'notes'  => [
                            'order_number' => $order['order_number'],
                            'reason'       => $reason !== '' ? $reason : 'Customer cancelled',
                            'source'       => 'inline_attempt',
                        ],
                    ]
                );

                               if (!empty($api['id'])) {
                    $db->prepare(
                        'UPDATE refunds SET status = "processed", provider_reference = ?, processed_at = UTC_TIMESTAMP(), updated_at = UTC_TIMESTAMP() WHERE id = ?'
                    )->execute([$api['id'], $refund['refundId']]);

                    $db->prepare(
                        'UPDATE refund_jobs SET status = "succeeded", completed_at = UTC_TIMESTAMP(), updated_at = UTC_TIMESTAMP() WHERE refund_id = ?'
                    )->execute([$refund['refundId']]);

                    // Generate GST credit note now that refund succeeded
                    try {
                        require_once __DIR__ . '/../lib/credit-note.php';
                        credit_note_generate_for_refund((string)$refund['refundId']);
                    } catch (Throwable $cnErr) {
                        error_log('Credit note gen failed (inline): ' . $cnErr->getMessage());
                    }

                    $response['refund']['status'] = 'processed';
                    $response['refund']['providerRefundId'] = $api['id'];
                    $response['async'] = false;
                }
            }
        } catch (Throwable $e) {
            // Inline failed — worker will pick it up. Just log.
            error_log('Inline refund attempt failed (queued for retry): ' . $e->getMessage());
        }
    }

    json_response(200, $response);

} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    error_log('Customer cancellation error: ' . $e->getMessage() . ' :: ' . $e->getTraceAsString());
    json_response(500, ['success' => false, 'message' => 'Unable to process the cancellation request.']);
}