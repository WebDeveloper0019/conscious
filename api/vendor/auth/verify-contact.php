<?php
declare(strict_types=1);
require_once __DIR__.'/../../lib/helpers.php';require_once __DIR__.'/../../lib/resend.php';require_once __DIR__.'/../../lib/vendor.php';
send_cors_headers();if(($_SERVER['REQUEST_METHOD']??'')!=='POST')json_response(405,['success'=>false,'message'=>'Method not allowed.']);
$body=get_json_body();$email=strtolower(trim((string)($body['email']??'')));$code=preg_replace('/\D+/','',(string)($body['code']??''));if(!is_valid_email($email)||strlen($code)!==6)json_response(422,['success'=>false,'message'=>'Enter the registered email and six-digit code.']);
if(rate_limit('vendor-verify|'.request_ip().'|'.$email,10,900))json_response(429,['success'=>false,'message'=>'Too many verification attempts. Try again later.']);
$db=get_db();$stmt=$db->prepare('SELECT * FROM vendors WHERE email=:email LIMIT 1');$stmt->execute(['email'=>$email]);$vendor=$stmt->fetch();
if(!$vendor||!empty($vendor['email_verified']))json_response(400,['success'=>false,'message'=>'The code is invalid or no longer required.']);
if((int)$vendor['otp_attempts']>=5||empty($vendor['otp_expires_at'])||strtotime((string)$vendor['otp_expires_at'])<time())json_response(410,['success'=>false,'message'=>'This code expired. Request a new code.']);
if(empty($vendor['otp_hash'])||!password_verify($code,$vendor['otp_hash'])){$db->prepare('UPDATE vendors SET otp_attempts=otp_attempts+1 WHERE id=:id')->execute(['id'=>$vendor['id']]);json_response(400,['success'=>false,'message'=>'The verification code is incorrect.']);}
$db->prepare('UPDATE vendors SET email_verified=1,otp_hash=NULL,otp_expires_at=NULL,otp_attempts=0,onboarding_status="in_progress",updated_at=UTC_TIMESTAMP() WHERE id=:id')->execute(['id'=>$vendor['id']]);
$session=create_auth_session('vendor',$vendor['id'],false);audit_event('vendor.contact_verified','vendor',$vendor['id'],[],['type'=>'vendor','id'=>$vendor['id']]);
json_response(200,['success'=>true,'message'=>'Contact verified. Continue vendor onboarding.','csrf_token'=>$session['csrf_token'],'vendor'=>public_vendor($vendor),'redirect'=>'../onboarding.php?type='.rawurlencode($vendor['vendor_type'])]);
