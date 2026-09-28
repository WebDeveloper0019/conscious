<?php
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
send_cors_headers();
if($_SERVER['REQUEST_METHOD']!=='POST')json_response(405,['success'=>false,'message'=>'Method not allowed.']);
$body=get_json_body(); $email=strtolower(trim($body['email']??'')); $otp=preg_replace('/\D/','',(string)($body['otp']??''));
if(!is_valid_email($email)||strlen($otp)!==6)json_response(400,['success'=>false,'message'=>'Enter a valid email and 6-digit OTP.']);
$db=get_db(); $stmt=$db->prepare('SELECT * FROM users WHERE email=:email LIMIT 1'); $stmt->execute(['email'=>$email]); $user=$stmt->fetch();
if(!$user)json_response(404,['success'=>false,'message'=>'Account not found.']);
if(!empty($user['email_verified']))json_response(200,['success'=>true,'message'=>'Email is already verified.']);
if((int)$user['otp_attempts']>=5)json_response(429,['success'=>false,'message'=>'Too many incorrect OTP attempts. Please request a new OTP.']);
if(empty($user['otp_expires_at'])||strtotime($user['otp_expires_at'])<time())json_response(410,['success'=>false,'message'=>'OTP has expired. Please request a new OTP.']);
if(!password_verify($otp,$user['otp_hash'])){ $db->prepare('UPDATE users SET otp_attempts=otp_attempts+1 WHERE id=:id')->execute(['id'=>$user['id']]); json_response(400,['success'=>false,'message'=>'Incorrect OTP.']); }
$db->prepare('UPDATE users SET email_verified=1,otp_hash=NULL,otp_expires_at=NULL,otp_attempts=0 WHERE id=:id')->execute(['id'=>$user['id']]);
$user['email_verified']=1; $token=issue_token($user,false);
json_response(200,['success'=>true,'message'=>'Email verified successfully.','token'=>$token,'user'=>public_user($user)]);
