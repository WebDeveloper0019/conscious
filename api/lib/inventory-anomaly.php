<?php
declare(strict_types=1);
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/admin-notify.php';

/**
 * Legit movement types that may increase stock.
 * Anything else that increases → anomaly.
 */
function inventory_allowed_increase_types(): array
{
    return ['restock', 'purchase', 'return', 'cancellation', 'initial', 'correction'];
}

/**
 * Read a numeric threshold from platform_settings.
 */
function inventory_anomaly_threshold(string $key, float $default): float
{
    static $cache = [];
    if (array_key_exists($key, $cache)) return (float)$cache[$key];

    $stmt = get_db()->prepare('SELECT value_json FROM platform_settings WHERE setting_key = ?');
    $stmt->execute([$key]);
    $raw = $stmt->fetchColumn();
    if ($raw === false) return $cache[$key] = $default;

    $decoded = json_decode((string)$raw, true);
    return $cache[$key] = (float)($decoded['value'] ?? $default);
}

/**
 * Log an anomaly + create admin alert. Idempotent per movement.
 *
 * Call from any code path that modifies inventory_levels.stock_quantity.
 * Silently no-ops on failure (never blocks the calling transaction).
 */
function inventory_check_anomaly(
    string  $variantId,
    ?string $vendorId,
    string  $movementType,
    int     $quantityDelta,
    int     $stockAfter,
    ?int    $movementId = null,
    int     $stockBefore = 0
): bool {
    // Only increases are suspicious by default
    if ($quantityDelta <= 0) return false;

    try {
        $db = get_db();
        $anomalies = [];

        // ── Rule 1: Large single increase ──
        $largeThreshold = (int)inventory_anomaly_threshold('inventory_anomaly_large_increase', 50);
        if ($largeThreshold > 0 && $quantityDelta >= $largeThreshold) {
            $anomalies[] = [
                'type'   => 'large_increase',
                'severity' => $quantityDelta >= $largeThreshold * 4 ? 'critical' : 'warning',
                'detail' => [
                    'reason'    => "Single increase of {$quantityDelta} units exceeded threshold {$largeThreshold}",
                    'threshold' => $largeThreshold,
                ],
            ];
        }

        // ── Rule 2: Unexpected movement type ──
        if (!in_array($movementType, inventory_allowed_increase_types(), true)) {
            $anomalies[] = [
                'type'   => 'unexpected_movement_type',
                'severity' => 'critical',
                'detail' => [
                    'reason'          => "Movement type '{$movementType}' is not in the allowed-increase list",
                    'allowed_types'   => inventory_allowed_increase_types(),
                ],
            ];
        }

        // ── Rule 3: Exceeds 2× historical max ──
        $factor = inventory_anomaly_threshold('inventory_anomaly_historical_factor', 2.0);
        if ($factor > 0) {
            $histStmt = $db->prepare(
                'SELECT MAX(stock_after) FROM inventory_movements
                 WHERE variant_id = ? AND stock_after IS NOT NULL'
            );
            $histStmt->execute([$variantId]);
            $historicalMax = (int)$histStmt->fetchColumn();

            if ($historicalMax > 0 && $stockAfter > $historicalMax * $factor) {
                $anomalies[] = [
                    'type'   => 'exceeds_historical_max',
                    'severity' => 'warning',
                    'detail' => [
                        'reason'         => "Stock after ({$stockAfter}) exceeded {$factor}× historical max ({$historicalMax})",
                        'historical_max' => $historicalMax,
                        'factor'         => $factor,
                    ],
                ];
            }
        }

        // ── Rule 4: Rapid-fire increases for same variant ──
        $rapidWindow = (int)inventory_anomaly_threshold('inventory_anomaly_rapid_window_sec', 60);
        $rapidCount  = (int)inventory_anomaly_threshold('inventory_anomaly_rapid_threshold', 5);
        if ($rapidWindow > 0 && $rapidCount > 0) {
            $rapidStmt = $db->prepare(
                'SELECT COUNT(*) FROM inventory_movements
                 WHERE variant_id = ? AND quantity_delta > 0
                   AND created_at >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL ? SECOND)'
            );
            $rapidStmt->execute([$variantId, $rapidWindow]);
            $recentIncreases = (int)$rapidStmt->fetchColumn();

            if ($recentIncreases >= $rapidCount) {
                $anomalies[] = [
                    'type'   => 'rapid_fire',
                    'severity' => 'warning',
                    'detail' => [
                        'reason'           => "{$recentIncreases} increases within {$rapidWindow}s",
                        'window_seconds'   => $rapidWindow,
                        'count'            => $recentIncreases,
                        'threshold'        => $rapidCount,
                    ],
                ];
            }
        }

        if (!$anomalies) return false;

        // ── Log each anomaly + create admin task/alert ──
        foreach ($anomalies as $a) {
            $anomalyId = uuid_string();

            try {
                $db->prepare(
                    'INSERT INTO inventory_anomaly_log
                       (id, variant_id, vendor_id, movement_id, anomaly_type, severity,
                        detail_json, stock_before, stock_after, quantity_delta,
                        resolved, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, UTC_TIMESTAMP())'
                )->execute([
                    $anomalyId,
                    $variantId,
                    $vendorId,
                    $movementId,
                    $a['type'],
                    $a['severity'],
                    json_encode($a['detail'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    $stockBefore,
                    $stockAfter,
                    $quantityDelta,
                ]);

                // Flag the movement row
                if ($movementId !== null) {
                    $db->prepare('UPDATE inventory_movements SET anomaly_flag = 1 WHERE id = ?')
                       ->execute([$movementId]);
                }
            } catch (Throwable $e) {
                error_log('anomaly log insert failed: ' . $e->getMessage());
            }

            // Admin alert
            admin_create_alert(
                'inventory_anomaly_' . $a['type'],
                'Inventory anomaly: ' . str_replace('_', ' ', $a['type']),
                "Variant {$variantId}: " . ($a['detail']['reason'] ?? 'Unknown anomaly'),
                $a['severity'],
                'variant',
                $variantId,
                [
                    'anomaly_id'     => $anomalyId,
                    'movement_type'  => $movementType,
                    'quantity_delta' => $quantityDelta,
                    'stock_after'    => $stockAfter,
                    'detail'         => $a['detail'],
                ]
            );

            // Critical anomalies → also create an admin task
            if ($a['severity'] === 'critical') {
                admin_create_task(
                    'inventory_investigation',
                    'Investigate inventory anomaly for variant ' . substr($variantId, 0, 8),
                    ($a['detail']['reason'] ?? '') . "\n\nMovement type: {$movementType}\nQty delta: {$quantityDelta}\nStock after: {$stockAfter}",
                    'high',
                    'variant',
                    $variantId,
                    ['anomaly_id' => $anomalyId, 'detail' => $a['detail']]
                );
            }
        }

        return true;
    } catch (Throwable $e) {
        error_log('inventory_check_anomaly failed: ' . $e->getMessage());
        return false;
    }
}