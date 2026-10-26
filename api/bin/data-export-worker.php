<?php
declare(strict_types=1);

/**
 * Data export worker — packages a user's data into a JSON file.
 * Every query is defensive: missing tables/columns are skipped.
 *
 * Run: *\/5 * * * * cd /path && php api/bin/data-export-worker.php
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
echo "[" . date('Y-m-d H:i:s') . "] Data export worker started\n";

// ── Claim one job atomically ──
$db->beginTransaction();
try {
    $cand = $db->query(
        'SELECT id, user_id FROM user_data_export_jobs
         WHERE status = "queued" ORDER BY created_at ASC LIMIT 1'
    )->fetch();

    if (!$cand) {
        $db->commit();
        echo "  Queue empty.\n";
        exit(0);
    }

    $upd = $db->prepare('UPDATE user_data_export_jobs SET status = "processing", started_at = UTC_TIMESTAMP(), updated_at = UTC_TIMESTAMP() WHERE id = ? AND status = "queued"');
    $upd->execute([$cand['id']]);
    if ($upd->rowCount() !== 1) {
        $db->rollBack();
        echo "  Another worker claimed it.\n";
        exit(0);
    }
    $db->commit();
} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    throw $e;
}

$jobId  = (string)$cand['id'];
$userId = (string)$cand['user_id'];
echo "  Processing job {$jobId} for user {$userId}\n";

/**
 * Defensive fetch — never throws on missing table/column.
 */
function safe_fetch(PDO $db, string $sql, array $params = [], string $label = ''): array
{
    try {
        $s = $db->prepare($sql);
        $s->execute($params);
        return $s->fetchAll();
    } catch (Throwable $e) {
        if ($label) error_log("data-export: {$label} skipped: " . $e->getMessage());
        return [];
    }
}

try {
    // ── Gather data — every query defensive ──
    $user = safe_fetch(
        $db,
        'SELECT id, first_name, last_name, email, mobile, email_verified,
                date_of_birth, gender, anniversary_date, marketing_opt_in,
                account_status, created_at, updated_at
         FROM users WHERE id = ?',
        [$userId],
        'users'
    );

    $addresses  = safe_fetch($db, 'SELECT * FROM user_addresses WHERE user_id = ?', [$userId], 'addresses');
    $prefs      = safe_fetch($db, 'SELECT * FROM user_preferences WHERE user_id = ?', [$userId], 'preferences');
    $orders     = safe_fetch($db, 'SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC', [$userId], 'orders');
    $orderItems = safe_fetch(
        $db,
        'SELECT oi.* FROM order_items oi JOIN orders o ON o.id = oi.order_id WHERE o.user_id = ? ORDER BY oi.id',
        [$userId],
        'order_items'
    );
    $invoices   = safe_fetch($db, 'SELECT * FROM invoices WHERE user_id = ?', [$userId], 'invoices');
    $creditNotes= safe_fetch($db, 'SELECT * FROM credit_notes WHERE user_id = ?', [$userId], 'credit_notes');
    $refunds    = safe_fetch(
        $db,
        'SELECT r.* FROM refunds r JOIN orders o ON o.id = r.order_id WHERE o.user_id = ?',
        [$userId],
        'refunds'
    );
    $returns    = safe_fetch($db, 'SELECT * FROM return_requests WHERE user_id = ?', [$userId], 'returns');
    $rewards    = safe_fetch($db, 'SELECT * FROM customer_reward_ledger WHERE user_id = ? ORDER BY created_at DESC', [$userId], 'rewards');
    $wishlist   = safe_fetch($db, 'SELECT * FROM wishlist_items WHERE user_id = ?', [$userId], 'wishlist');
    $reviews    = safe_fetch($db, 'SELECT * FROM product_reviews WHERE user_id = ?', [$userId], 'reviews');
    $notifs     = safe_fetch(
        $db,
        'SELECT type, title, message, created_at FROM notifications
         WHERE recipient_type = "customer" AND recipient_id = ?
         ORDER BY created_at DESC LIMIT 500',
        [$userId],
        'notifications'
    );

    $archive = [
        'exported_at'    => date('c'),
        'export_version' => '1.0',
        'user'           => $user[0] ?? null,
        'addresses'      => $addresses,
        'preferences'    => $prefs[0] ?? null,
        'orders'         => $orders,
        'order_items'    => $orderItems,
        'invoices'       => $invoices,
        'credit_notes'   => $creditNotes,
        'refunds'        => $refunds,
        'returns'        => $returns,
        'rewards_ledger' => $rewards,
        'wishlist'       => $wishlist,
        'reviews'        => $reviews,
        'notifications'  => $notifs,
    ];

    // ── Write to storage ──
    $storageDir = __DIR__ . '/../../storage/private/data-exports';
    if (!is_dir($storageDir) && !mkdir($storageDir, 0775, true) && !is_dir($storageDir)) {
        throw new RuntimeException('Cannot create storage directory.');
    }

    $fileId     = uuid_string();
    $filename   = 'user-data-' . $userId . '-' . date('Ymd-His') . '.json';
    $storedPath = $storageDir . '/' . $fileId . '.json';
    $json = json_encode($archive, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    if (file_put_contents($storedPath, $json) === false) {
        throw new RuntimeException('Failed to write export file.');
    }

    $size = strlen($json);

    // Register the private export using the canonical file_assets schema.
    try {
        $db->prepare(
            'INSERT INTO file_assets
               (id, owner_type, owner_id, purpose, storage_key, original_name, mime_type,
                extension, size_bytes, sha256, visibility, scan_status, created_at)
             VALUES (?, "customer", ?, "data_export", ?, ?, "application/json",
                     "json", ?, ?, "private", "clean", UTC_TIMESTAMP())'
        )->execute([
            $fileId, $userId, 'data-exports/' . basename($storedPath), $filename,
            $size, hash_file('sha256', $storedPath),
        ]);
    } catch (Throwable $e) {
        @unlink($storedPath);
        throw $e;
    }

    // Update job
    $db->prepare(
        'UPDATE user_data_export_jobs
         SET status = "ready", file_asset_id = ?, size_bytes = ?,
             expires_at = DATE_ADD(UTC_TIMESTAMP(), INTERVAL 7 DAY),
             completed_at = UTC_TIMESTAMP(), updated_at = UTC_TIMESTAMP()
         WHERE id = ?'
    )->execute([$fileId, $size, $jobId]);

    // Notify user
    try {
        create_notification(
            'customer',
            $userId,
            'data_export_ready',
            'Your data export is ready',
            'Download link expires in 7 days.',
            '/portals/modules/customer/account.php',
            ['job_id' => $jobId, 'size_bytes' => $size]
        );
    } catch (Throwable $ignore) {}

    echo "  ✓ Ready: {$filename} ({$size} bytes)\n";
    exit(0);

} catch (Throwable $e) {
    try {
        $db->prepare(
            'UPDATE user_data_export_jobs
             SET status = "failed", error_message = ?, updated_at = UTC_TIMESTAMP()
             WHERE id = ?'
        )->execute([mb_substr($e->getMessage(), 0, 500), $jobId]);
    } catch (Throwable $ignore) {}

    fwrite(STDERR, "[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage() . "\n");
    exit(2);
}
