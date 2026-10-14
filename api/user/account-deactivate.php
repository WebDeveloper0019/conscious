<?php
declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/audit.php';
require_once __DIR__ . '/../lib/session.php';

send_cors_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

$payload = require_auth();
$userId  = auth_user_id($payload);
require_csrf();

$body   = get_json_body();
$reason = trim((string)($body['reason'] ?? ''));
$confirm = trim((string)($body['confirm'] ?? ''));

if ($confirm !== 'DEACTIVATE') {
    json_response(422, ['success' => false, 'message' => 'Type DEACTIVATE to confirm.']);
}
if (mb_strlen($reason) > 500) {
    json_response(422, ['success' => false, 'message' => 'Reason must be ≤ 500 chars.']);
}

$db = get_db();

$stmt = $db->prepare('SELECT id, account_status FROM users WHERE id = ? LIMIT 1 FOR UPDATE');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) json_response(404, ['success' => false, 'message' => 'Account not found.']);
if ($user['account_status'] === 'deactivated') {
    json_response(409, ['success' => false, 'message' => 'Account already deactivated.']);
}
if ($user['account_status'] === 'pending_deletion') {
    json_response(409, ['success' => false, 'message' => 'Account is scheduled for deletion. Cancel deletion first.']);
}

$db->beginTransaction();
try {
    $db->prepare(
        'UPDATE users
         SET account_status = "deactivated",
             deactivated_at = UTC_TIMESTAMP(),
             updated_at = UTC_TIMESTAMP()
         WHERE id = ?'
    )->execute([$userId]);

    audit_event('customer.account_deactivated', 'user', $userId, ['reason' => $reason]);

    // Revoke all sessions
    revoke_principal_sessions('customer', $userId, 'account_deactivated');

    $db->commit();

    json_response(200, [
        'success' => true,
        'message' => 'Account deactivated. Log in anytime to reactivate.',
        'nextLogin' => 'sign in with your password to reactivate',
    ]);

} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    error_log('Account deactivation failed: ' . $e->getMessage());
    json_response(500, ['success' => false, 'message' => 'Unable to deactivate account.']);
}