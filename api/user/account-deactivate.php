<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/audit.php';
require_once __DIR__ . '/../lib/session.php';

send_cors_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}
$principal = require_principal(['customer']); $userId = $principal['id'];
require_csrf();

$body   = get_json_body();
$reason = trim((string)($body['reason'] ?? ''));
$confirm = trim((string)($body['confirm'] ?? ''));

if ($confirm !== 'DEACTIVATE') {
    json_response(422, ['success' => false, 'message' => 'Type DEACTIVATE to confirm.']);
}
if (mb_strlen($reason) > 500) {
    json_response(422, ['success' => false, 'message' => 'Reason must be at most 500 characters.']);
}

$db = get_db();

$db->beginTransaction();
try {
    $stmt = $db->prepare('SELECT id, account_status FROM users WHERE id = ? LIMIT 1 FOR UPDATE');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    if (!$user) throw new DomainException('ACCOUNT_NOT_FOUND');
    if ($user['account_status'] === 'deactivated') throw new DomainException('ALREADY_DEACTIVATED');
    if ($user['account_status'] === 'pending_deletion') throw new DomainException('PENDING_DELETION');
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

} catch (DomainException $e) {
    if ($db->inTransaction()) $db->rollBack();
    $messages = [
        'ACCOUNT_NOT_FOUND' => [404, 'Account not found.'],
        'ALREADY_DEACTIVATED' => [409, 'Account already deactivated.'],
        'PENDING_DELETION' => [409, 'Account is scheduled for deletion. Cancel deletion first.'],
    ];
    [$status, $message] = $messages[$e->getMessage()] ?? [409, 'Account cannot be deactivated.'];
    json_response($status, ['success' => false, 'message' => $message]);
} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    error_log('Account deactivation failed: ' . $e->getMessage());
    json_response(500, ['success' => false, 'message' => 'Unable to deactivate account.']);
}
