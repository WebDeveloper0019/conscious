<?php
if(!defined('APP_ENV')) define('APP_ENV', getenv('APP_ENV') ?: 'local');
if(!defined('APP_URL')) define('APP_URL', rtrim(getenv('APP_URL') ?: '', '/'));
if(!defined('APP_KEY')) define('APP_KEY', getenv('APP_KEY') ?: getenv('JWT_SECRET') ?: (APP_ENV === 'local' ? 'local-only-change-before-deployment-32-chars' : ''));
if(!defined('SESSION_TTL')) define('SESSION_TTL', 60 * 60 * 8);
if(!defined('SESSION_TTL_REMEMBER')) define('SESSION_TTL_REMEMBER', 60 * 60 * 24 * 30);
if(!defined('AUTH_COOKIE')) define('AUTH_COOKIE', 'ci_session');
if(!defined('CSRF_COOKIE')) define('CSRF_COOKIE', 'ci_csrf');

<<<<<<< HEAD
if(!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
if(!defined('DB_PORT')) define('DB_PORT', getenv('DB_PORT') ?: '3306');
if(!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'conscious_indi');
if(!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
if(!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') ?: '');
if(!defined('CORS_ORIGIN')) define('CORS_ORIGIN', rtrim(getenv('CORS_ORIGIN') ?: '', '/'));
if(!defined('RESEND_API_KEY')) define('RESEND_API_KEY', getenv('RESEND_API_KEY') ?: '');
if(!defined('RESEND_FROM')) define('RESEND_FROM', getenv('RESEND_FROM') ?: 'onboarding@resend.dev');
if(!defined('RESEND_FROM_NAME')) define('RESEND_FROM_NAME', getenv('RESEND_FROM_NAME') ?: 'Conscious Indi');
if(!defined('MAIL_TRANSPORT')) define('MAIL_TRANSPORT', getenv('MAIL_TRANSPORT') ?: (APP_ENV === 'local' ? 'log' : 'resend'));
if(!defined('PRIVATE_STORAGE_PATH')) define('PRIVATE_STORAGE_PATH', getenv('PRIVATE_STORAGE_PATH') ?: dirname(__DIR__) . '/storage/private');
if(!defined('MAX_UPLOAD_BYTES')) define('MAX_UPLOAD_BYTES', (int)(getenv('MAX_UPLOAD_BYTES') ?: 10485760));
if(!defined('CLAMAV_BINARY')) define('CLAMAV_BINARY', getenv('CLAMAV_BINARY') ?: '');
if(!defined('PAYMENT_WEBHOOK_SECRET')) define('PAYMENT_WEBHOOK_SECRET', getenv('PAYMENT_WEBHOOK_SECRET') ?: '');
if(!defined('PAYMENT_GATEWAY')) define('PAYMENT_GATEWAY', strtolower(getenv('PAYMENT_GATEWAY') ?: 'razorpay'));
if(!defined('RAZORPAY_KEY_ID')) define('RAZORPAY_KEY_ID', getenv('RAZORPAY_KEY_ID') ?: '');
if(!defined('RAZORPAY_KEY_SECRET')) define('RAZORPAY_KEY_SECRET', getenv('RAZORPAY_KEY_SECRET') ?: '');
if(!defined('RAZORPAY_WEBHOOK_SECRET')) define('RAZORPAY_WEBHOOK_SECRET', getenv('RAZORPAY_WEBHOOK_SECRET') ?: PAYMENT_WEBHOOK_SECRET);
=======
function load_app_env(): void
{
    $configured = trim((string)(getenv('APP_ENV_FILE') ?: ''));
    $candidates = array_filter([$configured, __DIR__ . '/.env', dirname(__DIR__, 2) . '/.env']);
    $envFile = null;

    foreach ($candidates as $candidate) {
        if (is_file($candidate)) {
            $envFile = $candidate;
            break;
        }
    }

    if ($envFile === null) {
        return;
    }

    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $value = trim($value, "\"'");
        if ($key !== '' && getenv($key) === false) {
            putenv($key . '=' . $value);
        }
    }
}

load_app_env();
date_default_timezone_set('UTC');

if ((getenv('APP_ENV') ?: 'local') === 'production') {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
}

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
define('RESEND_FROM_NAME', getenv('RESEND_FROM_NAME') ?: 'Conscious India');
define('MAIL_TRANSPORT', getenv('MAIL_TRANSPORT') ?: (APP_ENV === 'local' ? 'log' : 'resend'));
define('PRIVATE_STORAGE_PATH', getenv('PRIVATE_STORAGE_PATH') ?: dirname(__DIR__) . '/storage/private');
define('MAX_UPLOAD_BYTES', (int)(getenv('MAX_UPLOAD_BYTES') ?: 10485760));
define('CLAMAV_BINARY', getenv('CLAMAV_BINARY') ?: '');
define('PAYMENT_WEBHOOK_SECRET', getenv('PAYMENT_WEBHOOK_SECRET') ?: '');
define('PAYMENT_GATEWAY', strtolower(getenv('PAYMENT_GATEWAY') ?: 'razorpay'));
define('RAZORPAY_KEY_ID', getenv('RAZORPAY_KEY_ID') ?: '');
define('RAZORPAY_KEY_SECRET', getenv('RAZORPAY_KEY_SECRET') ?: '');
define('RAZORPAY_WEBHOOK_SECRET', getenv('RAZORPAY_WEBHOOK_SECRET') ?: PAYMENT_WEBHOOK_SECRET);

if (APP_ENV === 'production' && (!str_starts_with(APP_URL, 'https://') || parse_url(APP_URL, PHP_URL_HOST) === null)) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Production application URL must use HTTPS.']);
    exit;
}

if (APP_ENV !== 'local' && (strlen(APP_KEY) < 32 || str_contains(APP_KEY, 'replace-with'))) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Server authentication is not configured.']);
    exit;
}
if (APP_ENV !== 'local' && MAIL_TRANSPORT !== 'resend') {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Production email transport is not configured.']);
    exit;
}
>>>>>>> main
