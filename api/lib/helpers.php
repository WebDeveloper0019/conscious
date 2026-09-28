<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/jwt.php';

function json_response(int $statusCode, array $body): void {
    http_response_code($statusCode);
    echo json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}
function request_origin(): string {
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
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token');
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') { http_response_code(204); exit; }
}
function get_json_body(): array {
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') return [];
    $data = json_decode($raw, true);
    if (!is_array($data)) json_response(400, ['success' => false, 'message' => 'Invalid JSON request.']);
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
function issue_token(array $row, bool $remember): string {
    $now = time(); return jwt_encode(['sub' => $row['id'], 'iat' => $now, 'exp' => $now + ($remember ? JWT_TTL_REMEMBER : JWT_TTL)], JWT_SECRET);
}
function secure_cookie(): bool { return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || APP_ENV === 'production'; }
function set_auth_cookie(string $token, bool $remember): void {
    setcookie(AUTH_COOKIE, $token, ['expires' => $remember ? time()+JWT_TTL_REMEMBER : 0, 'path' => '/', 'secure' => secure_cookie(), 'httponly' => true, 'samesite' => 'Lax']);
}
function clear_auth_cookie(): void { setcookie(AUTH_COOKIE, '', ['expires'=>time()-3600,'path'=>'/','secure'=>secure_cookie(),'httponly'=>true,'samesite'=>'Lax']); }
function issue_csrf_token(): string {
    $token = bin2hex(random_bytes(24));
    setcookie(CSRF_COOKIE, $token, ['expires'=>0,'path'=>'/','secure'=>secure_cookie(),'httponly'=>false,'samesite'=>'Lax']); return $token;
}
function require_csrf(): void {
    if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET','HEAD','OPTIONS'], true)) return;
    $cookie = (string)($_COOKIE[CSRF_COOKIE] ?? ''); $header = (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if ($cookie === '' || $header === '' || !hash_equals($cookie, $header)) json_response(419, ['success'=>false,'message'=>'Your security token expired. Refresh and try again.']);
}
function get_bearer_token(): ?string {
    if (!empty($_COOKIE[AUTH_COOKIE])) return (string)$_COOKIE[AUTH_COOKIE];
    $header = (string)($_SERVER['HTTP_AUTHORIZATION'] ?? ''); return preg_match('/^Bearer\s+(\S+)$/i', $header, $m) ? $m[1] : null;
}
function require_auth(): array {
    $payload = jwt_decode(get_bearer_token(), JWT_SECRET);
    if (!$payload || empty($payload['sub'])) json_response(401, ['success'=>false,'message'=>'Your session has expired. Please sign in again.']); require_csrf(); return $payload;
}
function auth_user_id(array $payload): string { return (string)$payload['sub']; }
function rate_limit(string $bucket, int $maxAttempts, int $windowSeconds): bool {
    $dir = __DIR__ . '/../data/rate-limit'; if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) return false;
    $file = $dir . '/' . hash('sha256', $bucket) . '.json'; $handle = fopen($file, 'c+'); if (!$handle) return false;
    try {
        if (!flock($handle, LOCK_EX)) return false; $raw = stream_get_contents($handle); $now = time();
        $attempts = array_values(array_filter(json_decode($raw ?: '[]', true) ?: [], fn($t) => is_int($t) && $t > $now-$windowSeconds));
        if (count($attempts) >= $maxAttempts) return true; $attempts[] = $now;
        ftruncate($handle, 0); rewind($handle); fwrite($handle, json_encode($attempts)); fflush($handle); return false;
    } finally { flock($handle, LOCK_UN); fclose($handle); }
}
function request_ip(): string { return (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown'); }
