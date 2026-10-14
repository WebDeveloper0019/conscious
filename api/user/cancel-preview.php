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

<<<<<<< HEAD
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
=======
$stmt = $db->prepare('SELECT * FROM orders WHERE order_number = :n AND user_id = :u LIMIT 1');
$stmt->execute(['n' => $orderNumber, 'u' => $userId]);
$order = $stmt->fetch();
if (!$order) json_response(404, ['success' => false, 'message' => 'Order not found.']);

$items = $db->prepare('SELECT * FROM order_items WHERE order_id = :o');
$items->execute(['o' => $order['id']]);
$rows = $items->fetchAll();

$itemsOut = [];
foreach ($rows as $r) {
    $eligible = in_array($r['status'], ['awaiting_acceptance', 'accepted', 'processing'], true);
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
$packages = $db->prepare(
    'SELECT COUNT(*) FROM shipment_packages p
     JOIN vendor_order_groups g ON g.id=p.vendor_group_id WHERE g.order_id=:order_id'
);
$packages->execute(['order_id' => $order['id']]);
$hasShipment = (int)$packages->fetchColumn() > 0;
$allItemsEligible = count($rows) > 0 && count(array_filter($itemsOut, fn($item) => !$item['eligible'])) === 0;
$canCancel = in_array(strtolower((string)$order['status']), ['payment_pending', 'confirmed'], true)
    && empty($order['cancelled_at']) && $now < $deadline && !$hasShipment && $allItemsEligible;

json_response(200, [
    'success'        => true,
    'orderNumber'    => $order['order_number'],
    'status'         => $order['status'],
    'paymentStatus'  => $order['payment_status'],
    'canCancel'      => $canCancel,
    'cancelDeadline' => $deadline->format(DateTimeInterface::ATOM),
    'estimatedRefund'=> $order['payment_status'] === 'paid' ? round((float)$order['total_amount'], 2) : 0.0,
    'refundMethod'   => $order['payment_status'] === 'paid' ? 'manual_review' : 'none',
    'supportedScope' => 'full',
    'items'          => $itemsOut,
]);
>>>>>>> origin/main
