<?php
declare(strict_types=1);

/**
 * Reward expiry worker.
 *
 * Run daily:   * 3 * * * cd /path && php api/bin/reward-expiry-worker.php >> /var/log/reward-expiry.log 2>&1
 * Run hourly:  0 * * * * cd /path && php api/bin/reward-expiry-worker.php
 *
 * Steps:
 *   1. Send 30-day warnings for points expiring in 29-30 days
 *   2. Send 7-day warnings for points expiring in 6-7 days
 *   3. Send 1-day warnings for points expiring in 0-1 days
 *   4. Expire past-due points + reconcile balances
 *   5. Send post-expiry notifications
 */

if (PHP_SAPI !== 'cli') { fwrite(STDERR, "CLI only.\n"); exit(1); }

require_once __DIR__ . '/../config.php';

restore_error_handler();
restore_exception_handler();
set_exception_handler(function (Throwable $e) {
    fwrite(STDERR, "[FATAL] " . $e->getMessage() . "\n  at " . $e->getFile() . ":" . $e->getLine() . "\n");
    exit(1);
});

$_SERVER['REQUEST_METHOD'] ??= 'CLI';
$_SERVER['REMOTE_ADDR']    ??= '127.0.0.1';
$_SERVER['HTTP_HOST']      ??= 'localhost';

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/notifications.php';
require_once __DIR__ . '/../lib/reward-expiry.php';

$startedAt = microtime(true);
echo "[" . date('Y-m-d H:i:s') . "] Reward expiry worker started\n";

$totalWarnings = 0;
$totalExpired  = 0;
$totalNotifs   = 0;

try {
    // ── 1. Pre-expiry warnings ──

    if (reward_expiry_cfg('reward_expiry_warn_30d', true)) {
        $n = reward_expiry_send_warnings(29, 30, 'warn_30d');
        echo "  Sent 30-day warnings: {$n}\n";
        $totalWarnings += $n;
    }

    if (reward_expiry_cfg('reward_expiry_warn_7d', true)) {
        $n = reward_expiry_send_warnings(6, 7, 'warn_7d');
        echo "  Sent 7-day warnings: {$n}\n";
        $totalWarnings += $n;
    }

    if (reward_expiry_cfg('reward_expiry_warn_1d', true)) {
        $n = reward_expiry_send_warnings(0, 1, 'warn_1d');
        echo "  Sent 1-day warnings: {$n}\n";
        $totalWarnings += $n;
    }

    // ── 2. Sweep: expire past-due + reconcile balances ──
    $sweep = reward_expiry_sweep();
    echo "  Expired entries: {$sweep['entries_expired']} (users affected: {$sweep['users_affected']})\n";
    $totalExpired = $sweep['entries_expired'];

    // ── 3. Post-expiry notifications ──
    $n = reward_expiry_send_expired_notifications();
    echo "  Sent expired notifications: {$n}\n";
    $totalNotifs = $n;

    $elapsed = round(microtime(true) - $startedAt, 2);
    echo "[" . date('Y-m-d H:i:s') . "] Done in {$elapsed}s — warnings={$totalWarnings} expired={$totalExpired} notifs={$totalNotifs}\n";
    exit(0);

} catch (Throwable $e) {
    fwrite(STDERR, "[" . date('Y-m-d H:i:s') . "] WORKER ERROR: " . $e->getMessage() . "\n");
    fwrite(STDERR, $e->getTraceAsString() . "\n");
    exit(2);
}
<<<<<<< HEAD



=======
>>>>>>> origin/main
