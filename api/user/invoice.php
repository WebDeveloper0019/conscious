<?php

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';

send_cors_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(405, [
        'success' => false,
        'message' => 'Method not allowed.'
    ]);
}

$payload = require_auth();
$userId = auth_user_id($payload);
$db = get_db();

// Validate order number
$orderNumber = trim((string) ($_GET['orderNumber'] ?? ''));

if ($orderNumber === '' || strlen($orderNumber) > 100) {
    json_response(422, [
        'success' => false,
        'message' => 'A valid order number is required.'
    ]);
}

try {
    // Fetch only the order belonging to the authenticated customer
    $stmt = $db->prepare(
        'SELECT id, order_number, user_id, status, created_at,
                subtotal, shipping_amount, discount_amount, total_amount
         FROM orders
         WHERE order_number = :orderNumber
           AND user_id = :userId
         LIMIT 1'
    );

    $stmt->execute([
        'orderNumber' => $orderNumber,
        'userId' => $userId
    ]);

    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        json_response(404, [
            'success' => false,
            'message' => 'Order not found.'
        ]);
    }

    // Fetch items for the verified order
    $itemStmt = $db->prepare(
        'SELECT product_name, quantity, unit_price
         FROM order_items
         WHERE order_id = :orderId
         ORDER BY id ASC'
    );

    $itemStmt->execute([
        'orderId' => $order['id']
    ]);

    $items = [];

    foreach ($itemStmt->fetchAll(PDO::FETCH_ASSOC) as $item) {
        $items[] = [
            'productName' => $item['product_name'],
            'quantity' => (int) $item['quantity'],
            'unitPrice' => (float) $item['unit_price'],
            'lineTotal' => round(
                (int) $item['quantity'] * (float) $item['unit_price'],
                2
            )
        ];
    }

    // Return invoice data
    json_response(200, [
        'success' => true,
        'invoice' => [
            'orderNumber' => $order['order_number'],
            'orderDate' => $order['created_at'],
            'status' => $order['status'],
            'currency' => 'INR',
            'subtotal' => (float) $order['subtotal'],
            'shippingAmount' => (float) $order['shipping_amount'],
            'discountAmount' => (float) $order['discount_amount'],
            'totalAmount' => (float) $order['total_amount'],
            'items' => $items
        ]
    ]);

} catch (PDOException $e) {
    error_log('Invoice API database error: ' . $e->getMessage());

    json_response(500, [
        'success' => false,
        'message' => 'Unable to retrieve invoice at this time.'
    ]);
}
