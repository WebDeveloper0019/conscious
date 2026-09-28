<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/jwt.php';

function send_cors_headers(): void {
    header('Access-Control-Allow-Origin: ' . CORS_ORIGIN);
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

/** Reads and JSON-decodes the request body into an assoc array. */
function get_json_body(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function json_response(int $statusCode, array $body): void {
    http_response_code($statusCode);
    echo json_encode($body);
    exit;
}

function is_valid_email(?string $email): bool {
    return is_string($email) && filter_var(trim($email), FILTER_VALIDATE_EMAIL) !== false;
}

function is_strong_enough_password(?string $password): bool {
    return is_string($password) && strlen($password) >= 8;
}

/** Builds the public-safe user array (never includes password_hash). */
function public_user(array $row): array {
    return [
        'id' => $row['id'],
        'firstName' => $row['first_name'],
        'lastName' => $row['last_name'],
        'email' => $row['email'],
        'mobile' => $row['mobile'],
        'dateOfBirth' => $row['date_of_birth'] ?? null,
        'gender' => $row['gender'] ?? null,
        'anniversaryDate' => $row['anniversary_date'] ?? null,
        'createdAt' => $row['created_at'],
        'updatedAt' => $row['updated_at'] ?? null,
    ];
}

function uuid_string(): string {
    $id = bin2hex(random_bytes(16));
    return substr($id, 0, 8) . '-' . substr($id, 8, 4) . '-' . substr($id, 12, 4) . '-' . substr($id, 16, 4) . '-' . substr($id, 20, 12);
}

function auth_user_id(array $payload): string {
    $id = (string)($payload['sub'] ?? '');
    if ($id === '') {
        json_response(401, ['success' => false, 'message' => 'Invalid authentication subject.']);
    }
    return $id;
}

function issue_token(array $row, bool $rememberMe): string {
    $ttl = $rememberMe ? JWT_TTL_REMEMBER : JWT_TTL;
    $payload = [
        'sub' => $row['id'],
        'email' => $row['email'],
        'iat' => time(),
        'exp' => time() + $ttl,
    ];
    return jwt_encode($payload, JWT_SECRET);
}

function get_bearer_token(): ?string {
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!$header && function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        $header = $headers['Authorization'] ?? '';
    }
    if (preg_match('/Bearer\s+(\S+)/', $header, $matches)) {
        return $matches[1];
    }
    return null;
}

/**
 * Verifies the bearer token and returns the decoded payload,
 * or sends a 401 JSON response and exits if it's missing/invalid.
 */
function require_auth(): array {
    $token = get_bearer_token();
    $payload = jwt_decode($token, JWT_SECRET);

    if (!$payload) {
        json_response(401, ['success' => false, 'message' => 'Invalid or expired token.']);
    }

    return $payload;
}
