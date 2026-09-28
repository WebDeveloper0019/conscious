<?php
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';

send_cors_headers();
$payload = require_auth();
$userId = auth_user_id($payload);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(405,['success'=>false,'message'=>'Method not allowed.']);

$body=get_json_body();
$current=(string)($body['currentPassword'] ?? '');
$new=(string)($body['newPassword'] ?? '');
$confirm=(string)($body['confirmPassword'] ?? '');

if ($current === '' || $new === '' || $confirm === '') json_response(422,['success'=>false,'message'=>'All password fields are required.']);
if (!is_strong_enough_password($new)) json_response(422,['success'=>false,'message'=>'New password must be at least 8 characters.']);
if ($new !== $confirm) json_response(422,['success'=>false,'message'=>'New password and confirmation do not match.']);

$db=get_db();
$stmt=$db->prepare('SELECT password_hash FROM users WHERE id=:id LIMIT 1');
$stmt->execute(['id'=>$userId]);
$user=$stmt->fetch();
if (!$user || !password_verify($current,$user['password_hash'])) json_response(401,['success'=>false,'message'=>'Current password is incorrect.']);

$hash=password_hash($new,PASSWORD_BCRYPT);
$stmt=$db->prepare('UPDATE users SET password_hash=:hash,updated_at=:updated_at WHERE id=:id');
$stmt->execute(['hash'=>$hash,'updated_at'=>date('Y-m-d H:i:s'),'id'=>$userId]);

json_response(200,['success'=>true,'message'=>'Password updated successfully.']);
