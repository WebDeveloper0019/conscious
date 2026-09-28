<?php
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/resend.php';

send_cors_headers();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
$body = get_json_body();
$firstName = trim($body['firstName'] ?? ''); $lastName = trim($body['lastName'] ?? '');
$email = strtolower(trim($body['email'] ?? '')); $mobile = trim($body['mobile'] ?? '');
$password = $body['password'] ?? ''; $confirmPassword = $body['confirmPassword'] ?? '';
$agreeTerms = (bool)($body['agreeTerms'] ?? false);
$errors=[];
if($firstName==='')$errors['firstName']='First name is required.';
if($lastName==='')$errors['lastName']='Last name is required.';
if(!is_valid_email($email))$errors['email']='Enter a valid email address.';
if($mobile==='')$errors['mobile']='Mobile number is required.';
if(!is_strong_enough_password($password))$errors['password']='Password must be at least 8 characters.';
if($password!==$confirmPassword)$errors['confirmPassword']='Passwords do not match.';
if(!$agreeTerms)$errors['agreeTerms']='You must agree to the Terms of Use and Privacy Policy.';
if($errors)json_response(400,['success'=>false,'message'=>'Please fix the errors below.','errors'=>$errors]);
$db=get_db();
$check=$db->prepare('SELECT id,email_verified FROM users WHERE email=:email LIMIT 1'); $check->execute(['email'=>$email]); $existing=$check->fetch();
if($existing){
  if(empty($existing['email_verified'])) json_response(409,['success'=>false,'message'=>'This email is registered but not verified. Please verify the OTP or use resend OTP.','code'=>'EMAIL_NOT_VERIFIED']);
  json_response(409,['success'=>false,'message'=>'An account with this email already exists.','errors'=>['email'=>'This email is already registered.']]);
}
$id=bin2hex(random_bytes(16)); $id=substr($id,0,8).'-'.substr($id,8,4).'-'.substr($id,12,4).'-'.substr($id,16,4).'-'.substr($id,20,12);
$passwordHash=password_hash($password,PASSWORD_BCRYPT); $createdAt=date('Y-m-d H:i:s');
$otp=(string)random_int(100000,999999); $otpHash=password_hash($otp,PASSWORD_DEFAULT); $otpExpires=date('Y-m-d H:i:s',time()+600);
$insert=$db->prepare('INSERT INTO users (id,first_name,last_name,email,mobile,password_hash,created_at,email_verified,otp_hash,otp_expires_at,otp_attempts,otp_last_sent_at) VALUES (:id,:first_name,:last_name,:email,:mobile,:password_hash,:created_at,0,:otp_hash,:otp_expires_at,0,:otp_last_sent_at)');
$insert->execute(['id'=>$id,'first_name'=>$firstName,'last_name'=>$lastName,'email'=>$email,'mobile'=>$mobile,'password_hash'=>$passwordHash,'created_at'=>$createdAt,'otp_hash'=>$otpHash,'otp_expires_at'=>$otpExpires,'otp_last_sent_at'=>$createdAt]);
[$sent,$mailMessage]=send_otp_email($email,$firstName,$otp);
if(!$sent){ $db->prepare('DELETE FROM users WHERE id=:id')->execute(['id'=>$id]); json_response(502,['success'=>false,'message'=>'Account could not be created because the verification email could not be sent.','details'=>$mailMessage]); }
json_response(201,['success'=>true,'message'=>'Account created. We sent a verification OTP to your email.','requiresVerification'=>true,'email'=>$email]);
