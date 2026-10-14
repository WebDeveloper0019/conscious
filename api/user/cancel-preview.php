<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/commerce.php';
send_cors_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

$payload = require_auth();
$userId  = auth_user_id($payload);
$db      = get_db();

$orderNumber = trim((string)($_GET['orderNumber'] ?? ''));
if ($orderNumber === '') json_response(422, ['success' => false, 'message' => 'Order number is required.']);

$stmt = $db->prepare('SELECT * FROM orders WHERE order_number = ? AND user_id = ? LIMIT 1');
$stmt->execute([$orderNumber, $userId]);
$order = $stmt->fetch();
if (!$order) json_response(404, ['success' => false, 'message' => 'Order not found.']);

$items = $db->prepare(
    'SELECT oi.id, oi.product_name, oi.quantity, oi.cancelled_quantity,
            oi.line_total, oi.status, oi.vendor_group_id,
            f.vendor_net_amount
     FROM order_items oi
     LEFT JOIN order_item_financials f ON f.order_item_id = oi.id
     WHERE oi.order_id = ?
     ORDER BY oi.id'
);
$items->execute([$order['id']]);
$rows = $items->fetchAll();

$refundFull = 0.0;
$itemsOut = [];
foreach ($rows as $r) {
    $alreadyCancelled = (int)$r['cancelled_quantity'];
    $remaining = (int)$r['quantity'] - $alreadyCancelled;
    $eligible  = $remaining > 0 && !in_array($r['status'], ['cancelled', 'delivered', 'returned'], true);

    $lineTotal = (float)$r['line_total'];
    $unitAmt   = $remaining > 0 ? $lineTotal / $remaining : 0.0;

    if ($eligible) $refundFull += $lineTotal;

    $itemsOut[] = [
        'itemId'              => (int)$r['id'],
        'name'                => (string)$r['product_name'],
        'quantity'            => (int)$r['quantity'],
        'alreadyCancelled'    => $alreadyCancelled,
        'remainingQuantity'   => $remaining,
        'lineTotal'           => $lineTotal,
        'unitAmount'          => round($unitAmt, 2),
        'maxRefundable'       => round($unitAmt * $remaining, 2),
        'status'              => $r['status'],
        'eligible'            => $eligible,
    ];
}

$shippingAmt = (float)$order['shipping_amount'];

$placedAt = $order['placed_at'] ?? $order['created_at'];
$deadline = (new DateTimeImmutable((string)$placedAt, new DateTimeZone('UTC')))->modify('+24 hours');
$now      = new DateTimeImmutable('now', new DateTimeZone('UTC'));

$canCancel = in_array(strtolower((string)$order['status']), ['pending','processing','confirmed','awaiting_acceptance','accepted'], true)
             && empty($order['cancelled_at'])
             && $now < $deadline;

json_response(200, [
    'success'               => true,
    'orderNumber'           => $order['order_number'],
    'status'                => $order['status'],
    'paymentStatus'         => $order['payment_status'],
    'canCancel'             => $canCancel,
    'cancelDeadline'        => $deadline->format(DateTimeInterface::ATOM),
    'supportsPartialQty'    => true,
    'estimatedRefund'       => round($refundFull + ($canCancel ? $shippingAmt : 0), 2),
    'estimatedRefundFull'   => round($refundFull + $shippingAmt, 2),
    'estimatedRefundItems'  => round($refundFull, 2),
    'shippingAmount'        => round($shippingAmt, 2),
    'refundMethod'          => $order['payment_status'] === 'paid' ? 'razorpay_or_manual' : 'none',
    'items'                 => $itemsOut,
]);