<?php
declare(strict_types=1);

/**
 * Support SLA escalation worker.
 *
 * Run every 5 minutes:
 *   *\/5 * * * * cd /path && php api/bin/support-sla-worker.php
 *
 * Escalates overdue tickets in 3 tiers:
 *   Level 1 (warning)  — just past due
 *   Level 2 (urgent)   — admin task created
 *   Level 3 (critical) — urgent task
 *
 * Idempotent — one escalation per (ticket, breach_type, level).
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
$_SERVER['REMOTE_ADDR'] ??= '127.0.0.1';
$_SERVER['HTTP_HOST'] ??= 'localhost';

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/support-sla.php';

if (!sla_config('support_sla_worker_enabled', true)) {
    echo "[" . date('Y-m-d H:i:s') . "] Worker disabled via config. Exiting.\n";
    exit(0);
}

$db = get_db();
$batchSize = max(10, (int)sla_config('support_sla_worker_batch_size', 200));

echo "[" . date('Y-m-d H:i:s') . "] Support SLA worker started (batch={$batchSize})\n";

$firstResponseEscalations = 0;
$resolutionEscalations = 0;
$levelCounts = [1 => 0, 2 => 0, 3 => 0];
$errors = 0;

try {
    // ─── 1. First-response overdue ───
    $stmt = $db->prepare(
        'SELECT id, ticket_number, subject, priority, status, assigned_admin_id,
                first_response_due_at, first_response_at,
                TIMESTAMPDIFF(MINUTE, first_response_due_at, UTC_TIMESTAMP()) AS overdue_minutes
         FROM support_tickets
         WHERE first_response_at IS NULL
           AND first_response_due_at IS NOT NULL
           AND first_response_due_at < UTC_TIMESTAMP()
           AND status NOT IN ("resolved", "closed")
         ORDER BY first_response_due_at ASC
         LIMIT ' . $batchSize
    );
    $stmt->execute();
    $overdueFirst = $stmt->fetchAll();

    echo "  First-response overdue: " . count($overdueFirst) . " tickets\n";

    foreach ($overdueFirst as $t) {
        $overdue = (int)$t['overdue_minutes'];
        $level = sla_compute_level('first_response', $overdue);
        if ($level < 1) continue;

        $r = sla_escalate_ticket($t, 'first_response', $level, $overdue);
        if ($r['escalated']) {
            $firstResponseEscalations++;
            $levelCounts[$level] = ($levelCounts[$level] ?? 0) + 1;
            echo "    ✓ {$t['ticket_number']} — level {$level} ({$overdue}m overdue)\n";
        } elseif (($r['reason'] ?? '') === 'error') {
            $errors++;
        }
    }

    // ─── 2. Resolution overdue ───
    $stmt = $db->prepare(
        'SELECT id, ticket_number, subject, priority, status, assigned_admin_id,
                resolution_due_at,
                TIMESTAMPDIFF(MINUTE, resolution_due_at, UTC_TIMESTAMP()) AS overdue_minutes
         FROM support_tickets
         WHERE resolution_due_at IS NOT NULL
           AND resolution_due_at < UTC_TIMESTAMP()
           AND status NOT IN ("resolved", "closed")
         ORDER BY resolution_due_at ASC
         LIMIT ' . $batchSize
    );
    $stmt->execute();
    $overdueRes = $stmt->fetchAll();

    echo "  Resolution overdue: " . count($overdueRes) . " tickets\n";

    foreach ($overdueRes as $t) {
        $overdue = (int)$t['overdue_minutes'];
        $level = sla_compute_level('resolution', $overdue);
        if ($level < 1) continue;

        $r = sla_escalate_ticket($t, 'resolution', $level, $overdue);
        if ($r['escalated']) {
            $resolutionEscalations++;
            $levelCounts[$level] = ($levelCounts[$level] ?? 0) + 1;
            echo "    ✓ {$t['ticket_number']} — level {$level} ({$overdue}m overdue on resolution)\n";
        } elseif (($r['reason'] ?? '') === 'error') {
            $errors++;
        }
    }

    echo "\n[" . date('Y-m-d H:i:s') . "] Done.\n";
    echo "  First-response escalations: {$firstResponseEscalations}\n";
    echo "  Resolution escalations:     {$resolutionEscalations}\n";
    echo "  Level 1 (warning):          " . $levelCounts[1] . "\n";
    echo "  Level 2 (urgent):           " . $levelCounts[2] . "\n";
    echo "  Level 3 (critical):         " . $levelCounts[3] . "\n";
    if ($errors > 0) echo "  Errors:                     {$errors}\n";

    exit(0);

} catch (Throwable $e) {
    fwrite(STDERR, "[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage() . "\n");
    exit(2);
}