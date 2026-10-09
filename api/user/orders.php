<?php
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
send_cors_headers();
$payload=require_auth(); $userId=auth_user_id($payload); $db=get_db();

if($_SERVER['REQUEST_METHOD']!=='GET') json_response(405,['success'=>false,'message'=>'Method not allowed.']);

$status = trim((string)($_GET['status'] ?? ''));
$search = trim((string)($_GET['search'] ?? ''));
$limit = min(50, max(1, (int)($_GET['limit'] ?? 10)));
$offset = max(0, (int)($_GET['offset'] ?? 0));

$where = 'user_id = :user';
$params = ['user' => $userId];

if ($status !== '' && $status !== 'all') {
    $allowedStatuses = [
        'pending', 'processing', 'shipped',
        'delivered', 'cancelled', 'returned'
    ];

    if (!in_array($status, $allowedStatuses, true)) {
        json_response(400, [
            'success' => false,
            'message' => 'Invalid order status.'
        ]);
    }

    $where .= ' AND LOWER(status) = :status';
    $params['status'] = $status;
}

if ($search !== '') {
    $where .= ' AND order_number LIKE :search';
    $params['search'] = '%' . $search . '%';
}

$sql = "SELECT id, order_number, status, subtotal, shipping_amount,
               discount_amount, total_amount, created_at, updated_at,(
    SELECT COALESCE(SUM(oi.quantity), 0)
    FROM order_items oi
    WHERE oi.order_id = orders.id
) AS item_count
        FROM orders
        WHERE $where
        ORDER BY created_at DESC, id DESC
        LIMIT $limit OFFSET $offset";
$stmt=$db->prepare($sql); $stmt->execute($params);
$orders=[];
foreach($stmt->fetchAll() as $o){
$orders[] = [
    'id' => (int) $o['id'],
    'orderNumber' => $o['order_number'],
    'status' => $o['status'],
    'itemCount' => (int) $o['item_count'],
    'subtotal' => (float) $o['subtotal'],
    'shippingAmount' => (float) $o['shipping_amount'],
    'discountAmount' => (float) $o['discount_amount'],
    'totalAmount' => (float) $o['total_amount'],
    'createdAt' => $o['created_at'],
    'updatedAt' => $o['updated_at']
];
}
$countStmt=$db->prepare("SELECT COUNT(*) FROM orders WHERE $where"); $countStmt->execute($params); $total=(int)$countStmt->fetchColumn();
json_response(200,['success'=>true,'orders'=>$orders,'total'=>$total,'limit'=>$limit,'offset'=>$offset]);
