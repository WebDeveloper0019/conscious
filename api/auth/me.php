<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/authorization.php'; // Make sure this is here

send_cors_headers();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

// FIX: Use the real function here!
$principal = require_principal(['customer']); 

$stmt = get_db()->prepare("SELECT * FROM users WHERE id=:id AND email_verified=1 AND status='active' LIMIT 1"); 
$stmt->execute(['id' => $principal['id']]); 

$user = $stmt->fetch();

if (!$user) {
    clear_auth_cookie();
    json_response(401, ['success' => false, 'message' => 'Your session is no longer valid.']);
}

json_response(200, [
    'success' => true, 
    'user' => public_user($user), 
    'csrf_token' => current_csrf_token()
]);