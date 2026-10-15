<?php
declare(strict_types=1);

/**
 * Inventory anomaly scanner — cron job.
 *
 * Detects:
 *   - Inventory movements with quantity_delta > 0 that were never flagged
 *     by the inline check (e.g. direct SQL inserts, missed hooks)
 *   - Stock levels drifting above the last movement's stock_after
 *
 * Run every 5 minutes:
 *   *\/5 * * * * cd /path && php api/bin/inventory-anomaly-scanner.php
 */

if (PHP_SAPI !== 'cli') { fwrite(STDERR, "CLI only.\n"); exit(1); }

require_once __DIR__ . '/../config.php';
restore_error_handler();
restore_exception_handler();
set_exception_handler(function (Throwable $e) {
    fwrite(STDERR, "[FATAL] " . $e->getMessage() . "\n");
    exit(1);
});

$_SERVER['REQUEST_METHOD'] ??= 'CLI';
$_SERVER['REMOTE_ADDR'] ??= '127.0.0.1';
$_SERVER['HTTP_HOST'] ??= 'localhost';

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/inventory-anomaly.php';

$db = get_db();
$windowMinutes = 10;
$alertsCreated = 0;

echo "[" . date('Y-m-d H:i:s') . "] Inventory anomaly scanner started\n";

try {
    // ── Scan 1: unflagged positive movements in the last N minutes ──
    $stmt = $db->prepare(
        'SELECT id, variant_id, vendor_id, movement_type, quantity_delta,
                stock_after, created_at
         FROM inventory_movements
         WHERE quantity_delta > 0
           AND anomaly_flag = 0
           AND created_at >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL ? MINUTE)
         ORDER BY created_at ASC
         LIMIT 500'
    );
    $stmt->execute([$windowMinutes]);
    $rows = $stmt->fetchAll();

    echo "  Scanning " . count($rows) . " recent positive movements...\n";

    foreach ($rows as $m) {
        $flagged = inventory_check_anomaly(
            (string)$m['variant_id'],
            $m['vendor_id'] ? (string)$m['vendor_id'] : null,
            (string)$m['movement_type'],
            (int)$m['quantity_delta'],
            (int)$m['stock_after'],
            (int)$m['id'],
            0
        );
        if ($flagged) $alertsCreated++;
    }

    // ── Scan 2: stock drift — inventory_levels.stock_quantity doesn't match
    //             the most recent stock_after for that variant ──
    $drift = $db->query(
        "SELECT il.variant_id, il.stock_quantity,
                (SELECT stock_after FROM inventory_movements m
                 WHERE m.variant_id = il.variant_id
                 ORDER BY m.created_at DESC, m.id DESC LIMIT 1) AS last_stock_after
         FROM inventory_levels il
         WHERE il.stock_quantity > 0
           AND EXISTS (SELECT 1 FROM inventory_movements m2 WHERE m2.variant_id = il.variant_id)
         LIMIT 1000"
    );

    foreach ($drift->fetchAll() as $row) {
        $lastAfter = (int)$row['last_stock_after'];
        $current   = (int)$row['stock_quantity'];
        if ($lastAfter !== $current && abs($current - $lastAfter) > 0) {
            // Stock changed without a movement row → orphan change
            try {
                admin_create_alert(
                    'inventory_stock_drift',
                    'Stock drift detected',
                    "Variant {$row['variant_id']}: current stock is {$current} but last movement recorded {$lastAfter}",
                    'warning',
                    'variant',
                    (string)$row['variant_id'],
                    ['current' => $current, 'last_movement' => $lastAfter]
                );
                $alertsCreated++;
            } catch (Throwable $e) {
                error_log('stock drift alert failed: ' . $e->getMessage());
            }
        }
    }

    echo "[" . date('Y-m-d H:i:s') . "] Done. Alerts/tasks created: {$alertsCreated}\n";
    exit(0);

} catch (Throwable $e) {
    fwrite(STDERR, "[" . date('Y-m-d H:i:s') . "] SCANNER ERROR: " . $e->getMessage() . "\n");
    exit(2);
}