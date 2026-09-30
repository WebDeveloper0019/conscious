<?php
declare(strict_types=1);
require_once __DIR__.'/../../lib/helpers.php';require_once __DIR__.'/../../lib/resend.php';
send_cors_headers();if(($_SERVER['REQUEST_METHOD']??'')!=='POST')json_response(405,['success'=>false,'message'=>'Method not allowed.']);
$email=strtolower(trim((string)(get_json_body()['email']??'')));if(!is_valid_email($email))json_response(422,['success'=>false,'message'=>'Enter a valid email address.']);if(rate_limit('vendor-resend|'.request_ip().'|'.$email,5,3600))json_response(429,['success'=>false,'message'=>'Too many requests. Try again later.']);
$stmt=get_db()->prepare('SELECT * FROM vendors WHERE email=:email LIMIT 1');$stmt->execute(['email'=>$email]);$vendor=$stmt->fetch();$generic=['success'=>true,'message'=>'If an unverified vendor account exists, a new code has been sent.'];if(!$vendor||!empty($vendor['email_verified']))json_response(200,$generic);
if(!empty($vendor['otp_last_sent_at'])&&strtotime((string)$vendor['otp_last_sent_at'])>time()-60)json_response(429,['success'=>false,'message'=>'Wait 60 seconds before requesting another code.']);
$otp=(string)random_int(100000,999999);[$sent,$error]=send_vendor_otp_email($email,$vendor['owner_name'],$otp);if(!$sent){error_log('Vendor OTP resend failed: '.$error);json_response(502,['success'=>false,'message'=>'The verification email could not be sent. Try again.']);}
get_db()->prepare('UPDATE vendors SET otp_hash=:hash,otp_expires_at=DATE_ADD(UTC_TIMESTAMP(),INTERVAL 10 MINUTE),otp_attempts=0,otp_last_sent_at=UTC_TIMESTAMP() WHERE id=:id')->execute(['hash'=>password_hash($otp,PASSWORD_DEFAULT),'id'=>$vendor['id']]);json_response(200,$generic);
