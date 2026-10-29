<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/audit.php';

send_cors_headers();

$principal = require_principal(['customer']); $userId = $principal['id'];
$db      = get_db();
$method  = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Hash the current session's token so we can flag it as "current device"
$currentTokenHash = null;
$rawToken = (string)($_COOKIE[AUTH_COOKIE] ?? '');
if ($rawToken !== '' && function_exists('session_hash')) {
    $currentTokenHash = session_hash($rawToken);
}

// ────────── GET: list sessions ──────────
if ($method === 'GET') {
    $stmt = $db->prepare(
        'SELECT id, user_agent_text, ip_address, device_label,
                created_at, last_seen_at, expires_at, revoked_at
         FROM auth_sessions
         WHERE principal_type = "customer"
           AND principal_id = ?
           AND revoked_at IS NULL
           AND expires_at > UTC_TIMESTAMP()
         ORDER BY last_seen_at DESC'
    );
    $stmt->execute([$userId]);

    $sessions = [];
    foreach ($stmt->fetchAll() as $s) {
        // Determine if this is the current session
        $isCurrent = false;
        if ($currentTokenHash !== null) {
            $chk = $db->prepare('SELECT token_hash FROM auth_sessions WHERE id = ? LIMIT 1');
            $chk->execute([$s['id']]);
            $th = (string)$chk->fetchColumn();
            $isCurrent = ($th !== '' && hash_equals($th, $currentTokenHash));
        }

        $sessions[] = [
            'id'          => $s['id'],
            'deviceLabel' => $s['device_label'] ?: 'Unknown device',
            'ipAddress'   => $s['ip_address'],
            'userAgent'   => $s['user_agent_text'],
            'createdAt'   => $s['created_at'],
            'lastSeenAt'  => $s['last_seen_at'],
            'expiresAt'   => $s['expires_at'],
            'isCurrent'   => $isCurrent,
        ];
    }

    // Sort current session first
    usort($sessions, fn($a, $b) => ($b['isCurrent'] ? 1 : 0) - ($a['isCurrent'] ? 1 : 0));

    json_response(200, [
        'success'  => true,
        'sessions' => $sessions,
        'total'    => count($sessions),
    ]);
}

// ────────── DELETE: revoke session(s) ──────────
if ($method === 'DELETE') {
    require_csrf();

    $id  = trim((string)($_GET['id'] ?? ''));
    $all = !empty($_GET['all_others']);

    // Revoke all OTHER sessions (keep current)
    if ($all) {
        if ($currentTokenHash === null) {
            json_response(400, ['success' => false, 'message' => 'Current session token missing.']);
        }

        $stmt = $db->prepare(
            'UPDATE auth_sessions
             SET revoked_at = UTC_TIMESTAMP(), revoke_reason = "user_logout_other_devices"
             WHERE principal_type = "customer"
               AND principal_id = ?
               AND revoked_at IS NULL
               AND token_hash != ?'
        );
        $stmt->execute([$userId, $currentTokenHash]);

        audit_event('customer.sessions_revoked_others', 'user', $userId, [
            'count' => $stmt->rowCount(),
        ]);

        json_response(200, [
            'success' => true,
            'message' => $stmt->rowCount() . ' other session(s) logged out.',
            'revoked' => $stmt->rowCount(),
        ]);
    }

    if ($id === '') {
        json_response(422, ['success' => false, 'message' => 'Session id required (or pass all_others=1).']);
    }

    // Verify session belongs to this user
    $chk = $db->prepare(
        'SELECT id, token_hash FROM auth_sessions
         WHERE id = ? AND principal_type = "customer" AND principal_id = ?
           AND revoked_at IS NULL LIMIT 1'
    );
    $chk->execute([$id, $userId]);
    $row = $chk->fetch();

    if (!$row) {
        json_response(404, ['success' => false, 'message' => 'Session not found or already revoked.']);
    }

    $isCurrentSession = $currentTokenHash !== null && hash_equals((string)$row['token_hash'], $currentTokenHash);

    $db->prepare(
        'UPDATE auth_sessions
         SET revoked_at = UTC_TIMESTAMP(), revoke_reason = "user_logout_device"
         WHERE id = ?'
    )->execute([$id]);

    audit_event('customer.session_revoked', 'user', $userId, [
        'session_id'  => $id,
        'was_current' => $isCurrentSession,
    ]);

    // If user revoked their own current session → clear cookies
    if ($isCurrentSession && function_exists('clear_auth_cookie')) {
        clear_auth_cookie();
    }

    json_response(200, [
        'success'       => true,
        'message'       => $isCurrentSession ? 'Current device logged out.' : 'Device logged out.',
        'wasCurrent'    => $isCurrentSession,
    ]);
}

json_response(405, ['success' => false, 'message' => 'Method not allowed.']);