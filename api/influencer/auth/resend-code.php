<?php
declare(strict_types=1);
require_once __DIR__.'/../../lib/helpers.php';
require_once __DIR__.'/../../lib/resend.php';
send_cors_headers();
if (($_SERVER['REQUEST_METHOD']??'')!=='POST') json_response(405,['success'=>false,'message'=>'Method not allowed.']);
$body=get_json_body();$email=strtolower(trim((string)($body['email']??'')));
if(!is_valid_email($email))json_response(422,['success'=>false,'message'=>'Enter a valid email address.']);
if(rate_limit('influencer-resend|'.request_ip().'|'.$email,5,3600))json_response(429,['success'=>false,'message'=>'Please wait before requesting another code.']);
$stmt=get_db()->prepare('SELECT id,full_name,email_verified,otp_last_sent_at FROM influencers WHERE email=:email LIMIT 1');$stmt->execute(['email'=>$email]);$creator=$stmt->fetch();
$generic=['success'=>true,'message'=>'If this account still needs verification, a new code has been sent.'];
if(!$creator||$creator['email_verified'])json_response(200,$generic);
if($creator['otp_last_sent_at']&&strtotime((string)$creator['otp_last_sent_at'])>time()-60)json_response(429,['success'=>false,'message'=>'Wait one minute before requesting another code.']);
$otp=(string)random_int(100000,999999);[$sent,$error]=send_influencer_otp_email($email,(string)$creator['full_name'],$otp);
if(!$sent)json_response(502,['success'=>false,'message'=>'Verification email is unavailable. Try again later.']);
get_db()->prepare('UPDATE influencers SET otp_hash=:hash,otp_expires_at=DATE_ADD(UTC_TIMESTAMP(),INTERVAL 10 MINUTE),otp_last_sent_at=UTC_TIMESTAMP(),otp_attempts=0 WHERE id=:id')->execute(['hash'=>password_hash($otp,PASSWORD_DEFAULT),'id'=>$creator['id']]);
json_response(200,$generic);
