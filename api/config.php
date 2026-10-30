<?php


// api/config.php

// 1. Load .env file properly
$envPath = dirname(__DIR__) . '/.env'; // Points to root .env


if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value, "\"'"); 
            putenv("$name=$value");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

date_default_timezone_set('UTC');

// 2. Define all constants safely
if (!defined('APP_ENV')) define('APP_ENV', getenv('APP_ENV') ?: 'local');
if (!defined('APP_URL')) define('APP_URL', rtrim(getenv('APP_URL') ?: '', '/'));
if (!defined('APP_KEY')) define('APP_KEY', getenv('APP_KEY') ?: getenv('JWT_SECRET') ?: (APP_ENV === 'local' ? 'local-only-change-before-deployment-32-chars' : ''));
if (!defined('SESSION_TTL')) define('SESSION_TTL', 60 * 60 * 8);
if (!defined('SESSION_TTL_REMEMBER')) define('SESSION_TTL_REMEMBER', 60 * 60 * 24 * 30);
if (!defined('AUTH_COOKIE')) define('AUTH_COOKIE', 'ci_session');
if (!defined('CSRF_COOKIE')) define('CSRF_COOKIE', 'ci_csrf');

if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
if (!defined('DB_PORT')) define('DB_PORT', getenv('DB_PORT') ?: '3306');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'conscious_indi');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') ?: '');
if (!defined('CORS_ORIGIN')) define('CORS_ORIGIN', rtrim(getenv('CORS_ORIGIN') ?: '', '/'));

// Mail (Resend) - NOW READS FROM .ENV
if (!defined('MAIL_TRANSPORT')) define('MAIL_TRANSPORT', getenv('MAIL_TRANSPORT') ?: 'resend');
if (!defined('RESEND_API_KEY')) define('RESEND_API_KEY', getenv('RESEND_API_KEY') ?: ''); 
if (!defined('RESEND_FROM')) define('RESEND_FROM', getenv('RESEND_FROM') ?: 'verify@conscious.drowdigital.in');
if (!defined('RESEND_FROM_NAME')) define('RESEND_FROM_NAME', getenv('RESEND_FROM_NAME') ?: 'Conscious India');

if (!defined('PRIVATE_STORAGE_PATH')) define('PRIVATE_STORAGE_PATH', getenv('PRIVATE_STORAGE_PATH') ?: dirname(__DIR__) . '/storage/private');
if (!defined('MAX_UPLOAD_BYTES')) define('MAX_UPLOAD_BYTES', (int)(getenv('MAX_UPLOAD_BYTES') ?: 10485760));
if (!defined('CLAMAV_BINARY')) define('CLAMAV_BINARY', getenv('CLAMAV_BINARY') ?: '');
if (!defined('PAYMENT_WEBHOOK_SECRET')) define('PAYMENT_WEBHOOK_SECRET', getenv('PAYMENT_WEBHOOK_SECRET') ?: '');
if (!defined('PAYMENT_GATEWAY')) define('PAYMENT_GATEWAY', strtolower(getenv('PAYMENT_GATEWAY') ?: 'razorpay'));
if (!defined('RAZORPAY_KEY_ID')) define('RAZORPAY_KEY_ID', getenv('RAZORPAY_KEY_ID') ?: '');
if (!defined('RAZORPAY_KEY_SECRET')) define('RAZORPAY_KEY_SECRET', getenv('RAZORPAY_KEY_SECRET') ?: '');
if (!defined('RAZORPAY_WEBHOOK_SECRET')) define('RAZORPAY_WEBHOOK_SECRET', getenv('RAZORPAY_WEBHOOK_SECRET') ?: PAYMENT_WEBHOOK_SECRET);