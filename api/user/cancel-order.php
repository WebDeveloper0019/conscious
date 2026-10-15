<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/notifications.php';
require_once __DIR__ . '/../lib/audit.php';
require_once __DIR__ . '/../lib/commerce.php';
require_once __DIR__ . '/../lib/idempotency.php';
require_once __DIR__ . '/../lib/rewards.php';

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
if ($orderNumber === '' || strlen($orderNumber) > 100) json_response(422, ['success' => false, 'message' => 'A valid order number is required.']);
if (strlen($reason) > 500) json_response(422, ['success' => false, 'message' => 'Reason must not exceed 500 characters.']);
if ($scope !== 'full') json_response(422, ['success' => false, 'message' => 'Item-level cancellation is not available yet. Cancel the full order or contact support.']);

$idem = begin_idempotent_request('order.cancel', ['type' => 'customer', 'id' => $userId], [
    'orderNumber' => $orderNumber, 'scope' => 'full', 'reason' => $reason,
]);
$db = get_db();

try {
    $db->beginTransaction();
    $orderStmt = $db->prepare('SELECT * FROM orders WHERE order_number=:number AND user_id=:user LIMIT 1 FOR UPDATE');
    $orderStmt->execute(['number' => $orderNumber, 'user' => $userId]);
    $order = $orderStmt->fetch();
    if (!$order) throw new DomainException('ORDER_NOT_FOUND');
    if (!in_array((string)$order['status'], ['payment_pending', 'confirmed'], true) || !empty($order['cancelled_at'])) {
        throw new DomainException('NOT_CANCELLABLE');
    }

    $placedAt = new DateTimeImmutable((string)($order['placed_at'] ?? $order['created_at']), new DateTimeZone('UTC'));
    $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
    if ($placedAt > $now || $placedAt->modify('+24 hours') < $now) throw new DomainException('WINDOW_EXPIRED');

    $packageStmt = $db->prepare(
        'SELECT COUNT(*) FROM shipment_packages p
         JOIN vendor_order_groups g ON g.id=p.vendor_group_id WHERE g.order_id=:order_id'
    );
    $packageStmt->execute(['order_id' => $order['id']]);
    if ((int)$packageStmt->fetchColumn() > 0) throw new DomainException('SHIPMENT_STARTED');

    $itemStmt = $db->prepare(
        'SELECT oi.*,f.vendor_net_amount FROM order_items oi
         LEFT JOIN order_item_financials f ON f.order_item_id=oi.id
         WHERE oi.order_id=:order_id ORDER BY oi.id FOR UPDATE'
    );
    $itemStmt->execute(['order_id' => $order['id']]);
    $items = $itemStmt->fetchAll();
    if (!$items) throw new DomainException('NO_ITEMS');
    foreach ($items as $item) {
        if (!in_array((string)$item['status'], ['awaiting_acceptance', 'accepted', 'processing'], true)) {
            throw new DomainException('ITEM_NOT_CANCELLABLE');
        }
    }

    $stockWasConverted = in_array((string)$order['payment_status'], ['paid', 'cod_pending'], true);
    foreach ($items as $item) {
        if ($stockWasConverted && !empty($item['variant_id'])) {
            $db->prepare(
                'UPDATE inventory_levels SET stock_quantity=stock_quantity+:qty,updated_at=UTC_TIMESTAMP() WHERE variant_id=:variant'
            )->execute(['qty' => (int)$item['quantity'], 'variant' => $item['variant_id']]);
            $level = $db->prepare('SELECT stock_quantity,reserved_quantity FROM inventory_levels WHERE variant_id=:variant');
            $level->execute(['variant' => $item['variant_id']]);
            $after = $level->fetch() ?: ['stock_quantity' => 0, 'reserved_quantity' => 0];
            $db->prepare(
                'INSERT INTO inventory_movements
                 (variant_id,vendor_id,movement_type,quantity_delta,reserved_delta,stock_after,reserved_after,
                  reference_type,reference_id,note,actor_type,actor_id,created_at)
                 VALUES (:variant,:vendor,"cancellation",:qty,0,:stock,:reserved,
                         "order_item",:reference,"Customer cancelled order","customer",:actor,UTC_TIMESTAMP())'
            )->execute([
                'variant' => $item['variant_id'], 'vendor' => $item['vendor_id'], 'qty' => (int)$item['quantity'],
                'stock' => $after['stock_quantity'], 'reserved' => $after['reserved_quantity'],
                'reference' => (string)$item['id'], 'actor' => $userId,
            ]);
        }
        if ($stockWasConverted && !empty($item['vendor_id']) && (float)($item['vendor_net_amount'] ?? 0) !== 0.0) {
            $db->prepare(
                'INSERT INTO vendor_ledger_entries
                 (id,vendor_id,entry_type,amount,currency,balance_bucket,reference_type,reference_id,description,available_at,created_at)
                 VALUES (:id,:vendor,"order_cancellation",:amount,"INR","reversed","order_item",:reference,:description,NULL,UTC_TIMESTAMP())'
            )->execute([
                'id' => uuid_string(), 'vendor' => $item['vendor_id'],
                'amount' => -abs((float)$item['vendor_net_amount']), 'reference' => (string)$item['id'],
                'description' => 'Cancellation reversal - order ' . $order['order_number'],
            ]);
        }
    }

    $db->prepare('UPDATE order_items SET status="cancelled",cancelled_quantity=quantity WHERE order_id=:order_id')->execute(['order_id' => $order['id']]);
    $db->prepare('UPDATE vendor_order_groups SET status="cancelled",updated_at=UTC_TIMESTAMP() WHERE order_id=:order_id')->execute(['order_id' => $order['id']]);
    $db->prepare(
        'UPDATE orders SET status="cancelled",
         payment_status=IF(payment_status="paid","paid","cancelled"),
         cancelled_at=UTC_TIMESTAMP(),cancelled_reason=:reason,updated_at=UTC_TIMESTAMP() WHERE id=:order_id'
    )->execute(['reason' => $reason !== '' ? $reason : null, 'order_id' => $order['id']]);
    $db->prepare('UPDATE payment_attempts SET status="cancelled",updated_at=UTC_TIMESTAMP() WHERE order_id=:order_id AND status<>"paid"')
       ->execute(['order_id' => $order['id']]);

    if (!$stockWasConverted && !empty($order['checkout_quote_id'])) release_quote_reservations((string)$order['checkout_quote_id']);
    $db->prepare(
        'UPDATE coupon_redemptions SET status="reversed",reversed_at=UTC_TIMESTAMP(),reversed_reason="order_cancelled"
         WHERE order_id=:order_id AND status="active"'
    )->execute(['order_id' => $order['id']]);
    reward_refund_for_order($userId, (int)$order['id'], (string)$order['order_number']);

    $refund = ['status' => 'not_applicable', 'amount' => 0.0];
    if ($order['payment_status'] === 'paid') {
        $existing = $db->prepare('SELECT id FROM refunds WHERE order_id=:order_id AND return_request_id IS NULL LIMIT 1 FOR UPDATE');
        $existing->execute(['order_id' => $order['id']]);
        if ($existing->fetchColumn() !== false) throw new DomainException('REFUND_EXISTS');
        $paidStmt = $db->prepare('SELECT COALESCE(SUM(amount),0) FROM payment_attempts WHERE order_id=:order_id AND status="paid"');
        $paidStmt->execute(['order_id' => $order['id']]);
        $amount = round(min((float)$order['total_amount'], (float)$paidStmt->fetchColumn()), 2);
        if ($amount > 0) {
            $refundId = uuid_string();
            $db->prepare(
                'INSERT INTO refunds
                 (id,return_request_id,order_id,amount,status,provider_reference,reason,recommended_by_type,recommended_by_id,created_at,updated_at)
                 VALUES (:id,NULL,:order_id,:amount,"recommended",NULL,:reason,"customer",:user,UTC_TIMESTAMP(),UTC_TIMESTAMP())'
            )->execute([
                'id' => $refundId, 'order_id' => $order['id'], 'amount' => $amount,
                'reason' => $reason !== '' ? $reason : 'Customer cancelled order', 'user' => $userId,
            ]);
            $refund = ['id' => $refundId, 'status' => 'recommended', 'amount' => $amount];
        }
    }

    record_status_change('order', (string)$order['id'], (string)$order['status'], 'cancelled', $reason !== '' ? $reason : 'Customer requested cancellation');
    audit_event('customer.order_cancelled', 'order', (string)$order['id'], ['order_number' => $order['order_number'], 'refund' => $refund]);
    create_notification('customer', $userId, 'order_update', 'Order cancellation confirmed',
        'Your order ' . $order['order_number'] . ' has been cancelled.',
        '/customer/order-details.html?orderNumber=' . urlencode((string)$order['order_number']),
        ['order_number' => $order['order_number'], 'refund' => $refund]);

    $response = ['success' => true, 'message' => 'Order cancelled successfully.', 'orderNumber' => $order['order_number'], 'status' => 'cancelled', 'refund' => $refund];
    finish_idempotent_request((int)$idem['id'], 200, $response);
    $db->commit();
    json_response(200, $response);
} catch (DomainException $e) {
    if ($db->inTransaction()) $db->rollBack();
    $messages = [
        'ORDER_NOT_FOUND' => [404, 'Order not found.'],
        'WINDOW_EXPIRED' => [409, 'The 24-hour cancellation window has passed.'],
        'SHIPMENT_STARTED' => [409, 'This order has entered fulfilment and can no longer be cancelled.'],
        'REFUND_EXISTS' => [409, 'A refund already exists for this order.'],
        'NO_ITEMS' => [409, 'No order items were found.'],
        'ITEM_NOT_CANCELLABLE' => [409, 'One or more items are no longer eligible for cancellation.'],
        'NOT_CANCELLABLE' => [409, 'This order is no longer eligible for cancellation.'],
    ];
    [$status, $message] = $messages[$e->getMessage()] ?? [409, 'Order cannot be cancelled.'];
    json_response($status, ['success' => false, 'message' => $message]);
} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    error_log('Customer cancellation error: ' . $e->getMessage());
    json_response(500, ['success' => false, 'message' => 'Unable to process the cancellation request.']);
}
