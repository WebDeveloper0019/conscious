<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/razorpay.php';
require_once __DIR__ . '/../lib/influencer-service-orders.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
if (!razorpay_is_configured()) {
    fwrite(STDERR, "Razorpay is not configured; service refunds were not processed.\n");
    exit(2);
}

$db = get_db();
$limit = max(1, min(50, (int)($argv[1] ?? 10)));
$locker = gethostname() . ':' . getmypid() . ':' . bin2hex(random_bytes(3));
$ids = $db->query(
    'SELECT id FROM influencer_service_refunds
     WHERE status IN ("queued","retry_scheduled") AND next_attempt_at<=UTC_TIMESTAMP()
       AND attempts<max_attempts AND (locked_until IS NULL OR locked_until<UTC_TIMESTAMP())
     ORDER BY next_attempt_at LIMIT ' . $limit
)->fetchAll(PDO::FETCH_COLUMN);
$processed = 0;

foreach ($ids as $id) {
    $claim = $db->prepare(
        'UPDATE influencer_service_refunds
         SET status="processing",locked_by=:locker,locked_until=DATE_ADD(UTC_TIMESTAMP(),INTERVAL 2 MINUTE),updated_at=UTC_TIMESTAMP()
         WHERE id=:id AND status IN ("queued","retry_scheduled")
           AND (locked_until IS NULL OR locked_until<UTC_TIMESTAMP())'
    );
    $claim->execute(['locker' => $locker, 'id' => $id]);
    if ($claim->rowCount() !== 1) continue;

    $stmt = $db->prepare(
        'SELECT r.*,p.provider_payment_id,o.order_number,o.influencer_id,o.vendor_id,o.status AS order_status
         FROM influencer_service_refunds r
         JOIN influencer_service_payments p ON p.id=r.payment_id
         JOIN influencer_service_orders o ON o.id=r.order_id
         WHERE r.id=:id'
    );
    $stmt->execute(['id' => $id]);
    $refund = $stmt->fetch();
    if (!$refund) continue;

    try {
        // Reconcile first: if Razorpay accepted an earlier request but the
        // worker died before the local commit, the stable refund id prevents
        // a second payout on retry.
        $providerRefundId = '';
        $remoteRefunds = razorpay_request('GET', 'payments/' . rawurlencode((string)$refund['provider_payment_id']) . '/refunds?count=100');
        foreach (($remoteRefunds['items'] ?? []) as $remoteRefund) {
            if ((string)($remoteRefund['notes']['service_refund_id'] ?? '') === (string)$refund['id']) {
                $providerRefundId = trim((string)($remoteRefund['id'] ?? ''));
                if ($providerRefundId !== '') break;
            }
        }
        if ($providerRefundId === '') {
            $remote = razorpay_request('POST', 'payments/' . rawurlencode((string)$refund['provider_payment_id']) . '/refund', [
                'amount' => (int)round((float)$refund['amount'] * 100),
                'speed' => 'normal',
                'receipt' => 'refund-' . $refund['order_number'],
                'notes' => ['conscious_service_order_id' => $refund['order_id'], 'service_refund_id' => $refund['id']],
            ]);
            $providerRefundId = trim((string)($remote['id'] ?? ''));
        }
        if ($providerRefundId === '') throw new RuntimeException('Razorpay did not return a refund id.');

        $db->beginTransaction();
        $locked = $db->prepare('SELECT status FROM influencer_service_refunds WHERE id=:id FOR UPDATE');
        $locked->execute(['id' => $id]);
        if ($locked->fetchColumn() !== 'processing') throw new RuntimeException('Refund job lock was lost.');
        $db->prepare(
            'UPDATE influencer_service_refunds
             SET status="refunded",provider_refund_id=:provider_refund,processed_at=UTC_TIMESTAMP(),
                 locked_by=NULL,locked_until=NULL,updated_at=UTC_TIMESTAMP() WHERE id=:id'
        )->execute(['provider_refund' => $providerRefundId, 'id' => $id]);
        $db->prepare('UPDATE influencer_service_payments SET status="refunded",updated_at=UTC_TIMESTAMP() WHERE id=:id')
           ->execute(['id' => $refund['payment_id']]);
        $db->prepare(
            'UPDATE influencer_service_orders
             SET status="cancelled",payment_status="refunded",cancelled_at=UTC_TIMESTAMP(),updated_at=UTC_TIMESTAMP()
             WHERE id=:id'
        )->execute(['id' => $refund['order_id']]);
        service_order_reverse_earning($db, (string)$refund['order_id'], (string)$refund['influencer_id']);
        service_order_record_history($db, (string)$refund['order_id'], (string)$refund['order_status'], 'cancelled', 'Razorpay refund processed.', 'system', '00000000-0000-0000-0000-000000000000');
        create_notification('vendor', $refund['vendor_id'], 'creator_order_refunded', 'Creator package refund processed', 'The refund for ' . $refund['order_number'] . ' was submitted to Razorpay.', null, ['order_id' => $refund['order_id'], 'refund_id' => $id]);
        create_notification('influencer', $refund['influencer_id'], 'creator_order_cancelled', 'Creator collaboration cancelled', $refund['order_number'] . ' was cancelled and refunded.', null, ['order_id' => $refund['order_id']]);
        $db->commit();
        audit_event('system.influencer_service_refunded', 'influencer_service_order', (string)$refund['order_id'], ['refund_id' => $id, 'provider_refund_id' => $providerRefundId], ['type' => 'system', 'id' => '00000000-0000-0000-0000-000000000000']);
        $processed++;
    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        $attempts = (int)$refund['attempts'] + 1;
        $permanent = $attempts >= (int)$refund['max_attempts'];
        $delays = [60, 300, 900, 3600, 7200];
        $next = gmdate('Y-m-d H:i:s', time() + $delays[min($attempts - 1, count($delays) - 1)]);
        $db->prepare(
            'UPDATE influencer_service_refunds
             SET status=:status,attempts=:attempts,next_attempt_at=:next_attempt,failure_message=:message,
                 locked_by=NULL,locked_until=NULL,updated_at=UTC_TIMESTAMP() WHERE id=:id'
        )->execute([
            'status' => $permanent ? 'failed' : 'retry_scheduled', 'attempts' => $attempts,
            'next_attempt' => $next, 'message' => mb_substr($e->getMessage(), 0, 2000), 'id' => $id,
        ]);
        if ($permanent) {
            $db->prepare('UPDATE influencer_service_orders SET status="refund_failed",updated_at=UTC_TIMESTAMP() WHERE id=:id')
               ->execute(['id' => $refund['order_id']]);
            admin_create_task('manual_service_refund', 'Manual creator-package refund required: ' . $refund['order_number'], 'Automatic Razorpay refund failed after ' . $attempts . ' attempts. Last error: ' . mb_substr($e->getMessage(), 0, 1000), 'urgent', 'influencer_service_order', (string)$refund['order_id'], ['refund_id' => $id, 'amount' => $refund['amount']]);
            admin_create_alert('service_refund_failed', 'Creator-package refund failed', $refund['order_number'] . ' requires a manual refund.', 'critical', 'influencer_service_order', (string)$refund['order_id'], ['refund_id' => $id]);
        }
        fwrite(STDERR, $refund['order_number'] . ': ' . $e->getMessage() . "\n");
    }
}

fwrite(STDOUT, "Processed {$processed} influencer-service refunds.\n");
