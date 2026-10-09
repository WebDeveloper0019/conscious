
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

// Validate pagination
$page = filter_var(
    $_GET['page'] ?? 1,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

$limit = filter_var(
    $_GET['limit'] ?? 10,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1, 'max_range' => 50]]
);

if ($page === false || $limit === false) {
    json_response(422, [
        'success' => false,
        'message' => 'Invalid pagination parameters.'
    ]);
}

$offset = ($page - 1) * $limit;

try {
    // Count only the logged-in customer's orders
    $countStmt = $db->prepare(
        'SELECT COUNT(*)
         FROM orders
         WHERE user_id = :userId'
    );

    $countStmt->execute(['userId' => $userId]);
    $total = (int) $countStmt->fetchColumn();

    // Fetch real orders for this customer
    $stmt = $db->prepare(
        'SELECT id, order_number, status, total_amount, created_at
         FROM orders
         WHERE user_id = :userId
         ORDER BY created_at DESC, id DESC
         LIMIT :limit OFFSET :offset'
    );

    $stmt->bindValue(':userId', $userId);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $invoices = [];

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $order) {
        $invoices[] = [
            'orderId' => (int) $order['id'],
            'orderNumber' => $order['order_number'],
            'invoiceNumber' => null,
            'date' => $order['created_at'],
            'amount' => (float) $order['total_amount'],
            'gstAmount' => null,
            'status' => $order['status']
        ];
    }

    json_response(200, [
        'success' => true,
        'invoices' => $invoices,
        'pagination' => [
            'page' => (int) $page,
            'limit' => (int) $limit,
            'total' => $total,
            'totalPages' => (int) ceil($total / $limit)
        ]
    ]);

} catch (PDOException $e) {
    error_log('Invoices API database error: ' . $e->getMessage());

    json_response(500, [
        'success' => false,
        'message' => 'Unable to retrieve invoices at this time.'
    ]);
}

