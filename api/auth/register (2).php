<?php
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';

send_cors_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

$body = get_json_body();

$firstName = trim($body['firstName'] ?? '');
$lastName = trim($body['lastName'] ?? '');
$email = trim($body['email'] ?? '');
$mobile = trim($body['mobile'] ?? '');
$password = $body['password'] ?? '';
$confirmPassword = $body['confirmPassword'] ?? '';
$agreeTerms = (bool) ($body['agreeTerms'] ?? false);

$errors = [];
if ($firstName === '') $errors['firstName'] = 'First name is required.';
if ($lastName === '') $errors['lastName'] = 'Last name is required.';
if (!is_valid_email($email)) $errors['email'] = 'Enter a valid email address.';
if ($mobile === '') $errors['mobile'] = 'Mobile number is required.';
if (!is_strong_enough_password($password)) $errors['password'] = 'Password must be at least 8 characters.';
if ($password !== $confirmPassword) $errors['confirmPassword'] = 'Passwords do not match.';
if (!$agreeTerms) $errors['agreeTerms'] = 'You must agree to the Terms of Use and Privacy Policy.';

if (!empty($errors)) {
    json_response(400, ['success' => false, 'message' => 'Please fix the errors below.', 'errors' => $errors]);
}

$db = get_db();

$check = $db->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
$check->execute(['email' => strtolower($email)]);
if ($check->fetch()) {
    json_response(409, [
        'success' => false,
        'message' => 'An account with this email already exists.',
        'errors' => ['email' => 'This email is already registered.'],
    ]);
}

$id = bin2hex(random_bytes(16));
// Format as a UUID-ish string for readability (not a strict UUIDv4, doesn't need to be).
$id = substr($id, 0, 8) . '-' . substr($id, 8, 4) . '-' . substr($id, 12, 4) . '-' . substr($id, 16, 4) . '-' . substr($id, 20, 12);

$passwordHash = password_hash($password, PASSWORD_BCRYPT);
$createdAt = date('Y-m-d H:i:s');

$insert = $db->prepare('
    INSERT INTO users (id, first_name, last_name, email, mobile, password_hash, created_at)
    VALUES (:id, :first_name, :last_name, :email, :mobile, :password_hash, :created_at)
');
$insert->execute([
    'id' => $id,
    'first_name' => $firstName,
    'last_name' => $lastName,
    'email' => strtolower($email),
    'mobile' => $mobile,
    'password_hash' => $passwordHash,
    'created_at' => $createdAt,
]);

$user = [
    'id' => $id,
    'first_name' => $firstName,
    'last_name' => $lastName,
    'email' => strtolower($email),
    'mobile' => $mobile,
    'created_at' => $createdAt,
];

$token = issue_token($user, false);

json_response(201, [
    'success' => true,
    'message' => 'Account created successfully.',
    'token' => $token,
    'user' => public_user($user),
]);
