<?php
declare(strict_types=1);
require_once __DIR__.'/../../lib/helpers.php';require_once __DIR__.'/../../lib/vendor.php';
send_cors_headers();if(($_SERVER['REQUEST_METHOD']??'')!=='POST')json_response(405,['success'=>false,'message'=>'Method not allowed.']);
$body=get_json_body();$email=strtolower(trim((string)($body['email']??'')));$password=(string)($body['password']??'');$remember=filter_var($body['remember']??false,FILTER_VALIDATE_BOOL);
if(!is_valid_email($email)||$password==='')json_response(422,['success'=>false,'message'=>'Enter a valid email address and password.']);
$stmt=get_db()->prepare('SELECT * FROM vendors WHERE email=:email LIMIT 1');$stmt->execute(['email'=>$email]);$vendor=$stmt->fetch();
if(!$vendor||!password_verify($password,$vendor['password_hash'])){if(rate_limit('vendor-login|'.request_ip().'|'.$email,10,900))json_response(429,['success'=>false,'message'=>'Too many attempts. Try again later.']);json_response(401,['success'=>false,'message'=>'The sign-in details are incorrect.']);}
if(APP_ENV!=='local'&&$vendor['verification_status']==='demo_fixture')json_response(403,['success'=>false,'message'=>'Demo accounts are available only in the local test environment.']);
if(empty($vendor['email_verified']))json_response(403,['success'=>false,'message'=>'Verify your email before signing in.','code'=>'CONTACT_NOT_VERIFIED','email'=>$email]);
if(in_array($vendor['status'],['suspended','blocked','inactive'],true))json_response(403,['success'=>false,'message'=>'This vendor account is not currently permitted to sign in.','code'=>'VENDOR_ACCOUNT_RESTRICTED']);
$session=create_auth_session('vendor',$vendor['id'],$remember);get_db()->prepare('UPDATE vendors SET last_login_at=UTC_TIMESTAMP() WHERE id=:id')->execute(['id'=>$vendor['id']]);audit_event('vendor.login','vendor',$vendor['id'],[],['type'=>'vendor','id'=>$vendor['id']]);
json_response(200,['success'=>true,'message'=>'Signed in successfully.','csrf_token'=>$session['csrf_token'],'vendor'=>public_vendor($vendor),'redirect'=>vendor_next_route($vendor)]);
