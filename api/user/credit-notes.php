<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/credit-note.php';

send_cors_headers();
$principal = require_principal(['customer']); $userId = $principal['id'];
$db      = get_db();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_response(405, ['success' => false, 'message' => 'Method not allowed.']);

$page   = max(1, (int)($_GET['page']  ?? 1));
$limit  = min(100, max(1, (int)($_GET['limit'] ?? 20)));
$offset = ($page - 1) * $limit;

$countStmt = $db->prepare('SELECT COUNT(*) FROM credit_notes WHERE user_id = ?');
$countStmt->execute([$userId]);
$total = (int)$countStmt->fetchColumn();

$stmt = $db->prepare(
    'SELECT cn.id, cn.credit_note_number, cn.order_id, cn.refund_id,
            cn.issued_at, cn.total_credit, cn.total_tax, cn.currency,
            cn.reason, cn.cancellation_scope,
            o.order_number
     FROM credit_notes cn
     JOIN orders o ON o.id = cn.order_id
     WHERE cn.user_id = ?
     ORDER BY cn.issued_at DESC, cn.id DESC
     LIMIT ' . $limit . ' OFFSET ' . $offset
);
$stmt->execute([$userId]);

$rows = [];
foreach ($stmt->fetchAll() as $r) {
    $rows[] = [
        'id'            => $r['id'],
        'creditNoteNumber' => $r['credit_note_number'],
        'orderId'       => (int)$r['order_id'],
        'orderNumber'   => $r['order_number'],
        'refundId'      => $r['refund_id'],
        'date'          => $r['issued_at'],
        'amount'        => (float)$r['total_credit'],
        'taxAmount'     => (float)$r['total_tax'],
        'currency'      => $r['currency'],
        'reason'        => $r['reason'],
        'scope'         => $r['cancellation_scope'],
    ];
}

$totalPages = $total > 0 ? (int)ceil($total / $limit) : 0;

json_response(200, [
    'success'     => true,
    'creditNotes' => $rows,
    'pagination'  => [
        'page' => $page, 'limit' => $limit, 'total' => $total,
        'totalPages' => $totalPages,
        'hasNext' => $page < $totalPages, 'hasPrev' => $page > 1,
    ],
]);