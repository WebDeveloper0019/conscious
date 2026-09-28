<?php
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/resend.php';
send_cors_headers();
if($_SERVER['REQUEST_METHOD']!=='POST')json_response(405,['success'=>false,'message'=>'Method not allowed.']);
$body=get_json_body(); $email=strtolower(trim($body['email']??''));
if(!is_valid_email($email))json_response(400,['success'=>false,'message'=>'Enter a valid email address.']);
$db=get_db(); $stmt=$db->prepare('SELECT * FROM users WHERE email=:email LIMIT 1'); $stmt->execute(['email'=>$email]); $user=$stmt->fetch();
if(!$user)json_response(404,['success'=>false,'message'=>'Account not found.']);
if(!empty($user['email_verified']))json_response(200,['success'=>true,'message'=>'Email is already verified.']);
if(!empty($user['otp_last_sent_at']) && strtotime($user['otp_last_sent_at'])>time()-60)json_response(429,['success'=>false,'message'=>'Please wait 60 seconds before requesting another OTP.']);
$otp=(string)random_int(100000,999999); $hash=password_hash($otp,PASSWORD_DEFAULT); $expires=date('Y-m-d H:i:s',time()+600); $now=date('Y-m-d H:i:s');
[$sent,$mailMessage]=send_otp_email($email,$user['first_name'],$otp);
if(!$sent)json_response(502,['success'=>false,'message'=>'Could not send OTP email.','details'=>$mailMessage]);
$db->prepare('UPDATE users SET otp_hash=:otp_hash,otp_expires_at=:expires,otp_attempts=0,otp_last_sent_at=:sent_at WHERE id=:id')->execute(['otp_hash'=>$hash,'expires'=>$expires,'sent_at'=>$now,'id'=>$user['id']]);
json_response(200,['success'=>true,'message'=>'A new OTP has been sent to your email.']);
