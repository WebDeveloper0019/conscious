<?php
/**
 * Central config. Edit the values below (or override with real environment
 * variables on your server) before deploying.
 */

// Secret used to sign JWT tokens — change this to a long random string in production.
define('JWT_SECRET', getenv('JWT_SECRET') ?: 'change-this-to-a-long-random-secret-string');

// Token lifetime in seconds
define('JWT_TTL', 60 * 60 * 24);            // 1 day for a normal login
define('JWT_TTL_REMEMBER', 60 * 60 * 24 * 30); // 30 days for "keep me signed in"

// MySQL connection settings — match whatever you set up in phpMyAdmin.
// (Local dev under XAMPP/WAMP/MAMP defaults: host localhost, user root, empty password.)
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'conscious_indi');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Allow the API to be called from a different origin than it's hosted on.
// Set to '*' during local development, or a specific origin in production,
// e.g. 'https://www.yourdomain.com'
define('CORS_ORIGIN', getenv('CORS_ORIGIN') ?: '*');

// Resend email configuration. Put your real API key in api/.env (never commit it).
function load_local_env(): void {
    $envFile = __DIR__ . '/.env';
    if (!is_file($envFile)) return;
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key); $value = trim($value);
        if ($value !== '' && !getenv($key)) putenv($key . '=' . $value);
    }
}
load_local_env();
define('RESEND_API_KEY', getenv('RESEND_API_KEY') ?: '');
define('RESEND_FROM', getenv('RESEND_FROM') ?: 'onboarding@resend.dev');
define('RESEND_FROM_NAME', getenv('RESEND_FROM_NAME') ?: 'Conscious Indi');

