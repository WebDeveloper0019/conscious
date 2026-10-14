<?php
declare(strict_types=1);

/**
 * Refund queue worker.
 *
 * Run manually:  php api/bin/refund-worker.php
 * Run via cron:  * * * * * cd /path && php api/bin/refund-worker.php >> /dev/null 2>&1
 *
 * Processes up to 20 refund jobs per invocation, exits when queue drains.
 * Safe to run concurrently (uses FOR UPDATE SKIP LOCKED).
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only.\n");
    exit(1);
}

require_once __DIR__ . '/../config.php';

// CLI hardening — override HTTP error handlers
restore_error_handler();
restore_exception_handler();
set_exception_handler(function (Throwable $e) {
    fwrite(STDERR, "[FATAL] " . $e->getMessage() . "\n  at " . $e->getFile() . ":" . $e->getLine() . "\n");
    exit(1);
});

// Stub HTTP vars
$_SERVER['REQUEST_METHOD'] ??= 'CLI';
$_SERVER['REMOTE_ADDR']    ??= '127.0.0.1';
$_SERVER['HTTP_HOST']      ??= 'localhost';

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/razorpay.php';
require_once __DIR__ . '/../lib/refund-queue.php';

$batchSize   = 20;
$totalRun    = 0;
$succeeded   = 0;
$retried     = 0;
$permanently = 0;

echo "[" . date('Y-m-d H:i:s') . "] Refund worker started\n";

try {
    $jobs = refund_queue_claim($batchSize);
    if (!$jobs) {
        echo "[" . date('Y-m-d H:i:s') . "] Queue empty. Exiting.\n";
        exit(0);
    }

    echo "[" . date('Y-m-d H:i:s') . "] Claimed " . count($jobs) . " job(s)\n";

    foreach ($jobs as $job) {
        $totalRun++;
        $jobId        = (string)$job['id'];
        $refundId     = (string)$job['refund_id'];
        $orderNumber  = (string)$job['order_number'];
        $paymentId    = (string)$job['provider_payment_id'];
        $amount       = (float)$job['amount'];
        $attempt      = (int)$job['attempts'] + 1;
        $maxAttempts  = (int)$job['max_attempts'];

        echo "  → Job {$jobId} (order {$orderNumber}, attempt {$attempt}/{$maxAttempts}): ";

        // Skip if refund is already marked processed (defensive)
        if (strtolower((string)$job['refund_status']) === 'processed') {
            echo "already processed (skip)\n";
            refund_queue_mark_succeeded($jobId, '');
            continue;
        }

        if ($paymentId === '') {
            echo "no provider_payment_id (permanent fail)\n";
            refund_queue_mark_failed($jobId, 'Missing provider_payment_id');
            $permanently++;
            continue;
        }

        if (!razorpay_is_configured()) {
            echo "Razorpay not configured\n";
            refund_queue_mark_failed($jobId, 'Razorpay not configured');
            $permanently++;
            continue;
        }

        try {
            $api = razorpay_request(
                'POST',
                'payments/' . $paymentId . '/refund',
                [
                    'amount' => (int)round($amount * 100),
                    'notes'  => [
                        'order_number' => $orderNumber,
                        'source'       => 'refund_worker',
                        'attempt'      => $attempt,
                    ],
                ]
            );

            if (empty($api['id'])) {
                throw new RuntimeException('Razorpay returned no refund id');
            }

            refund_queue_mark_succeeded($jobId, (string)$api['id']);
            echo "✓ succeeded (refund {$api['id']})\n";
            $succeeded++;

        } catch (Throwable $e) {
            $err = $e->getMessage();
            $willRetry = refund_queue_mark_failed($jobId, $err);

            if ($willRetry) {
                echo "✗ failed (will retry): {$err}\n";
                $retried++;
            } else {
                echo "✗ failed permanently: {$err}\n";
                $permanently++;
            }
        }
    }

    echo "[" . date('Y-m-d H:i:s') . "] Done. total={$totalRun} ok={$succeeded} retried={$retried} permanent_fail={$permanently}\n";
    exit(0);

} catch (Throwable $e) {
    fwrite(STDERR, "[" . date('Y-m-d H:i:s') . "] WORKER ERROR: " . $e->getMessage() . "\n");
    exit(2);
}