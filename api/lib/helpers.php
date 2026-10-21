<?php
declare(strict_types=1);
if (!defined('APP_KEY')) define('APP_KEY', 'your-super-secret-32-character-key-here');
// Define the secure upload path (points to the storage folder in your project root)
if (!defined('PRIVATE_STORAGE_PATH')) {
    define('PRIVATE_STORAGE_PATH', realpath(__DIR__ . '/../../storage') . DIRECTORY_SEPARATOR . 'private_uploads');
}

// Define the max upload size (5 MB)
if (!defined('MAX_UPLOAD_BYTES')) {
    define('MAX_UPLOAD_BYTES', 5 * 1024 * 1024);
}

if (!defined('APP_ENV')) define('APP_ENV', getenv('APP_ENV') ?: 'local');
if (!defined('APP_URL')) define('APP_URL', getenv('APP_URL') ?: '');
if (!defined('CSRF_COOKIE')) define('CSRF_COOKIE', 'ci_csrf');
if (!defined('SESSION_COOKIE')) define('SESSION_COOKIE', 'ci_session');

/* ── CORS (only enable if frontend is on a different origin) ── */
function send_cors_headers(): void {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $allowed = array_filter(array_map('trim', explode(',', (string)getenv('CORS_ORIGINS'))));
    if ($origin && in_array($origin, $allowed, true)) {
        header("Access-Control-Allow-Origin: $origin");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
    }
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') { http_response_code(204); exit; }
}

/* ── DB ── */
function get_db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $name = getenv('DB_NAME') ?: 'conscious_indi';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASS') ?: '';
    $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    $pdo->exec("SET time_zone = '+00:00'");
    return $pdo;
}

/* ── JSON I/O ── */
function json_response(int $code, array $payload): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function get_json_body(): array {
    $raw = file_get_contents('php://input') ?: '';
    if ($raw === '') return $_POST;
    $data = json_decode($raw, true);
    return is_array($data) ? $data : $_POST;
}

function request_ip(): string {
    return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/* ── Validation ── */
function is_valid_email(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function is_strong_enough_password(string $pw): bool {
    return strlen($pw) >= 12
        && preg_match('/[A-Z]/', $pw)
        && preg_match('/[a-z]/', $pw)
        && preg_match('/[0-9]/', $pw)
        && preg_match('/[^A-Za-z0-9]/', $pw);
}

/* ── Utility ── */
function uuid_string(): string {
    $d = random_bytes(16);
    $d[6] = chr((ord($d[6]) & 0x0f) | 0x40);
    $d[8] = chr((ord($d[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($d), 4));
}

/* ── Rate limiting (file-backed) ── */
function rate_limit(string $key, int $max, int $windowSeconds): bool {
    $dir = sys_get_temp_dir() . '/ci_rl';
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    $file = $dir . '/' . md5($key) . '.json';
    $now  = time();
    $data = file_exists($file) ? json_decode((string)@file_get_contents($file), true) : null;
    if (!is_array($data) || ($data['reset'] ?? 0) < $now) {
        $data = ['count' => 0, 'reset' => $now + $windowSeconds];
    }
    $data['count']++;
    @file_put_contents($file, json_encode($data), LOCK_EX);
    return $data['count'] > $max;
}

/* ── Audit ── */
function audit_event(string $event, string $type, string $id, array $meta = [], array $actor = []): void {
    try {
        get_db()->prepare(
            'INSERT INTO audit_log (id, event, target_type, target_id, actor_type, actor_id, meta_json, created_at)
             VALUES (:id, :ev, :tt, :tid, :at, :aid, :meta, UTC_TIMESTAMP())'
        )->execute([
            'id'   => uuid_string(),
            'ev'   => $event,
            'tt'   => $type,
            'tid'  => $id,
            'at'   => $actor['type'] ?? null,
            'aid'  => $actor['id']   ?? null,
            'meta' => json_encode($meta),
        ]);
    } catch (Throwable $e) { /* ignore */ }
}

/* ── Sessions (cookie-based) ── */
function create_auth_session(string $ptype, string $pid, bool $remember = false): array {
    $token   = bin2hex(random_bytes(32));
    $csrf    = bin2hex(random_bytes(24));
    $expires = time() + ($remember ? 60 * 60 * 24 * 30 : 60 * 60 * 8);
    
    get_db()->prepare(
        'INSERT INTO auth_sessions
         (id, token_hash, principal_type, principal_id, csrf_token, remember, ip, user_agent, expires_at, created_at)
         VALUES (:id, :th, :pt, :pid, :csrf, :rem, :ip, :ua, FROM_UNIXTIME(:exp), UTC_TIMESTAMP())'
    )->execute([
        'id'   => uuid_string(),
        'th'   => hash('sha256', $token),
        'pt'   => $ptype,
        'pid'  => $pid,
        'csrf' => $csrf,
        'rem'  => $remember ? 1 : 0,
        'ip'   => request_ip(),
        'ua'   => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        'exp'  => $expires,
    ]);
    
    $cookieBase = [
        'expires'  => $expires,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']),
        'samesite' => 'Lax',
    ];
    setcookie(SESSION_COOKIE, $token, $cookieBase + ['httponly' => true]);
    setcookie(CSRF_COOKIE,   $csrf,  $cookieBase + ['httponly' => false]);
    
    return ['token' => $token, 'csrf_token' => $csrf, 'expires_at' => $expires];
}

function current_session(): ?array {
    $token = $_COOKIE[SESSION_COOKIE] ?? '';
    if (!$token) return null;
    
    $stmt = get_db()->prepare(
        'SELECT * FROM auth_sessions WHERE token_hash = :h AND expires_at > UTC_TIMESTAMP() LIMIT 1'
    );
    $stmt->execute(['h' => hash('sha256', $token)]);
    return $stmt->fetch() ?: null;
}

function destroy_auth_session(): void {
    $token = $_COOKIE[SESSION_COOKIE] ?? '';
    if ($token) {
        get_db()->prepare('DELETE FROM auth_sessions WHERE token_hash = :h')
                ->execute(['h' => hash('sha256', $token)]);
    }
    setcookie(SESSION_COOKIE, '', ['expires' => time() - 3600, 'path' => '/']);
    setcookie(CSRF_COOKIE,   '', ['expires' => time() - 3600, 'path' => '/']);
}

function require_csrf(): void {
    $session = current_session();
    if (!$session) json_response(401, ['success' => false, 'message' => 'Not signed in.']);
    $sent = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals((string)$session['csrf_token'], (string)$sent)) {
        json_response(403, ['success' => false, 'message' => 'Invalid CSRF token.']);
    }
}

function require_influencer_session(): array {
    $s = current_session();
    if (!$s || $s['principal_type'] !== 'influencer') {
        json_response(401, ['success' => false, 'message' => 'Sign in required.']);
    }
    $stmt = get_db()->prepare('SELECT * FROM influencers WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $s['principal_id']]);
    $creator = $stmt->fetch();
    if (!$creator) json_response(401, ['success' => false, 'message' => 'Session invalid.']);
    return ['creator' => $creator, 'session' => $s];
}
function current_auth_session(): ?array {
    return current_session();
}
// Alias for application.php
function require_principal(array $types): array {
    $s = current_session();
    if (!$s || !in_array($s['principal_type'], $types)) {
        json_response(401, ['success' => false, 'message' => 'Sign in required.']);
    }
    return ['id' => $s['principal_id'], 'type' => $s['principal_type']];
}

// Dummy function to prevent crashes if status_history table is missing
function record_status_change(string $type, string $id, string $from, string $to, string $reason = ''): void {
    try {
        // If you have a status_history table, it would go here.
        // For now, we just silently succeed so the app doesn't crash.
    } catch (Throwable $e) {}
}