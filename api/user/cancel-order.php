<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/notifications.php';
require_once __DIR__ . '/../lib/audit.php';
require_once __DIR__ . '/../lib/commerce.php';
require_once __DIR__ . '/../lib/idempotency.php';
require_once __DIR__ . '/../lib/rewards.php';
require_once __DIR__ . '/../lib/refund-queue.php';
require_once __DIR__ . '/../lib/inventory-anomaly.php';

send_cors_headers();
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
$auth = require_auth();
$userId = auth_user_id($auth);
require_csrf();
if (rate_limit('cancel-order:' . $userId, 5, 300)) json_response(429, ['success' => false, 'message' => 'Too many cancellation requests.']);

$body = get_json_body();
$orderNumber = trim((string)($body['orderNumber'] ?? ''));
$reason = trim((string)($body['reason'] ?? ''));
$scope = strtolower(trim((string)($body['scope'] ?? 'full')));
$requestedItems = $body['items'] ?? [];
if ($orderNumber === '' || strlen($orderNumber) > 100) json_response(422, ['success' => false, 'message' => 'A valid order number is required.']);
if (strlen($reason) > 500) json_response(422, ['success' => false, 'message' => 'Reason must not exceed 500 characters.']);
if (!in_array($scope, ['full', 'item'], true)) json_response(422, ['success' => false, 'message' => 'Scope must be full or item.']);
if ($scope === 'item' && (!is_array($requestedItems) || !$requestedItems)) json_response(422, ['success' => false, 'message' => 'At least one item is required.']);

$normalizedItems = [];
if ($scope === 'item') {
    foreach ($requestedItems as $requested) {
        if (!is_array($requested) || !isset($requested['itemId']) || !ctype_digit((string)$requested['itemId'])) {
            json_response(422, ['success' => false, 'message' => 'Each item requires a valid itemId.']);
        }
        $itemId = (int)$requested['itemId'];
        $quantity = array_key_exists('quantity', $requested) ? (int)$requested['quantity'] : null;
        if ($itemId < 1 || ($quantity !== null && $quantity < 1)) json_response(422, ['success' => false, 'message' => 'Item quantities must be positive.']);
        if (isset($normalizedItems[$itemId])) json_response(422, ['success' => false, 'message' => 'Duplicate itemId supplied.']);
        $normalizedItems[$itemId] = $quantity;
    }
    ksort($normalizedItems);
}

$idem = begin_idempotent_request('order.cancel', ['type' => 'customer', 'id' => $userId], [
    'orderNumber' => $orderNumber,
    'scope' => $scope,
    'reason' => $reason,
    'items' => $normalizedItems,
]);
$db = get_db();

try {
    $db->beginTransaction();
    $orderStmt = $db->prepare('SELECT * FROM orders WHERE order_number=:number AND user_id=:user LIMIT 1 FOR UPDATE');
    $orderStmt->execute(['number' => $orderNumber, 'user' => $userId]);
    $order = $orderStmt->fetch();
    if (!$order) throw new DomainException('ORDER_NOT_FOUND');
    if (!in_array((string)$order['status'], ['payment_pending', 'confirmed', 'partially_cancelled'], true) || !empty($order['cancelled_at'])) {
        throw new DomainException('NOT_CANCELLABLE');
    }

    $placedAt = new DateTimeImmutable((string)($order['placed_at'] ?? $order['created_at']), new DateTimeZone('UTC'));
    $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
    if ($placedAt > $now || $placedAt->modify('+24 hours') < $now) throw new DomainException('WINDOW_EXPIRED');

    $packageStmt = $db->prepare('SELECT COUNT(*) FROM shipment_packages p JOIN vendor_order_groups g ON g.id=p.vendor_group_id WHERE g.order_id=:order_id');
    $packageStmt->execute(['order_id' => $order['id']]);
    if ((int)$packageStmt->fetchColumn() > 0) throw new DomainException('SHIPMENT_STARTED');

    $itemStmt = $db->prepare(
        'SELECT oi.*, f.vendor_net_amount FROM order_items oi
         LEFT JOIN order_item_financials f ON f.order_item_id=oi.id
         WHERE oi.order_id=:order_id ORDER BY oi.id FOR UPDATE'
    );
    $itemStmt->execute(['order_id' => $order['id']]);
    $items = $itemStmt->fetchAll();
    if (!$items) throw new DomainException('NO_ITEMS');
    if ($scope === 'item' && !in_array((string)$order['payment_status'], ['paid', 'cod_pending'], true)) {
        throw new DomainException('PARTIAL_PAYMENT_PENDING');
    }

    $itemsById = [];
    foreach ($items as $item) $itemsById[(int)$item['id']] = $item;
    foreach (array_keys($normalizedItems) as $itemId) {
        if (!isset($itemsById[$itemId])) throw new DomainException('ITEM_NOT_FOUND');
    }

    $plan = [];
    $refundForItems = 0.0;
    foreach ($items as $item) {
        $itemId = (int)$item['id'];
        if ($scope === 'item' && !array_key_exists($itemId, $normalizedItems)) continue;
        if (!in_array((string)$item['status'], ['awaiting_acceptance', 'accepted', 'processing'], true)) throw new DomainException('ITEM_NOT_CANCELLABLE');
        $remaining = (int)$item['quantity'] - (int)$item['cancelled_quantity'] - (int)$item['returned_quantity'];
        if ($remaining < 1) throw new DomainException('ITEM_NOT_CANCELLABLE');
        $quantity = $scope === 'full' ? $remaining : ($normalizedItems[$itemId] ?? $remaining);
        if ($quantity < 1 || $quantity > $remaining) throw new DomainException('INVALID_QUANTITY');
        $lineRefund = round(((float)$item['line_total'] / max(1, (int)$item['quantity'])) * $quantity, 2);
        $vendorReversal = round(((float)($item['vendor_net_amount'] ?? 0) / max(1, (int)$item['quantity'])) * $quantity, 2);
        $plan[] = ['row' => $item, 'quantity' => $quantity, 'lineRefund' => $lineRefund, 'vendorReversal' => $vendorReversal];
        $refundForItems += $lineRefund;
    }
    if (!$plan) throw new DomainException('NO_ITEMS');

    $stockWasConverted = in_array((string)$order['payment_status'], ['paid', 'cod_pending'], true);
    $cancelledResponseItems = [];
    foreach ($plan as $entry) {
        $item = $entry['row'];
        $quantity = (int)$entry['quantity'];
        $newCancelled = (int)$item['cancelled_quantity'] + $quantity;
        $fullyCancelled = $newCancelled + (int)$item['returned_quantity'] >= (int)$item['quantity'];
        $referenceId = (string)$order['id'] . ':' . (string)$item['id'] . ':' . $newCancelled;

        if ($stockWasConverted && !empty($item['variant_id'])) {
            $levelStmt = $db->prepare('SELECT stock_quantity,reserved_quantity FROM inventory_levels WHERE variant_id=:variant FOR UPDATE');
            $levelStmt->execute(['variant' => $item['variant_id']]);
            $before = $levelStmt->fetch();
            if (!$before) throw new DomainException('INVENTORY_MISSING');
            $db->prepare('UPDATE inventory_levels SET stock_quantity=stock_quantity+:qty,updated_at=UTC_TIMESTAMP() WHERE variant_id=:variant')
               ->execute(['qty' => $quantity, 'variant' => $item['variant_id']]);
            $stockAfter = (int)$before['stock_quantity'] + $quantity;
            $movementStmt = $db->prepare(
                'INSERT INTO inventory_movements
                 (variant_id,vendor_id,movement_type,quantity_delta,reserved_delta,stock_after,reserved_after,
                  reference_type,reference_id,note,actor_type,actor_id,created_at)
                 VALUES (:variant,:vendor,"cancellation",:qty,0,:stock,:reserved,
                         "order_cancellation",:reference,"Customer cancellation","customer",:actor,UTC_TIMESTAMP())'
            );
            $movementStmt->execute([
                'variant' => $item['variant_id'], 'vendor' => $item['vendor_id'], 'qty' => $quantity,
                'stock' => $stockAfter, 'reserved' => (int)$before['reserved_quantity'],
                'reference' => $referenceId, 'actor' => $userId,
            ]);
            inventory_check_anomaly((string)$item['variant_id'], $item['vendor_id'] ? (string)$item['vendor_id'] : null,
                'cancellation', $quantity, $stockAfter, (int)$db->lastInsertId(), (int)$before['stock_quantity']);
        }

        if ($stockWasConverted && !empty($item['vendor_id']) && (float)$entry['vendorReversal'] > 0) {
            $db->prepare(
                'INSERT INTO vendor_ledger_entries
                 (id,vendor_id,entry_type,amount,currency,balance_bucket,reference_type,reference_id,description,available_at,created_at)
                 VALUES (:id,:vendor,"order_cancellation",:amount,"INR","reversed","order_cancellation",:reference,:description,NULL,UTC_TIMESTAMP())'
            )->execute([
                'id' => uuid_string(), 'vendor' => $item['vendor_id'], 'amount' => -abs((float)$entry['vendorReversal']),
                'reference' => $referenceId, 'description' => 'Cancellation reversal - order ' . $order['order_number'],
            ]);
        }

        $db->prepare(
            'UPDATE order_items SET cancelled_quantity=:quantity,cancelled_amount=cancelled_amount+:amount,
             status=IF(:fully=1,"cancelled",status) WHERE id=:id'
        )->execute(['quantity' => $newCancelled, 'amount' => $entry['lineRefund'], 'fully' => $fullyCancelled ? 1 : 0, 'id' => $item['id']]);
        $cancelledResponseItems[] = ['itemId' => $item['id'], 'quantity' => $quantity, 'amount' => $entry['lineRefund']];
    }

    $remainingStmt = $db->prepare('SELECT COUNT(*) FROM order_items WHERE order_id=:order_id AND cancelled_quantity+returned_quantity<quantity');
    $remainingStmt->execute(['order_id' => $order['id']]);
    $isFullCancellation = (int)$remainingStmt->fetchColumn() === 0;
    $newStatus = $isFullCancellation ? 'cancelled' : 'partially_cancelled';
    if ($isFullCancellation) {
        $refundForItems += (float)$order['shipping_amount'];
        $db->prepare('UPDATE vendor_order_groups SET status="cancelled",updated_at=UTC_TIMESTAMP() WHERE order_id=:order_id')->execute(['order_id' => $order['id']]);
        $db->prepare(
            'UPDATE orders SET status="cancelled",payment_status=IF(payment_status="paid","paid","cancelled"),
             cancelled_at=UTC_TIMESTAMP(),cancelled_reason=:reason,updated_at=UTC_TIMESTAMP() WHERE id=:order_id'
        )->execute(['reason' => $reason !== '' ? $reason : null, 'order_id' => $order['id']]);
        $db->prepare('UPDATE payment_attempts SET status="cancelled",updated_at=UTC_TIMESTAMP() WHERE order_id=:order_id AND status<>"paid"')
           ->execute(['order_id' => $order['id']]);
        if (!$stockWasConverted && !empty($order['checkout_quote_id'])) release_quote_reservations((string)$order['checkout_quote_id']);
        $db->prepare('UPDATE coupon_redemptions SET status="reversed",reversed_at=UTC_TIMESTAMP(),reversed_reason="order_cancelled" WHERE order_id=:order_id AND status="active"')
           ->execute(['order_id' => $order['id']]);
        reward_refund_for_order($userId, (int)$order['id'], (string)$order['order_number']);
    } else {
        $db->prepare('UPDATE orders SET status="partially_cancelled",cancelled_reason=:reason,updated_at=UTC_TIMESTAMP() WHERE id=:order_id')
           ->execute(['reason' => $reason !== '' ? $reason : null, 'order_id' => $order['id']]);
    }

    $refund = ['status' => 'not_applicable', 'amount' => 0.0];
    if ((string)$order['payment_status'] === 'paid') {
        $paidStmt = $db->prepare('SELECT COALESCE(SUM(amount),0) FROM payment_attempts WHERE order_id=:order_id AND status="paid"');
        $paidStmt->execute(['order_id' => $order['id']]);
        $paidAmount = (float)$paidStmt->fetchColumn();
        $priorStmt = $db->prepare('SELECT amount FROM refunds WHERE order_id=:order_id AND source="cancellation" AND status NOT IN ("rejected","cancelled") FOR UPDATE');
        $priorStmt->execute(['order_id' => $order['id']]);
        $priorAmount = array_sum(array_map(static fn(array $row): float => (float)$row['amount'], $priorStmt->fetchAll()));
        $refundable = max(0.0, round($paidAmount - $priorAmount, 2));
        $amount = round(min($refundForItems, $refundable), 2);
        if ($amount > 0) {
            $paymentStmt = $db->prepare('SELECT provider,provider_payment_id FROM payment_attempts WHERE order_id=:order_id AND status="paid" ORDER BY created_at DESC LIMIT 1');
            $paymentStmt->execute(['order_id' => $order['id']]);
            $payment = $paymentStmt->fetch() ?: [];
            $automatic = !empty($payment['provider_payment_id']);
            $refundId = uuid_string();
            $details = json_encode(['scope' => $scope, 'items' => $cancelledResponseItems], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $db->prepare(
                'INSERT INTO refunds
                 (id,return_request_id,order_id,amount,currency,method,source,status,provider_reference,provider_payment_id,
                  reason,details_json,recommended_by_type,recommended_by_id,created_at,updated_at)
                 VALUES (:id,NULL,:order_id,:amount,:currency,:method,"cancellation",:status,NULL,:provider_payment_id,
                         :reason,:details,"customer",:user,UTC_TIMESTAMP(),UTC_TIMESTAMP())'
            )->execute([
                'id' => $refundId, 'order_id' => $order['id'], 'amount' => $amount,
                'currency' => (string)($order['currency'] ?? 'INR'), 'method' => $automatic ? 'razorpay' : 'manual',
                'status' => $automatic ? 'processing' : 'recommended',
                'provider_payment_id' => $payment['provider_payment_id'] ?? null,
                'reason' => $reason !== '' ? $reason : 'Customer cancellation', 'details' => $details, 'user' => $userId,
            ]);
            if ($automatic) refund_queue_enqueue($refundId, (int)$order['id']);
            $refund = ['id' => $refundId, 'status' => $automatic ? 'processing' : 'recommended', 'amount' => $amount];
        }
    }

    record_status_change('order', (string)$order['id'], (string)$order['status'], $newStatus, $reason !== '' ? $reason : 'Customer cancellation');
    audit_event('customer.order_cancelled', 'order', (string)$order['id'], ['scope' => $scope, 'items' => $cancelledResponseItems, 'refund' => $refund]);
    create_notification('customer', $userId, 'order_update', 'Order cancellation confirmed',
        'Your cancellation for order ' . $order['order_number'] . ' has been recorded.',
        '/portals/modules/customer/orders.php?orderNumber=' . urlencode((string)$order['order_number']),
        ['order_number' => $order['order_number'], 'status' => $newStatus, 'refund' => $refund]);

    $response = [
        'success' => true, 'message' => $isFullCancellation ? 'Order cancelled successfully.' : 'Items cancelled successfully.',
        'orderNumber' => $order['order_number'], 'status' => $newStatus, 'scope' => $scope,
        'items' => $cancelledResponseItems, 'refund' => $refund,
    ];
    finish_idempotent_request((int)$idem['id'], 200, $response);
    $db->commit();
    json_response(200, $response);
} catch (DomainException $e) {
    if ($db->inTransaction()) $db->rollBack();
    $messages = [
        'ORDER_NOT_FOUND' => [404, 'Order not found.'],
        'WINDOW_EXPIRED' => [409, 'The 24-hour cancellation window has passed.'],
        'SHIPMENT_STARTED' => [409, 'This order has entered fulfilment and can no longer be cancelled.'],
        'NO_ITEMS' => [409, 'No cancellable order items were found.'],
        'ITEM_NOT_FOUND' => [404, 'One or more order items were not found.'],
        'ITEM_NOT_CANCELLABLE' => [409, 'One or more items are no longer eligible for cancellation.'],
        'INVALID_QUANTITY' => [422, 'A cancellation quantity exceeds the remaining item quantity.'],
        'PARTIAL_PAYMENT_PENDING' => [409, 'Item-level cancellation is unavailable until payment is completed.'],
        'INVENTORY_MISSING' => [409, 'Inventory could not be restored safely.'],
        'NOT_CANCELLABLE' => [409, 'This order is no longer eligible for cancellation.'],
    ];
    [$status, $message] = $messages[$e->getMessage()] ?? [409, 'Order cannot be cancelled.'];
    $response = ['success' => false, 'message' => $message];
    finish_idempotent_request((int)$idem['id'], $status, $response);
    json_response($status, $response);
} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    error_log('Customer cancellation error: ' . $e->getMessage());
    json_response(500, ['success' => false, 'message' => 'Unable to process the cancellation request.']);
}
