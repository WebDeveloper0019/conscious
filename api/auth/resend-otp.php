<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/helpers.php'; require_once __DIR__ . '/../lib/db.php'; require_once __DIR__ . '/../lib/resend.php';
send_cors_headers(); if(($_SERVER['REQUEST_METHOD']??'')!=='POST') json_response(405,['success'=>false,'message'=>'Method not allowed.']);
$body=get_json_body(); $email=strtolower(trim((string)($body['email']??'')));
if(!is_valid_email($email)) json_response(422,['success'=>false,'message'=>'Enter a valid email address.']);
if(rate_limit('resend|'.request_ip().'|'.$email,5,3600)) json_response(429,['success'=>false,'message'=>'Too many requests. Try again later.']);
$db=get_db(); $stmt=$db->prepare('SELECT * FROM users WHERE email=:email LIMIT 1'); $stmt->execute(['email'=>$email]); $user=$stmt->fetch();
$generic=['success'=>true,'message'=>'If an unverified account exists, a new code has been sent.'];
if(!$user || !empty($user['email_verified'])) json_response(200,$generic);
if(!empty($user['otp_last_sent_at'])&&strtotime($user['otp_last_sent_at'])>time()-60) json_response(429,['success'=>false,'message'=>'Please wait 60 seconds before requesting another code.']);
$otp=(string)random_int(100000,999999); [$sent,$error]=send_otp_email($email,$user['first_name'],$otp);
if(!$sent){error_log('OTP resend failed: '.$error);json_response(502,['success'=>false,'message'=>'The verification email could not be sent. Please try again.']);}
$db->prepare('UPDATE users SET otp_hash=:hash,otp_expires_at=:expires,otp_attempts=0,otp_last_sent_at=:sent WHERE id=:id')->execute(['hash'=>password_hash($otp,PASSWORD_DEFAULT),'expires'=>date('Y-m-d H:i:s',time()+600),'sent'=>date('Y-m-d H:i:s'),'id'=>$user['id']]);
json_response(200,$generic);
