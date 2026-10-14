<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';

send_cors_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

$payload = require_auth();
$userId  = auth_user_id($payload);
$db      = get_db();

$orderId = (int)($_GET['order_id'] ?? 0);
$orderNumber = trim((string)($_GET['order_number'] ?? ''));

$where = 'o.user_id = ? AND LOWER(o.status) = "delivered"';
$params = [$userId];

if ($orderId > 0) {
    $where .= ' AND o.id = ?';
    $params[] = $orderId;
} elseif ($orderNumber !== '') {
    $where .= ' AND o.order_number = ?';
    $params[] = $orderNumber;
}

$stmt = $db->prepare(
    "SELECT oi.id AS order_item_id, oi.order_id, oi.product_id, oi.product_name,
            oi.image AS product_image, oi.quantity, oi.unit_price,
            o.order_number, o.created_at AS order_date, o.updated_at AS delivered_at,
            r.id AS review_id
     FROM order_items oi
     JOIN orders o ON o.id = oi.order_id
     LEFT JOIN product_reviews r ON r.order_item_id = oi.id AND r.user_id = o.user_id
     WHERE $where
     ORDER BY o.created_at DESC, oi.id ASC"
);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$pending = [];
$alreadyReviewed = [];

foreach ($rows as $r) {
    $entry = [
        'orderItemId'  => (int)$r['order_item_id'],
        'orderId'      => (int)$r['order_id'],
        'orderNumber'  => $r['order_number'],
        'productId'    => $r['product_id'],
        'productName'  => $r['product_name'],
        'productImage' => $r['product_image'],
        'quantity'     => (int)$r['quantity'],
        'orderDate'    => $r['order_date'],
        'reviewId'     => $r['review_id'],
    ];
    if ($r['review_id']) {
        $alreadyReviewed[] = $entry;
    } else {
        $pending[] = $entry;
    }
}

json_response(200, [
    'success'          => true,
    'pending'          => $pending,
    'alreadyReviewed'  => $alreadyReviewed,
    'pendingCount'     => count($pending),
]);