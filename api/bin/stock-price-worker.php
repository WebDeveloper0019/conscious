<?php
declare(strict_types=1);

/**
 * Back-in-stock + price-drop notification worker.
 *
 * Run every 5 minutes:
 *   *\/5 * * * * cd /path && php api/bin/stock-price-worker.php
 *
 * Skips users who received a notification in the last 12 hours for the
 * same subscription (prevents spam if price oscillates).
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
require_once __DIR__ . '/../lib/notifications.php';

$db = get_db();
echo "[" . date('Y-m-d H:i:s') . "] Stock/price worker started\n";

$SPAM_WINDOW_HOURS = 12;

$backInStockSent = 0;
$priceDropSent   = 0;

try {
    // ═══════════════════════════════════════════
    //  1. BACK IN STOCK
    // ═══════════════════════════════════════════
    $stmt = $db->prepare(
        "SELECT s.id, s.user_id, s.product_id, s.variant_id,
                p.name AS product_name,
                (SELECT (i.stock_quantity - i.reserved_quantity)
                 FROM inventory_levels i
                 WHERE i.variant_id = COALESCE(s.variant_id,
                       (SELECT id FROM product_variants WHERE product_id = p.id ORDER BY id LIMIT 1))
                 LIMIT 1) AS available_qty
         FROM stock_price_subscriptions s
         JOIN products p ON p.id = s.product_id
         WHERE s.subscription_type = 'back_in_stock'
           AND s.status = 'active'
           AND (
               s.last_notified_at IS NULL
               OR s.last_notified_at < DATE_SUB(UTC_TIMESTAMP(), INTERVAL {$SPAM_WINDOW_HOURS} HOUR)
           )
         LIMIT 500"
    );
    $stmt->execute();
    $subs = $stmt->fetchAll();

    foreach ($subs as $s) {
        if ((int)$s['available_qty'] <= 0) continue;

        try {
            create_notification(
                'customer',
                (string)$s['user_id'],
                'back_in_stock',
                'Back in stock: ' . $s['product_name'],
                'The item you were watching is available again. Grab it before it sells out!',
                '/product-detail.html?id=' . urlencode((string)$s['product_id']),
                ['product_id' => $s['product_id'], 'variant_id' => $s['variant_id']]
            );

            $db->prepare(
                'UPDATE stock_price_subscriptions
                 SET status = "notified",
                     last_notified_at = UTC_TIMESTAMP(),
                     notify_count = notify_count + 1,
                     updated_at = UTC_TIMESTAMP()
                 WHERE id = ?'
            )->execute([$s['id']]);

            $backInStockSent++;
        } catch (Throwable $e) {
            error_log('Back-in-stock notify failed for sub ' . $s['id'] . ': ' . $e->getMessage());
        }
    }

    echo "  Back-in-stock notifications: {$backInStockSent}\n";

    // ═══════════════════════════════════════════
    //  2. PRICE DROP
    // ═══════════════════════════════════════════
    $stmt = $db->prepare(
        "SELECT s.id, s.user_id, s.product_id, s.variant_id,
                s.target_price, s.original_price,
                p.name AS product_name, p.selling_price AS current_price
         FROM stock_price_subscriptions s
         JOIN products p ON p.id = s.product_id
         WHERE s.subscription_type = 'price_drop'
           AND s.status = 'active'
           AND p.status = 'approved'
           AND p.selling_price < s.target_price
           AND (
               s.last_notified_at IS NULL
               OR s.last_notified_at < DATE_SUB(UTC_TIMESTAMP(), INTERVAL {$SPAM_WINDOW_HOURS} HOUR)
           )
         LIMIT 500"
    );
    $stmt->execute();
    $subs = $stmt->fetchAll();

    foreach ($subs as $s) {
        $current  = (float)$s['current_price'];
        $original = (float)$s['original_price'];
        $target   = (float)$s['target_price'];
        $savings  = max(0, $original - $current);

        try {
            create_notification(
                'customer',
                (string)$s['user_id'],
                'price_drop',
                'Price drop: ' . $s['product_name'],
                'Now ₹' . number_format($current, 2)
                    . ' — down from ₹' . number_format($original, 2)
                    . ($savings > 0 ? ' (save ₹' . number_format($savings, 2) . ')' : ''),
                '/product-detail.html?id=' . urlencode((string)$s['product_id']),
                [
                    'product_id'   => $s['product_id'],
                    'variant_id'   => $s['variant_id'],
                    'current_price' => $current,
                    'original_price' => $original,
                    'savings'      => $savings,
                ]
            );

            $db->prepare(
                'UPDATE stock_price_subscriptions
                 SET status = "notified",
                     last_notified_at = UTC_TIMESTAMP(),
                     notify_count = notify_count + 1,
                     updated_at = UTC_TIMESTAMP()
                 WHERE id = ?'
            )->execute([$s['id']]);

            $priceDropSent++;
        } catch (Throwable $e) {
            error_log('Price-drop notify failed for sub ' . $s['id'] . ': ' . $e->getMessage());
        }
    }

    echo "  Price-drop notifications: {$priceDropSent}\n";

    // ═══════════════════════════════════════════
    //  3. EXPIRE STALE SUBSCRIPTIONS (>90 days old, never triggered)
    // ═══════════════════════════════════════════
    $expired = $db->prepare(
        'UPDATE stock_price_subscriptions
         SET status = "expired", updated_at = UTC_TIMESTAMP()
         WHERE status = "active"
           AND created_at < DATE_SUB(UTC_TIMESTAMP(), INTERVAL 90 DAY)'
    );
    $expired->execute();
    echo "  Expired stale subs: " . $expired->rowCount() . "\n";

    echo "[" . date('Y-m-d H:i:s') . "] Done.\n";
    exit(0);

} catch (Throwable $e) {
    fwrite(STDERR, "[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage() . "\n");
    exit(2);
}