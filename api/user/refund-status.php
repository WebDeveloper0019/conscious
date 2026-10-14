<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/refund-queue.php';

send_cors_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

$payload = require_auth();
$userId  = auth_user_id($payload);
$db      = get_db();

$orderNumber = trim((string)($_GET['orderNumber'] ?? ''));
$refundId    = trim((string)($_GET['refund_id']   ?? ''));

if ($orderNumber === '' && $refundId === '') {
    json_response(422, ['success' => false, 'message' => 'Provide orderNumber or refund_id.']);
}

// ── Resolve order ──
if ($refundId !== '') {
    $stmt = $db->prepare(
        'SELECT r.*, o.order_number, o.status AS order_status,
                o.payment_status, o.total_amount, o.user_id AS order_user_id
         FROM refunds r
         JOIN orders o ON o.id = r.order_id
         WHERE r.id = ? AND o.user_id = ?
         LIMIT 1'
    );
    $stmt->execute([$refundId, $userId]);
    $single = $stmt->fetch();
    if (!$single) {
        json_response(404, ['success' => false, 'message' => 'Refund not found.']);
    }
    $orderId = (int)$single['order_id'];
} else {
    $stmt = $db->prepare('SELECT id, order_number, status, payment_status, total_amount FROM orders WHERE order_number = ? AND user_id = ? LIMIT 1');
    $stmt->execute([$orderNumber, $userId]);
    $order = $stmt->fetch();
    if (!$order) {
        json_response(404, ['success' => false, 'message' => 'Order not found.']);
    }
    $orderId = (int)$order['id'];
    $single = null;
}

// ── Fetch all refunds for the order ──
$rStmt = $db->prepare(
    'SELECT r.*, 
            j.status AS job_status, j.attempts, j.max_attempts,
            j.next_attempt_at, j.last_error
     FROM refunds r
     LEFT JOIN refund_jobs j ON j.refund_id = r.id
     WHERE r.order_id = ?
     ORDER BY r.created_at DESC'
);
$rStmt->execute([$orderId]);
$refundRows = $rStmt->fetchAll();

// ── Fetch order info for context ──
$oStmt = $db->prepare('SELECT id, order_number, status, payment_status, payment_method, total_amount, cancelled_at FROM orders WHERE id = ?');
$oStmt->execute([$orderId]);
$orderInfo = $oStmt->fetch();

// ── Normalize statuses ──
function normalize_refund_status(string $status, ?string $jobStatus): array
{
    $map = [
        'recommended'      => ['state' => 'awaiting_approval', 'label' => 'Awaiting approval'],
        'processing'       => ['state' => 'processing',        'label' => 'Refund in progress'],
        'processed'        => ['state' => 'completed',         'label' => 'Refund completed'],
        'failed'           => ['state' => 'failed',            'label' => 'Refund failed'],
        'cancelled'        => ['state' => 'cancelled',         'label' => 'Refund cancelled'],
    ];

    $info = $map[strtolower($status)] ?? ['state' => 'unknown', 'label' => 'Unknown status'];

    // If queued for retry, refine label
    if ($jobStatus === 'queued' || $jobStatus === 'retry_scheduled') {
        $info['label'] .= ' (queued for retry)';
    } elseif ($jobStatus === 'processing') {
        $info['label'] = 'Refund processing';
    } elseif ($jobStatus === 'failed_permanent') {
        $info['state'] = 'failed';
        $info['label'] = 'Refund failed — manual action pending';
    }

    return $info;
}

// ── Build response ──
$refunds = [];
$totalRefunded = 0.0;
$totalPending  = 0.0;

foreach ($refundRows as $r) {
    $status = normalize_refund_status((string)$r['status'], $r['job_status'] ?? null);
    $amount = (float)$r['amount'];

    if ($status['state'] === 'completed')      $totalRefunded += $amount;
    elseif (in_array($status['state'], ['processing', 'awaiting_approval'], true)) $totalPending += $amount;

    $refunds[] = [
        'refundId'        => $r['id'],
        'amount'          => $amount,
        'currency'        => $r['currency'] ?? 'INR',
        'method'          => $r['method'],
        'source'          => $r['source'] ?? 'cancellation',
        'status'          => $status['state'],
        'statusLabel'     => $status['label'],
        'providerRef'     => $r['provider_reference'] ?? null,
        'paymentId'       => $r['provider_payment_id'] ?? null,
        'failureMessage'  => $r['failure_message'] ?? null,
        'attempts'        => isset($r['attempts']) ? (int)$r['attempts'] : null,
        'maxAttempts'     => isset($r['max_attempts']) ? (int)$r['max_attempts'] : null,
        'nextRetryAt'     => $r['next_attempt_at'] ?? null,
        'lastError'       => $r['last_error'] ?? null,
        'createdAt'       => $r['created_at'],
        'processedAt'     => $r['processed_at'] ?? null,
    ];
}

$response = [
    'success' => true,
    'order' => [
        'orderNumber'    => $orderInfo['order_number'],
        'status'         => $orderInfo['status'],
        'paymentStatus'  => $orderInfo['payment_status'],
        'paymentMethod'  => $orderInfo['payment_method'] ?? null,
        'totalAmount'    => (float)$orderInfo['total_amount'],
        'cancelledAt'    => $orderInfo['cancelled_at'],
    ],
    'summary' => [
        'refundCount'      => count($refunds),
        'totalRefunded'    => round($totalRefunded, 2),
        'totalPending'     => round($totalPending, 2),
        'hasActiveRefund'  => $totalPending > 0,
        'allComplete'      => count($refunds) > 0 && $totalPending === 0.0 && $totalRefunded > 0,
    ],
    'refunds' => $refunds,
    'polledAt' => (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format(DateTimeInterface::ATOM),
];

json_response(200, $response);