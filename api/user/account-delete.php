<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/audit.php';
require_once __DIR__ . '/../lib/session.php';

send_cors_headers();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = require_auth();
    $userId  = auth_user_id($payload);
    require_csrf();

    $body    = get_json_body();
    $confirm = trim((string)($body['confirm'] ?? ''));
    $reason  = trim((string)($body['reason'] ?? ''));

    if ($confirm !== 'DELETE MY ACCOUNT') {
        json_response(422, ['success' => false, 'message' => 'Type DELETE MY ACCOUNT to confirm.']);
    }
    if (mb_strlen($reason) > 500) {
<<<<<<< HEAD
        json_response(422, ['success' => false, 'message' => 'Reason must be ≤ 500 chars.']);
    }

    $db = get_db();
    $stmt = $db->prepare('SELECT id, account_status, deletion_scheduled_for FROM users WHERE id = ? LIMIT 1 FOR UPDATE');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    if (!$user) json_response(404, ['success' => false, 'message' => 'Account not found.']);

    if ($user['account_status'] === 'pending_deletion') {
        json_response(409, [
            'success' => true,
            'message' => 'Deletion already scheduled.',
            'scheduledFor' => $user['deletion_scheduled_for'],
        ]);
    }

=======
        json_response(422, ['success' => false, 'message' => 'Reason must be at most 500 characters.']);
    }

    $db = get_db();
>>>>>>> origin/main
    // 30-day grace period
    $graceDays = 30;

    $db->beginTransaction();
    try {
<<<<<<< HEAD
=======
        $stmt = $db->prepare('SELECT id, account_status, deletion_scheduled_for FROM users WHERE id = ? LIMIT 1 FOR UPDATE');
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        if (!$user) throw new DomainException('ACCOUNT_NOT_FOUND');
        if ($user['account_status'] === 'pending_deletion') throw new DomainException('ALREADY_SCHEDULED:' . (string)$user['deletion_scheduled_for']);
>>>>>>> origin/main
        $db->prepare(
            'UPDATE users
             SET account_status = "pending_deletion",
                 deletion_requested_at = UTC_TIMESTAMP(),
                 deletion_scheduled_for = DATE_ADD(UTC_TIMESTAMP(), INTERVAL ? DAY),
                 updated_at = UTC_TIMESTAMP()
             WHERE id = ?'
        )->execute([$graceDays, $userId]);

        audit_event('customer.account_deletion_requested', 'user', $userId, [
            'reason' => $reason,
            'grace_days' => $graceDays,
        ]);

<<<<<<< HEAD
=======
        revoke_principal_sessions('customer', $userId, 'account_deletion_requested');

>>>>>>> origin/main
        // Fetch schedule
        $s = $db->prepare('SELECT deletion_scheduled_for FROM users WHERE id = ?');
        $s->execute([$userId]);
        $scheduledFor = $s->fetchColumn();

        $db->commit();

        json_response(200, [
            'success' => true,
<<<<<<< HEAD
            'message' => "Your account will be permanently deleted in {$graceDays} days. Log in anytime to cancel.",
=======
            'message' => "Your account will be permanently anonymized in {$graceDays} days. Sign in again during the grace period to cancel.",
>>>>>>> origin/main
            'scheduledFor' => $scheduledFor,
            'graceDays' => $graceDays,
        ]);

<<<<<<< HEAD
=======
    } catch (DomainException $e) {
        if ($db->inTransaction()) $db->rollBack();
        if (str_starts_with($e->getMessage(), 'ALREADY_SCHEDULED:')) {
            json_response(409, ['success' => true, 'message' => 'Deletion already scheduled.', 'scheduledFor' => substr($e->getMessage(), 18)]);
        }
        json_response(404, ['success' => false, 'message' => 'Account not found.']);
>>>>>>> origin/main
    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        error_log('Account deletion scheduling failed: ' . $e->getMessage());
        json_response(500, ['success' => false, 'message' => 'Unable to schedule deletion.']);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $payload = require_auth();
    $userId  = auth_user_id($payload);
    require_csrf();

    $db = get_db();
    $db->prepare(
        'UPDATE users
         SET account_status = "active",
             deletion_requested_at = NULL,
             deletion_scheduled_for = NULL,
             updated_at = UTC_TIMESTAMP()
         WHERE id = ? AND account_status = "pending_deletion"'
    )->execute([$userId]);

    audit_event('customer.account_deletion_cancelled', 'user', $userId);
    json_response(200, ['success' => true, 'message' => 'Deletion cancelled. Welcome back.']);
}

<<<<<<< HEAD
json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
=======
json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
>>>>>>> origin/main
