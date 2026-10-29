<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/helpers.php'; require_once __DIR__ . '/../lib/db.php'; send_cors_headers();
$principal = require_principal(['customer']); $userId = $principal['id']; if(($_SERVER['REQUEST_METHOD']??'')!=='POST') json_response(405,['success'=>false,'message'=>'Method not allowed.']);
$body=get_json_body(); $current=(string)($body['currentPassword']??''); $new=(string)($body['newPassword']??''); $confirm=(string)($body['confirmPassword']??'');
if($current===''||$new===''||$confirm==='') json_response(422,['success'=>false,'message'=>'All password fields are required.']);
if(!is_strong_enough_password($new)) json_response(422,['success'=>false,'message'=>'Use 12+ characters with upper, lower, number and symbol.']);
if($new!==$confirm) json_response(422,['success'=>false,'message'=>'New password and confirmation do not match.']);
if(hash_equals($current,$new)) json_response(422,['success'=>false,'message'=>'Choose a password different from your current password.']);
$db=get_db(); $stmt=$db->prepare('SELECT password_hash FROM users WHERE id=:id LIMIT 1'); $stmt->execute(['id'=>$userId]); $user=$stmt->fetch();
if(!$user||!password_verify($current,$user['password_hash'])) json_response(401,['success'=>false,'message'=>'Current password is incorrect.']);
$db->prepare('UPDATE users SET password_hash=:hash,updated_at=:updated WHERE id=:id')->execute(['hash'=>password_hash($new,PASSWORD_DEFAULT),'updated'=>date('Y-m-d H:i:s'),'id'=>$userId]);
audit_event('customer.password_changed','customer',$userId); revoke_principal_sessions('customer',$userId,'password_changed'); clear_auth_cookie();
json_response(200,['success'=>true,'message'=>'Password updated. Please sign in again.','requiresLogin'=>true]);
