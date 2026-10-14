<?php
declare(strict_types=1);

/**
 * Account deletion worker — runs daily.
 *
 * Anonymizes accounts whose deletion_scheduled_for has passed.
 * We do NOT hard-delete because orders/invoices/audit must remain for
 * GST and legal compliance. Instead we scrub PII.
 *
 * Run daily:
 *   30 3 * * * cd /path && php api/bin/account-deletion-worker.php
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
require_once __DIR__ . '/../lib/audit.php';

$db = get_db();

echo "[" . date('Y-m-d H:i:s') . "] Account deletion worker started\n";

$stmt = $db->prepare(
    'SELECT id, email, mobile FROM users
     WHERE account_status = "pending_deletion"
       AND deletion_scheduled_for <= UTC_TIMESTAMP()
     LIMIT 500
     FOR UPDATE'
);

$db->beginTransaction();
try {
    $stmt->execute();
    $users = $stmt->fetchAll();
    echo "  Found " . count($users) . " accounts past grace period.\n";

    $anonymized = 0;

    foreach ($users as $u) {
        $uid = (string)$u['id'];
        $anonymizedEmail = 'deleted+' . substr(hash('sha256', $uid), 0, 16) . '@anonymized.local';
        $anonymizedMobile = '0000000000';

        // Scrub PII
        $db->prepare(
            'UPDATE users
             SET first_name = "Deleted",
                 last_name = "User",
                 email = ?,
                 mobile = ?,
                 password_hash = "!",
                 otp_hash = NULL,
                 otp_expires_at = NULL,
                 date_of_birth = NULL,
                 gender = NULL,
                 anniversary_date = NULL,
                 account_status = "deleted",
                 updated_at = UTC_TIMESTAMP()
             WHERE id = ?'
        )->execute([$anonymizedEmail, $anonymizedMobile, $uid]);

        // Scrub addresses
        $db->prepare('UPDATE user_addresses SET full_name = "Deleted", phone = "0000000000", address_line1 = "[REDACTED]", address_line2 = NULL, landmark = NULL WHERE user_id = ?')
           ->execute([$uid]);

        // Scrub wishlist / prefs / cart
        $db->prepare('DELETE FROM wishlist_items WHERE user_id = ?')->execute([$uid]);
        $db->prepare('DELETE FROM cart_items WHERE cart_id IN (SELECT id FROM carts WHERE user_id = ?)')->execute([$uid]);

        // Revoke all sessions
        $db->prepare('UPDATE auth_sessions SET revoked_at = UTC_TIMESTAMP(), revoke_reason = "account_deleted" WHERE principal_type = "customer" AND principal_id = ? AND revoked_at IS NULL')->execute([$uid]);

        audit_event('system.account_anonymized', 'user', $uid, [
            'anonymized_at' => date('Y-m-d H:i:s'),
        ]);

        $anonymized++;
    }

    $db->commit();
    echo "  Anonymized: {$anonymized}\n";
    echo "[" . date('Y-m-d H:i:s') . "] Done.\n";
    exit(0);
} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    fwrite(STDERR, "[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage() . "\n");
    exit(2);
}