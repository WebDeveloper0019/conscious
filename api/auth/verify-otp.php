<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
send_cors_headers();
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') json_response(405, ['success'=>false,'message'=>'Method not allowed.']);
$body=get_json_body(); $email=strtolower(trim((string)($body['email']??''))); $otp=preg_replace('/\D/','',(string)($body['otp']??''));
if(!is_valid_email($email)||strlen($otp)!==6) json_response(422,['success'=>false,'message'=>'Enter the email address and 6-digit code.']);
if(rate_limit('verify|'.request_ip().'|'.$email,10,900)) json_response(429,['success'=>false,'message'=>'Too many verification attempts. Try again later.']);
$db=get_db(); $stmt=$db->prepare('SELECT * FROM users WHERE email=:email LIMIT 1'); $stmt->execute(['email'=>$email]); $user=$stmt->fetch();
if(!$user || !empty($user['email_verified'])) json_response(400,['success'=>false,'message'=>'The code is invalid or no longer required.']);
if((int)$user['otp_attempts']>=5 || empty($user['otp_expires_at']) || strtotime($user['otp_expires_at'])<time()) json_response(410,['success'=>false,'message'=>'This code expired. Request a new code.']);
if(empty($user['otp_hash']) || !password_verify($otp,$user['otp_hash'])) { $db->prepare('UPDATE users SET otp_attempts=otp_attempts+1 WHERE id=:id')->execute(['id'=>$user['id']]); json_response(400,['success'=>false,'message'=>'The verification code is incorrect.']); }
$db->prepare('UPDATE users SET email_verified=1,otp_hash=NULL,otp_expires_at=NULL,otp_attempts=0,updated_at=:updated WHERE id=:id')->execute(['updated'=>date('Y-m-d H:i:s'),'id'=>$user['id']]);
$user['email_verified']=1; $session=create_auth_session('customer',$user['id'],false); audit_event('customer.email_verified','customer',$user['id']); $csrf=$session['csrf_token'];
json_response(200,['success'=>true,'message'=>'Email verified successfully.','user'=>public_user($user),'csrf_token'=>$csrf,'redirect'=>'../index.php']);
