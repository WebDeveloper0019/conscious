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

define('APP_ENV', getenv('APP_ENV') ?: 'local');
define('APP_URL', rtrim(getenv('APP_URL') ?: '', '/'));
define('JWT_SECRET', getenv('JWT_SECRET') ?: (APP_ENV === 'local' ? 'local-only-change-before-deployment-32-chars' : ''));
define('JWT_TTL', 60 * 60 * 8);
define('JWT_TTL_REMEMBER', 60 * 60 * 24 * 30);
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

if (APP_ENV !== 'local' && (strlen(JWT_SECRET) < 32 || JWT_SECRET === 'replace-with-at-least-32-random-characters')) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Server authentication is not configured.']);
    exit;
}
