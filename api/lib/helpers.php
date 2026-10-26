<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/audit.php';
require_once __DIR__ . '/errors.php';
install_error_handlers();

function json_response(int $statusCode, array $body): void {
    if ($statusCode >= 400 && function_exists('get_db_if_connected')) {
        $db = get_db_if_connected();
        if ($db && $db->inTransaction()) $db->rollBack();
        if (function_exists('abort_idempotent_request')) abort_idempotent_request();
    }
    http_response_code($statusCode);
    if (!headers_sent()) header('Content-Type: application/json; charset=utf-8');
    echo json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}
function request_origin(): string
{
    if (APP_URL !== '') {
        $scheme = (string)parse_url(APP_URL, PHP_URL_SCHEME);
        $host = (string)parse_url(APP_URL, PHP_URL_HOST);
        $port = parse_url(APP_URL, PHP_URL_PORT);

        if ($scheme !== '' && $host !== '') {
            return $scheme . '://' . $host . ($port ? ':' . $port : '');
        }
    }

    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    return ($https ? 'https://' : 'http://') . (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
}
function send_cors_headers(): void {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    $origin = rtrim((string)($_SERVER['HTTP_ORIGIN'] ?? ''), '/');
    if ($origin !== '') {
        $allowed = CORS_ORIGIN !== '' ? CORS_ORIGIN : request_origin();
        if (!hash_equals($allowed, $origin)) json_response(403, ['success' => false, 'message' => 'Origin is not allowed.']);
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials: true');
        header('Vary: Origin');
    }
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token, Idempotency-Key');
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') { http_response_code(204); exit; }
}
function get_json_body(): array {
    if ((int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 1048576) json_response(413, ['success'=>false,'message'=>'Request body is too large.']);
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') return [];
    $data = json_decode($raw, true);
    if (!is_array($data) || (array_is_list($data) && $data !== [])) json_response(400, ['success' => false, 'message' => 'A JSON object is required.']);
    return $data;
}
function is_valid_email(?string $email): bool { return is_string($email) && filter_var(trim($email), FILTER_VALIDATE_EMAIL) !== false; }
function is_valid_indian_mobile(?string $mobile): bool { return is_string($mobile) && preg_match('/^[6-9][0-9]{9}$/', preg_replace('/\D+/', '', $mobile)) === 1; }
function is_strong_enough_password(?string $password): bool {
    return is_string($password) && strlen($password) >= 12 && strlen($password) <= 128
        && preg_match('/[a-z]/', $password) && preg_match('/[A-Z]/', $password)
        && preg_match('/[0-9]/', $password) && preg_match('/[^A-Za-z0-9]/', $password);
}
function public_user(array $row): array {
    return [
        'id' => $row['id'], 'first_name' => $row['first_name'], 'last_name' => $row['last_name'],
        'firstName' => $row['first_name'], 'lastName' => $row['last_name'], 'email' => $row['email'],
        'phone' => $row['mobile'], 'mobile' => $row['mobile'],
        'dateOfBirth' => $row['date_of_birth'] ?? null, 'gender' => $row['gender'] ?? null,
        'anniversaryDate' => $row['anniversary_date'] ?? null,
        'emailVerified' => !empty($row['email_verified']), 'createdAt' => $row['created_at'],
        'updatedAt' => $row['updated_at'] ?? null,
    ];
}
function uuid_string(): string {
    $bytes = random_bytes(16); $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40); $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
    $hex = bin2hex($bytes); return substr($hex,0,8).'-'.substr($hex,8,4).'-'.substr($hex,12,4).'-'.substr($hex,16,4).'-'.substr($hex,20);
}
function secure_cookie(): bool { return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || APP_ENV === 'production'; }
function clear_auth_cookie(): void { setcookie(AUTH_COOKIE, '', ['expires'=>time()-3600,'path'=>'/','secure'=>secure_cookie(),'httponly'=>true,'samesite'=>'Lax']); }
function issue_csrf_token(): string {
    return current_csrf_token();
}
function require_csrf(): void {
    verify_session_csrf();
}
function require_auth(): array {
    $principal=require_principal(['customer']); return ['sub'=>$principal['id'],'session_id'=>$principal['session_id'],'type'=>'customer'];
}
function auth_user_id(array $payload): string { return (string)$payload['sub']; }
function rate_limit(string $bucket, int $maxAttempts, int $windowSeconds): bool {
    $dir = __DIR__ . '/../data/rate-limit'; if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) return true;
    $file = $dir . '/' . hash('sha256', $bucket) . '.json'; $handle = fopen($file, 'c+'); if (!$handle) return true;
    try {
        if (!flock($handle, LOCK_EX)) return true; $raw = stream_get_contents($handle); $now = time();
        $attempts = array_values(array_filter(json_decode($raw ?: '[]', true) ?: [], fn($t) => is_int($t) && $t > $now-$windowSeconds));
        if (count($attempts) >= $maxAttempts) return true; $attempts[] = $now;
        ftruncate($handle, 0); rewind($handle); fwrite($handle, json_encode($attempts)); fflush($handle); return false;
    } finally { flock($handle, LOCK_UN); fclose($handle); }
}
function request_ip(): string { return (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown'); }
// Vendor-facing routes share the same request bootstrap; loading this once here
// prevents individual dashboard endpoints from accidentally omitting ownership
// and seller-status guards.
require_once __DIR__ . '/vendor.php';
