<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
send_cors_headers();

$principal = require_principal(['influencer']);
$id = $principal['id'];
$db = get_db();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'PATCH') {
    $body = get_json_body();
    $currentPassword = (string)($body['current_password'] ?? '');
    $newPassword = (string)($body['new_password'] ?? '');

    if ($currentPassword === '' || $newPassword === '') {
        json_response(422, ['success' => false, 'message' => 'Both current and new passwords are required.']);
    }

    if (!is_strong_enough_password($newPassword)) {
        json_response(422, ['success' => false, 'message' => 'New password must be at least 12 characters with uppercase, lowercase, number and symbol.']);
    }

    // 1. Verify current password
    $stmt = $db->prepare('SELECT password_hash FROM influencers WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $hash = $stmt->fetchColumn();

    if (!password_verify($currentPassword, $hash)) {
        json_response(401, ['success' => false, 'message' => 'Current password is incorrect.']);
    }

    // 2. Update to new password
    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $db->prepare('UPDATE influencers SET password_hash = :hash, updated_at = UTC_TIMESTAMP() WHERE id = :id')
       ->execute(['hash' => $newHash, 'id' => $id]);

    // 3. (Optional but recommended) Destroy all other active sessions for security
    $db->prepare('DELETE FROM auth_sessions WHERE principal_type = "influencer" AND principal_id = :id')
       ->execute(['id' => $id]);

    json_response(200, ['success' => true, 'message' => 'Password changed successfully. Please log in again.']);
}

json_response(405, ['success' => false, 'message' => 'Method not allowed.']);