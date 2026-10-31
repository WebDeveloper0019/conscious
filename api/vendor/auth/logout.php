<?php
// api/vendor/auth/logout.php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../lib/helpers.php';

send_cors_headers();

// 1. Destroy the PHP session
if (session_status() === PHP_SESSION_ACTIVE) {
    session_unset();
    session_destroy();
}

// 2. Clear the authentication cookies
$cookieOptions = [
    'expires' => time() - 3600,
    'path' => '/',
    'secure' => false, // Set to true if using HTTPS in production
    'httponly' => true,
    'samesite' => 'Lax'
];

if (defined('AUTH_COOKIE')) {
    setcookie(AUTH_COOKIE, '', $cookieOptions);
}
if (defined('CSRF_COOKIE')) {
    setcookie(CSRF_COOKIE, '', $cookieOptions);
}

// 3. Return success so the frontend redirects to login
json_response(200, [
    'success' => true, 
    'message' => 'Logged out successfully.',
    'redirect' => 'login.php' // or wherever your vendor login page is
]);