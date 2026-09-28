<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
send_cors_headers();
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') json_response(405, ['success'=>false,'message'=>'Method not allowed.']);

$body = get_json_body();
$identifier = strtolower(trim((string)($body['identifier'] ?? $body['email'] ?? '')));
$password = (string)($body['password'] ?? '');
$remember = filter_var($body['remember'] ?? $body['rememberMe'] ?? false, FILTER_VALIDATE_BOOL);
if ($identifier === '' || $password === '') json_response(422, ['success'=>false,'message'=>'Enter your email or mobile number and password.']);

$db = get_db();
$digits = preg_replace('/\D+/', '', $identifier);
$stmt = $db->prepare(str_contains($identifier, '@') ? 'SELECT * FROM users WHERE email=:value LIMIT 1' : 'SELECT * FROM users WHERE mobile=:value LIMIT 1');
$stmt->execute(['value'=>str_contains($identifier, '@') ? $identifier : $digits]);
$user = $stmt->fetch();
if (!$user || !password_verify($password, $user['password_hash'])) {
    if (rate_limit('login|' . request_ip() . '|' . $identifier, 10, 900)) json_response(429, ['success'=>false,'message'=>'Too many attempts. Try again in 15 minutes.']);
    json_response(401, ['success'=>false,'message'=>'The sign-in details are incorrect.']);
}
if (empty($user['email_verified'])) json_response(403, ['success'=>false,'message'=>'Verify your email before signing in.','code'=>'EMAIL_NOT_VERIFIED','email'=>$user['email']]);

set_auth_cookie(issue_token($user, $remember), $remember);
$csrf = issue_csrf_token();
json_response(200, ['success'=>true,'message'=>'Signed in successfully.','user'=>public_user($user),'csrf_token'=>$csrf,'redirect'=>'../index.php']);
