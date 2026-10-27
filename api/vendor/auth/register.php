<?php
declare(strict_types=1);
require_once __DIR__.'/../../lib/helpers.php';require_once __DIR__.'/../../lib/validation.php';require_once __DIR__.'/../../lib/resend.php';
send_cors_headers();if(($_SERVER['REQUEST_METHOD']??'')!=='POST')json_response(405,['success'=>false,'message'=>'Method not allowed.']);
// A low IP-only cap locks out everyone behind the same office/network (and
// quickly blocks local QA). Keep a broad network cap and a stricter cap for
// repeated attempts against the same contact details.
if(rate_limit('vendor-register|'.request_ip(),60,3600))json_response(429,['success'=>false,'message'=>'Too many sign-up requests from this network. Please try again in an hour.']);
$body=get_json_body();$data=validate_fields($body,['vendor_type'=>'required|in:brand,artisan','owner_name'=>'required|string|min:2|max:100','display_name'=>'required|string|min:2|max:140','email'=>'required|email|max:190','phone'=>'required|mobile','password'=>'required|string','terms'=>'required|boolean']);
if(!is_strong_enough_password((string)$data['password']))json_response(422,['success'=>false,'message'=>'Please correct the highlighted fields.','errors'=>['password'=>'Use 12+ characters with upper, lower, number and symbol.']]);
if(!filter_var($data['terms'],FILTER_VALIDATE_BOOL))json_response(422,['success'=>false,'message'=>'Accept the vendor terms and privacy notice.','errors'=>['terms'=>'Required.']]);
$email=strtolower((string)$data['email']);$mobile=preg_replace('/\D+/','',(string)$data['phone']);
if(rate_limit('vendor-register-email|'.request_ip().'|'.$email,5,3600)
    || rate_limit('vendor-register-mobile|'.request_ip().'|'.$mobile,5,3600)){
    json_response(429,['success'=>false,'message'=>'Too many sign-up attempts for this email or mobile. Please wait up to an hour, or sign in if you already registered.']);
}
$db=get_db();
$check=$db->prepare('SELECT id FROM vendors WHERE email=:email OR mobile=:mobile LIMIT 1');$check->execute(['email'=>$email,'mobile'=>$mobile]);if($check->fetch())json_response(409,['success'=>false,'message'=>'A vendor account already uses this email or mobile number.']);
$vendorId=uuid_string();$applicationId=uuid_string();$reference='VND-'.($data['vendor_type']==='brand'?'BR':'AR').'-'.gmdate('Y').'-'.strtoupper(bin2hex(random_bytes(4)));$otp=(string)random_int(100000,999999);$now=gmdate('Y-m-d H:i:s');
$db->beginTransaction();try{
    $stmt=$db->prepare('INSERT INTO vendors (id,vendor_type,owner_name,display_name,email,mobile,password_hash,email_verified,otp_hash,otp_expires_at,otp_last_sent_at,status,verification_status,onboarding_status,created_at) VALUES (:id,:type,:owner,:display,:email,:mobile,:password,0,:otp,DATE_ADD(UTC_TIMESTAMP(),INTERVAL 10 MINUTE),UTC_TIMESTAMP(),"pending","not_submitted","not_started",UTC_TIMESTAMP())');
    $stmt->execute(['id'=>$vendorId,'type'=>$data['vendor_type'],'owner'=>$data['owner_name'],'display'=>$data['display_name'],'email'=>$email,'mobile'=>$mobile,'password'=>password_hash((string)$data['password'],PASSWORD_DEFAULT),'otp'=>password_hash($otp,PASSWORD_DEFAULT)]);
    $db->prepare('INSERT INTO vendor_applications (id,vendor_id,reference,status,created_at) VALUES (:id,:vendor,:reference,"draft",UTC_TIMESTAMP())')->execute(['id'=>$applicationId,'vendor'=>$vendorId,'reference'=>$reference]);
    $db->prepare('INSERT INTO principal_roles (principal_type,principal_id,role_id,granted_at) SELECT "vendor",:vendor,id,UTC_TIMESTAMP() FROM roles WHERE code="vendor"')->execute(['vendor'=>$vendorId]);
    [$sent,$error]=send_vendor_otp_email($email,(string)$data['owner_name'],$otp);if(!$sent){error_log('Vendor OTP email failed: '.$error);throw new RuntimeException('email');}$db->commit();
}catch(Throwable $e){if($db->inTransaction())$db->rollBack();json_response(502,['success'=>false,'message'=>'The vendor account could not be created because verification email was unavailable. Try again.']);}
audit_event('vendor.registered','vendor',$vendorId,['vendor_type'=>$data['vendor_type'],'application_reference'=>$reference],['type'=>'vendor','id'=>$vendorId]);
json_response(201,['success'=>true,'message'=>APP_ENV==='local'&&MAIL_TRANSPORT==='log'?'Vendor account created. The random verification code is in api/data/mail-outbox; no email was sent.':'Vendor account created. Enter the code sent to your email.','email'=>$email,'vendor_type'=>$data['vendor_type'],'application_reference'=>$reference,'redirect'=>'verify-contact.php?type='.rawurlencode((string)$data['vendor_type'])]);
