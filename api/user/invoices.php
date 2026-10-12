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

$page  = max(1, (int)($_GET['page'] ?? 1));
$limit = min(50, max(1, (int)($_GET['limit'] ?? 10)));
$offset = ($page - 1) * $limit;

try {
    $countStmt = $db->prepare('SELECT COUNT(*) FROM invoices WHERE user_id = :u');
    $countStmt->execute(['u' => $userId]);
    $total = (int)$countStmt->fetchColumn();

    $stmt = $db->prepare(
        'SELECT i.id, i.invoice_number, i.order_id, i.issued_at,
                i.grand_total, i.total_tax, i.currency,
                o.order_number, o.status AS order_status, o.payment_status
         FROM invoices i
         JOIN orders o ON o.id = i.order_id
         WHERE i.user_id = :u
         ORDER BY i.issued_at DESC, i.id DESC
         LIMIT :l OFFSET :o'
    );
    $stmt->bindValue(':u', $userId);
    $stmt->bindValue(':l', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':o', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $invoices = [];
    foreach ($stmt->fetchAll() as $r) {
        $invoices[] = [
            'id'            => $r['id'],
            'invoiceNumber' => $r['invoice_number'],
            'orderId'       => (int)$r['order_id'],
            'orderNumber'   => $r['order_number'],
            'date'          => $r['issued_at'],
            'amount'        => (float)$r['grand_total'],
            'gstAmount'     => (float)$r['total_tax'],
            'currency'      => $r['currency'],
            'orderStatus'   => $r['order_status'],
            'paymentStatus' => $r['payment_status'],
        ];
    }

    json_response(200, [
        'success'  => true,
        'invoices' => $invoices,
        'pagination' => [
            'page'       => $page,
            'limit'      => $limit,
            'total'      => $total,
            'totalPages' => $total > 0 ? (int)ceil($total / $limit) : 0,
        ],
    ]);

} catch (Throwable $e) {
    error_log('Invoices API error: ' . $e->getMessage());
    json_response(500, ['success' => false, 'message' => 'Unable to retrieve invoices.']);
}