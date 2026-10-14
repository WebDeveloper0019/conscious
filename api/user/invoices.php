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

// ── Pagination ──
$page   = max(1, (int)($_GET['page']  ?? 1));
$limit  = min(100, max(1, (int)($_GET['limit'] ?? 20)));
$offset = ($page - 1) * $limit;

// ── Filters ──
$search   = trim((string)($_GET['search']    ?? ''));
$dateFrom = trim((string)($_GET['date_from'] ?? ''));
$dateTo   = trim((string)($_GET['date_to']   ?? ''));
$payStatus= strtolower(trim((string)($_GET['payment_status'] ?? '')));

$where  = 'i.user_id = :uid';
$params = ['uid' => $userId];

// Search — invoice number, order number, amount
if ($search !== '') {
    if (strlen($search) > 100) {
        json_response(422, ['success' => false, 'message' => 'Search term too long.']);
    }
    $where .= ' AND (
        i.invoice_number LIKE :s
        OR o.order_number LIKE :s
        OR CAST(i.grand_total AS CHAR) LIKE :s
    )';
    $params['s'] = '%' . $search . '%';
}

// Date range — validated YYYY-MM-DD
if ($dateFrom !== '') {
    $d = DateTimeImmutable::createFromFormat('Y-m-d', $dateFrom, new DateTimeZone('UTC'));
    if (!$d || $d->format('Y-m-d') !== $dateFrom) {
        json_response(422, ['success' => false, 'message' => 'date_from must be YYYY-MM-DD.']);
    }
    $where .= ' AND DATE(i.issued_at) >= :df';
    $params['df'] = $dateFrom;
}
if ($dateTo !== '') {
    $d = DateTimeImmutable::createFromFormat('Y-m-d', $dateTo, new DateTimeZone('UTC'));
    if (!$d || $d->format('Y-m-d') !== $dateTo) {
        json_response(422, ['success' => false, 'message' => 'date_to must be YYYY-MM-DD.']);
    }
    $where .= ' AND DATE(i.issued_at) <= :dt';
    $params['dt'] = $dateTo;
}

// Payment status filter
if ($payStatus !== '' && $payStatus !== 'all') {
    if (!in_array($payStatus, ['paid', 'pending', 'cod_pending', 'failed', 'refunded'], true)) {
        json_response(422, ['success' => false, 'message' => 'Invalid payment_status.']);
    }
    $where .= ' AND o.payment_status = :ps';
    $params['ps'] = $payStatus;
}

$countStmt = $db->prepare(
    "SELECT COUNT(*) FROM invoices i
     JOIN orders o ON o.id = i.order_id
     WHERE $where"
);
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();

$stmt = $db->prepare(
    "SELECT i.id, i.invoice_number, i.order_id, i.issued_at,
            i.grand_total, i.total_tax, i.currency,
            o.order_number, o.status AS order_status, o.payment_status
     FROM invoices i
     JOIN orders o ON o.id = i.order_id
     WHERE $where
     ORDER BY i.issued_at DESC, i.id DESC
     LIMIT $limit OFFSET $offset"
);
$stmt->execute($params);

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

$totalPages = $total > 0 ? (int)ceil($total / $limit) : 0;

json_response(200, [
    'success'  => true,
    'invoices' => $invoices,
    'filters'  => [
        'search'         => $search,
        'date_from'      => $dateFrom,
        'date_to'        => $dateTo,
        'payment_status' => $payStatus,
    ],
    'pagination' => [
        'page'       => $page,
        'limit'      => $limit,
        'total'      => $total,
        'totalPages' => $totalPages,
        'hasNext'    => $page < $totalPages,
        'hasPrev'    => $page > 1,
    ],
]);