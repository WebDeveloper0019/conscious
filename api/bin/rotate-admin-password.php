<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../lib/helpers.php';

if (($argv[1] ?? '') !== '--password-stdin' || count($argv) !== 3) {
    fwrite(STDERR, "Usage: php rotate-admin-password.php --password-stdin admin@example.com\n");
    exit(1);
}

$email = strtolower(trim((string)$argv[2]));
$password = rtrim((string)fgets(STDIN), "\r\n");
if (!is_valid_email($email) || !is_strong_enough_password($password)) {
    fwrite(STDERR, "Enter a valid email and a unique password of 12–128 characters with uppercase, lowercase, number and symbol.\n");
    exit(1);
}

$db = get_db();
$db->beginTransaction();
try {
    $lookup = $db->prepare('SELECT id,status FROM admin_users WHERE email=:email LIMIT 1 FOR UPDATE');
    $lookup->execute(['email' => $email]);
    $admin = $lookup->fetch();
    if (!$admin || $admin['status'] !== 'active') {
        throw new RuntimeException('Active administrator not found.');
    }

    $db->prepare('UPDATE admin_users SET password_hash=:hash,updated_at=UTC_TIMESTAMP() WHERE id=:id')
        ->execute(['hash' => password_hash($password, PASSWORD_DEFAULT), 'id' => $admin['id']]);
    revoke_principal_sessions('admin', (string)$admin['id'], 'password_rotation');
    $db->prepare('UPDATE password_reset_tokens SET used_at=UTC_TIMESTAMP() WHERE principal_type="admin" AND principal_id=:id AND used_at IS NULL')
        ->execute(['id' => $admin['id']]);
    $db->prepare('UPDATE admin_mfa_challenges SET used_at=UTC_TIMESTAMP() WHERE admin_id=:id AND used_at IS NULL')
        ->execute(['id' => $admin['id']]);
    audit_event('admin.password_rotated', 'admin', (string)$admin['id'], ['source' => 'ssh'], ['type' => 'system']);
    $db->commit();
    fwrite(STDOUT, "ADMIN_PASSWORD_ROTATED\n");
} catch (Throwable $error) {
    if ($db->inTransaction()) $db->rollBack();
    fwrite(STDERR, "ADMIN_PASSWORD_ROTATION_FAILED: " . $error->getMessage() . "\n");
    exit(1);
} finally {
    unset($password);
}
