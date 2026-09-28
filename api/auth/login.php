<?php
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';

send_cors_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

// Very small brute-force throttle based on file lock + attempt counter per email.
function too_many_attempts(string $email): bool {
    $dir = __DIR__ . '/../data/rate-limit';
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    $file = $dir . '/' . md5(strtolower($email)) . '.json';

    $now = time();
    $windowSeconds = 15 * 60;
    $maxAttempts = 10;

    $attempts = [];
    if (file_exists($file)) {
        $attempts = json_decode(file_get_contents($file), true) ?: [];
    }
    $attempts = array_values(array_filter($attempts, fn($t) => $t > $now - $windowSeconds));

    if (count($attempts) >= $maxAttempts) {
        return true;
    }

    $attempts[] = $now;
    file_put_contents($file, json_encode($attempts));
    return false;
}

$body = get_json_body();
$email = trim($body['email'] ?? '');
$password = $body['password'] ?? '';
$rememberMe = (bool) ($body['rememberMe'] ?? false);

if (!is_valid_email($email) || $password === '') {
    json_response(400, ['success' => false, 'message' => 'Enter a valid email and password.']);
}

if (too_many_attempts($email)) {
    json_response(429, ['success' => false, 'message' => 'Too many login attempts. Please try again later.']);
}

$db = get_db();
$stmt = $db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
$stmt->execute(['email' => strtolower($email)]);
$user = $stmt->fetch();

$genericError = ['success' => false, 'message' => 'Incorrect email or password.'];

if (!$user || !password_verify($password, $user['password_hash'])) {
    json_response(401, $genericError);
}

if (empty($user['email_verified'])) {
    json_response(403, [
        'success' => false,
        'message' => 'Please verify your email with the OTP before signing in.',
        'code' => 'EMAIL_NOT_VERIFIED',
        'email' => $user['email'],
    ]);
}

$token = issue_token($user, $rememberMe);

json_response(200, [
    'success' => true,
    'message' => 'Signed in successfully.',
    'token' => $token,
    'user' => public_user($user),
]);
