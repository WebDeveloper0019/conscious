<?php
declare(strict_types=1);

function load_local_env(): void {
    $envFile = __DIR__ . '/.env';
    if (!is_file($envFile)) return;
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $value = trim($value, "\"'");
        if ($key !== '' && getenv($key) === false) putenv($key . '=' . $value);
    }
}
load_local_env();
date_default_timezone_set('UTC');

define('APP_ENV', getenv('APP_ENV') ?: 'local');
define('APP_URL', rtrim(getenv('APP_URL') ?: '', '/'));
define('APP_KEY', getenv('APP_KEY') ?: getenv('JWT_SECRET') ?: (APP_ENV === 'local' ? 'local-only-change-before-deployment-32-chars' : ''));
define('SESSION_TTL', 60 * 60 * 8);
define('SESSION_TTL_REMEMBER', 60 * 60 * 24 * 30);
define('AUTH_COOKIE', 'ci_session');
define('CSRF_COOKIE', 'ci_csrf');

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'conscious_indi');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('CORS_ORIGIN', rtrim(getenv('CORS_ORIGIN') ?: '', '/'));
define('RESEND_API_KEY', getenv('RESEND_API_KEY') ?: '');
define('RESEND_FROM', getenv('RESEND_FROM') ?: 'onboarding@resend.dev');
define('RESEND_FROM_NAME', getenv('RESEND_FROM_NAME') ?: 'Conscious Indi');
define('MAIL_TRANSPORT', getenv('MAIL_TRANSPORT') ?: (APP_ENV === 'local' ? 'log' : 'resend'));
define('PRIVATE_STORAGE_PATH', getenv('PRIVATE_STORAGE_PATH') ?: dirname(__DIR__) . '/storage/private');
define('MAX_UPLOAD_BYTES', (int)(getenv('MAX_UPLOAD_BYTES') ?: 10485760));
define('CLAMAV_BINARY', getenv('CLAMAV_BINARY') ?: '');
define('PAYMENT_WEBHOOK_SECRET', getenv('PAYMENT_WEBHOOK_SECRET') ?: '');
define('PAYMENT_GATEWAY', strtolower(getenv('PAYMENT_GATEWAY') ?: 'razorpay'));
define('RAZORPAY_KEY_ID', getenv('RAZORPAY_KEY_ID') ?: '');
define('RAZORPAY_KEY_SECRET', getenv('RAZORPAY_KEY_SECRET') ?: '');
define('RAZORPAY_WEBHOOK_SECRET', getenv('RAZORPAY_WEBHOOK_SECRET') ?: PAYMENT_WEBHOOK_SECRET);

if (APP_ENV !== 'local' && (strlen(APP_KEY) < 32 || str_contains(APP_KEY, 'replace-with'))) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Server authentication is not configured.']);
    exit;
}
if (APP_ENV !== 'local' && MAIL_TRANSPORT !== 'resend') {
    http_response_code(500);header('Content-Type: application/json; charset=utf-8');echo json_encode(['success'=>false,'message'=>'Production email transport is not configured.']);exit;
}
