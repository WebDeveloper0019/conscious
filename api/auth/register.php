<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/resend.php';
send_cors_headers();
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') json_response(405, ['success'=>false,'message'=>'Method not allowed.']);
// if (rate_limit('register|' . request_ip(), 8, 3600)) json_response(429, ['success'=>false,'message'=>'Too many registration attempts. Try again later.']);

$body=get_json_body();
$first=trim((string)($body['first_name'] ?? $body['firstName'] ?? ''));
$last=trim((string)($body['last_name'] ?? $body['lastName'] ?? ''));
$email=strtolower(trim((string)($body['email'] ?? '')));
$mobile=preg_replace('/\D+/', '', (string)($body['phone'] ?? $body['mobile'] ?? ''));
$password=(string)($body['password'] ?? '');
$terms=filter_var($body['terms'] ?? $body['agreeTerms'] ?? false, FILTER_VALIDATE_BOOL);
$marketing=filter_var($body['marketing'] ?? false, FILTER_VALIDATE_BOOL);
$errors=[];
if (mb_strlen($first)<2 || mb_strlen($first)>60) $errors['first_name']='Enter a valid first name.';
if (mb_strlen($last)<2 || mb_strlen($last)>60) $errors['last_name']='Enter a valid last name.';
if (!is_valid_email($email)) $errors['email']='Enter a valid email address.';
if (!is_valid_indian_mobile($mobile)) $errors['phone']='Enter a valid 10-digit Indian mobile number.';
if (!is_strong_enough_password($password)) $errors['password']='Use 12+ characters with upper, lower, number and symbol.';
if (!$terms) $errors['terms']='You must accept the Terms and Privacy Notice.';
if ($errors) json_response(422, ['success'=>false,'message'=>'Please correct the highlighted fields.','errors'=>$errors]);

$db=get_db();
$stmt=$db->prepare('SELECT id,email,mobile,email_verified,otp_last_sent_at FROM users WHERE email=:email LIMIT 1');
$stmt->execute(['email'=>$email]); $existing=$stmt->fetch();
$mobileCheck=$db->prepare('SELECT id FROM users WHERE mobile=:mobile AND email<>:email LIMIT 1'); $mobileCheck->execute(['mobile'=>$mobile,'email'=>$email]);
if($mobileCheck->fetch()) json_response(409,['success'=>false,'message'=>'An account already uses this mobile number.','errors'=>['phone'=>'Try signing in or use another mobile number.']]);
if ($existing && !empty($existing['email_verified'])) json_response(409, ['success'=>false,'message'=>'An account already uses these details.','errors'=>['email'=>'Try signing in or recovering your password.']]);

$otp=(string)random_int(100000,999999); $otpHash=password_hash($otp,PASSWORD_DEFAULT); $now=date('Y-m-d H:i:s'); $expires=date('Y-m-d H:i:s',time()+600);
$db->beginTransaction();
try {
    if ($existing) {
        $id=$existing['id'];
        $stmt=$db->prepare('UPDATE users SET first_name=:first,last_name=:last,mobile=:mobile,password_hash=:hash,marketing_opt_in=:marketing,otp_hash=:otp,otp_expires_at=:expires,otp_attempts=0,otp_last_sent_at=:sent WHERE id=:id');
        $stmt->execute(['first'=>$first,'last'=>$last,'mobile'=>$mobile,'hash'=>password_hash($password,PASSWORD_DEFAULT),'marketing'=>$marketing?1:0,'otp'=>$otpHash,'expires'=>$expires,'sent'=>$now,'id'=>$id]);
    } else {
        $id=uuid_string();
        $stmt=$db->prepare('INSERT INTO users (id,first_name,last_name,email,mobile,password_hash,email_verified,marketing_opt_in,otp_hash,otp_expires_at,otp_attempts,otp_last_sent_at,created_at) VALUES (:id,:first,:last,:email,:mobile,:hash,0,:marketing,:otp,:expires,0,:sent,:created)');
        $stmt->execute(['id'=>$id,'first'=>$first,'last'=>$last,'email'=>$email,'mobile'=>$mobile,'hash'=>password_hash($password,PASSWORD_DEFAULT),'marketing'=>$marketing?1:0,'otp'=>$otpHash,'expires'=>$expires,'sent'=>$now,'created'=>$now]);
    }
    [$sent,$mailError]=send_otp_email($email,$first,$otp);
    if (!$sent) { error_log('OTP email failed: '.$mailError); throw new RuntimeException('mail'); }
    $db->commit();
} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    json_response(502, ['success'=>false,'message'=>'We could not send the verification email. Please try again.']);
}
json_response(201, ['success'=>true,'message'=>'We sent a 6-digit verification code to your email.','requiresVerification'=>true,'email'=>$email,'redirect'=>'verify-email.php']);
