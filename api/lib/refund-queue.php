<?php
declare(strict_types=1);
require_once __DIR__ . '/helpers.php';

function refund_queue_enqueue(string $refundId, int $orderId): bool
{
    try {
        get_db()->prepare(
            'INSERT INTO refund_jobs
               (id, refund_id, order_id, attempts, max_attempts, status,
                next_attempt_at, created_at, updated_at)
             VALUES (?, ?, ?, 0, 5, "queued",
                     UTC_TIMESTAMP(), UTC_TIMESTAMP(), UTC_TIMESTAMP())'
        )->execute([uuid_string(), $refundId, $orderId]);
        return true;
    } catch (PDOException $e) {
        if ((string)$e->getCode() === '23000') return false;
        throw $e;
    }
}

function refund_queue_claim(int $limit = 10, int $lockSeconds = 120): array
{
    $db = get_db();
    $lockerId = gethostname() . ':' . getmypid() . ':' . bin2hex(random_bytes(3));

    // 1. Find candidate IDs (no lock)
    $cand = $db->prepare(
        'SELECT id FROM refund_jobs
         WHERE status IN ("queued", "retry_scheduled")
           AND next_attempt_at <= UTC_TIMESTAMP()
           AND (locked_until IS NULL OR locked_until < UTC_TIMESTAMP())
           AND attempts < max_attempts
         ORDER BY next_attempt_at ASC
         LIMIT ' . (int)$limit
    );
    $cand->execute();
    $candidateIds = array_column($cand->fetchAll(), 'id');

    if (!$candidateIds) return [];

    // 2. Atomic claim one by one
    $claimedIds = [];
    $claimStmt = $db->prepare(
        'UPDATE refund_jobs
         SET status = "processing",
             locked_by = ?,
             locked_until = DATE_ADD(UTC_TIMESTAMP(), INTERVAL ? SECOND),
             updated_at = UTC_TIMESTAMP()
         WHERE id = ?
           AND status IN ("queued", "retry_scheduled")
           AND (locked_until IS NULL OR locked_until < UTC_TIMESTAMP())'
    );

    foreach ($candidateIds as $id) {
        $claimStmt->execute([$lockerId, $lockSeconds, $id]);
        if ($claimStmt->rowCount() === 1) {
            $claimedIds[] = $id;
        }
    }

    if (!$claimedIds) return [];

    // 3. Fetch full rows
    $inList = implode(',', array_map(fn($id) => $db->quote($id), $claimedIds));
    $fetch = $db->query(
        "SELECT j.*, r.amount, r.currency, r.provider_payment_id, r.method,
                r.status AS refund_status, o.order_number, o.user_id
         FROM refund_jobs j
         JOIN refunds r ON r.id = j.refund_id
         JOIN orders  o ON o.id = j.order_id
         WHERE j.id IN ($inList)"
    );
    return $fetch->fetchAll();
}

function refund_queue_mark_succeeded(string $jobId, string $providerRefundId): void
{
    $db = get_db();
    $db->beginTransaction();
    $refundId = null;
    try {
        $db->prepare(
            'UPDATE refund_jobs
             SET status = "succeeded", completed_at = UTC_TIMESTAMP(),
                 locked_by = NULL, locked_until = NULL, updated_at = UTC_TIMESTAMP()
             WHERE id = ?'
        )->execute([$jobId]);

        $db->prepare(
            'UPDATE refunds r
             JOIN refund_jobs j ON j.refund_id = r.id
             SET r.status = "processed",
                 r.provider_reference = ?,
                 r.processed_at = UTC_TIMESTAMP(),
                 r.updated_at = UTC_TIMESTAMP()
             WHERE j.id = ?'
        )->execute([$providerRefundId, $jobId]);

        // Fetch the refund_id so we can generate a credit note
        $rStmt = $db->prepare('SELECT refund_id FROM refund_jobs WHERE id = ?');
        $rStmt->execute([$jobId]);
        $refundId = $rStmt->fetchColumn() ?: null;

        // Generate GST credit note
        if ($refundId) {
            require_once __DIR__ . '/credit-note.php';
            try {
                credit_note_generate_for_refund((string)$refundId);
            } catch (Throwable $e) {
                error_log('Credit note generation failed for refund ' . $refundId . ': ' . $e->getMessage());
                // Don't fail the whole job — refund itself succeeded
            }
        }

        $db->commit();
    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        throw $e;
    }
                // Reverse earned points for RETURN-linked refunds
            if ($refundId) {
                try {
                    $rInfo = $db->prepare(
                        'SELECT r.return_request_id, r.order_id, r.amount,
                                o.total_amount, o.order_number, o.user_id
                         FROM refunds r
                         JOIN orders o ON o.id = r.order_id
                         WHERE r.id = ? LIMIT 1'
                    );
                    $rInfo->execute([$refundId]);
                    $info = $rInfo->fetch();

                    if ($info && !empty($info['return_request_id'])) {
                        require_once __DIR__ . '/rewards.php';
                        reward_clawback_for_return(
                            (string)$info['user_id'],
                            (int)$info['order_id'],
                            (float)$info['amount'],
                            (float)$info['total_amount'],
                            (string)$info['order_number'],
                            (string)$info['return_request_id']
                        );
                    }
                } catch (Throwable $e) {
                    error_log('Return points reversal failed: ' . $e->getMessage());
                }
            }
}

function refund_queue_mark_failed(string $jobId, string $errorMessage): bool
{
    $db = get_db();
    $db->beginTransaction();
    try {
        $stmt = $db->prepare('SELECT attempts, max_attempts, refund_id, order_id FROM refund_jobs WHERE id = ? FOR UPDATE');
        $stmt->execute([$jobId]);
        $job = $stmt->fetch();
        if (!$job) { $db->rollBack(); return false; }

        $attempts    = (int)$job['attempts'] + 1;
        $maxAttempts = (int)$job['max_attempts'];
        $backoffMap  = [60, 300, 900, 3600, 7200];
        $backoff     = $backoffMap[min($attempts - 1, count($backoffMap) - 1)];
        $errTrunc    = mb_substr($errorMessage, 0, 2000);

        if ($attempts >= $maxAttempts) {
            $db->prepare(
                'UPDATE refund_jobs
                 SET status = "failed_permanent", attempts = ?, last_error = ?,
                     locked_by = NULL, locked_until = NULL,
                     completed_at = UTC_TIMESTAMP(), updated_at = UTC_TIMESTAMP()
                 WHERE id = ?'
            )->execute([$attempts, $errTrunc, $jobId]);

            $db->prepare(
                'UPDATE refunds r
                 JOIN refund_jobs j ON j.refund_id = r.id
                 SET r.status = "failed", r.failure_message = ?, r.updated_at = UTC_TIMESTAMP()
                 WHERE j.id = ?'
            )->execute([$errTrunc, $jobId]);

                       $db->commit();

            // ── Admin task + alert on permanent failure ──
            try {
                require_once __DIR__ . '/notifications.php';
                require_once __DIR__ . '/admin-notify.php';

                // Fetch refund details for the task body
                $rStmt = $db->prepare(
                    'SELECT r.amount, r.method, r.provider_payment_id,
                            o.order_number, o.user_id, o.total_amount
                     FROM refunds r JOIN orders o ON o.id = r.order_id
                     WHERE r.id = ? LIMIT 1'
                );
                $rStmt->execute([$job['refund_id']]);
                $refundInfo = $rStmt->fetch() ?: [];

                $orderNumber = (string)($refundInfo['order_number'] ?? ('#' . $job['order_id']));
                $amount      = (float)($refundInfo['amount'] ?? 0);
                $method      = (string)($refundInfo['method'] ?? 'unknown');

                // 1. Admin task — goes into the work queue
                admin_create_task(
                    'manual_refund',
                    "Manual refund required: Order {$orderNumber}",
                    "The automatic Razorpay refund for order {$orderNumber} failed after {$attempts} attempts.\n\n" .
                    "Amount: ₹" . number_format($amount, 2) . "\n" .
                    "Method: {$method}\n" .
                    "Provider payment ID: " . ($refundInfo['provider_payment_id'] ?? 'N/A') . "\n\n" .
                    "Last error: {$errTrunc}\n\n" .
                    "Action: Process this refund manually via Razorpay dashboard, then mark this task resolved.",
                    'urgent',
                    'refund',
                    (string)$job['refund_id'],
                    [
                        'refund_id'     => $job['refund_id'],
                        'order_id'      => (int)$job['order_id'],
                        'order_number'  => $orderNumber,
                        'amount'        => $amount,
                        'attempts'      => $attempts,
                        'last_error'    => $errTrunc,
                    ]
                );

                // 2. Admin alert — shows up as a banner / unread counter
                admin_create_alert(
                    'refund_failed_permanent',
                    "Refund permanently failed for {$orderNumber}",
                    "₹" . number_format($amount, 2) . " refund could not be processed after {$attempts} attempts. Manual action required.",
                    'critical',
                    'refund',
                    (string)$job['refund_id'],
                    [
                        'refund_id' => $job['refund_id'],
                        'order_id'  => (int)$job['order_id'],
                        'amount'    => $amount,
                    ]
                );

                // 3. In-app notification for any admin user (legacy path)
                create_notification(
                    'admin',
                    '00000000-0000-0000-0000-000000000000',
                    'refund_failed_permanent',
                    'Refund permanently failed',
                    "Order {$orderNumber} refund failed after {$attempts} attempts: {$errTrunc}",
                    null,
                    ['refund_id' => $job['refund_id'], 'order_id' => (int)$job['order_id']]
                );
            } catch (Throwable $notifyErr) {
                error_log('Admin task/alert creation failed: ' . $notifyErr->getMessage());
            }

            return false;
        }

        $db->prepare(
            'UPDATE refund_jobs
             SET status = "retry_scheduled", attempts = ?, last_error = ?,
                 next_attempt_at = DATE_ADD(UTC_TIMESTAMP(), INTERVAL ? SECOND),
                 locked_by = NULL, locked_until = NULL, updated_at = UTC_TIMESTAMP()
             WHERE id = ?'
        )->execute([$attempts, $errTrunc, $backoff, $jobId]);

        $db->commit();
        return true;
    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        throw $e;
    }
}