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

$stmt = $db->prepare('SELECT * FROM orders WHERE order_number = :n AND user_id = :u LIMIT 1');
$stmt->execute(['n' => $orderNumber, 'u' => $userId]);
$order = $stmt->fetch();
if (!$order) json_response(404, ['success' => false, 'message' => 'Order not found.']);

$items = $db->prepare('SELECT * FROM order_items WHERE order_id = :o');
$items->execute(['o' => $order['id']]);
$rows = $items->fetchAll();

$refundAmount = 0.0;
$itemsOut = [];
foreach ($rows as $r) {
    $eligible = !in_array($r['status'], ['cancelled', 'delivered', 'returned'], true);
    if ($eligible) $refundAmount += (float)$r['line_total'];
    $itemsOut[] = [
        'itemId'    => (int)$r['id'],
        'name'      => $r['product_name'],
        'quantity'  => (int)$r['quantity'],
        'lineTotal' => (float)$r['line_total'],
        'status'    => $r['status'],
        'eligible'  => $eligible,
    ];
}

$placedAt = $order['placed_at'] ?? $order['created_at'];
$deadline = (new DateTimeImmutable((string)$placedAt, new DateTimeZone('UTC')))->modify('+24 hours');
$now      = new DateTimeImmutable('now', new DateTimeZone('UTC'));

json_response(200, [
    'success'        => true,
    'orderNumber'    => $order['order_number'],
    'status'         => $order['status'],
    'paymentStatus'  => $order['payment_status'],
    'canCancel'      => in_array(strtolower((string)$order['status']), ['pending','processing','confirmed','awaiting_acceptance','accepted'], true)
                        && empty($order['cancelled_at'])
                        && $now < $deadline,
    'cancelDeadline' => $deadline->format(DateTimeInterface::ATOM),
    'estimatedRefund'=> round($refundAmount, 2),
    'refundMethod'   => $order['payment_status'] === 'paid' ? 'razorpay_or_manual' : 'none',
    'items'          => $itemsOut,
]);