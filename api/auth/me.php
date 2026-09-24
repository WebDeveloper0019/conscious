<?php
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';

send_cors_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

$payload = require_auth(); // exits with 401 if invalid/expired

$db = get_db();
$stmt = $db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $payload['sub']]);
$user = $stmt->fetch();

if (!$user) {
    json_response(401, ['success' => false, 'message' => 'User no longer exists.']);
}

json_response(200, ['success' => true, 'user' => public_user($user)]);
