<?php
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';

send_cors_headers();
$payload = require_auth();
$userId = auth_user_id($payload);
$db = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch();
    if (!$user) json_response(404, ['success'=>false,'message'=>'User not found.']);
    json_response(200, ['success'=>true,'user'=>public_user($user)]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'PATCH') {
    json_response(405, ['success'=>false,'message'=>'Method not allowed.']);
}

$body = get_json_body();
$firstName = trim((string)($body['firstName'] ?? ''));
$lastName = trim((string)($body['lastName'] ?? ''));
$email = strtolower(trim((string)($body['email'] ?? '')));
$mobile = trim((string)($body['mobile'] ?? ''));
$dob = trim((string)($body['dateOfBirth'] ?? ''));
$gender = trim((string)($body['gender'] ?? ''));
$anniversary = trim((string)($body['anniversaryDate'] ?? ''));

if ($firstName === '' || $lastName === '') json_response(422, ['success'=>false,'message'=>'First name and last name are required.']);
if (!is_valid_email($email)) json_response(422, ['success'=>false,'message'=>'Please enter a valid email address.']);
if ($mobile === '') json_response(422, ['success'=>false,'message'=>'Mobile number is required.']);

foreach (['dateOfBirth'=>$dob,'anniversaryDate'=>$anniversary] as $field=>$value) {
    if ($value !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        json_response(422, ['success'=>false,'message'=>"$field must use YYYY-MM-DD format."]);
    }
}

$check = $db->prepare('SELECT id FROM users WHERE email = :email AND id <> :id LIMIT 1');
$check->execute(['email'=>$email,'id'=>$userId]);
if ($check->fetch()) json_response(409, ['success'=>false,'message'=>'That email address is already in use.']);

$stmt = $db->prepare('
    UPDATE users
    SET first_name=:first_name,last_name=:last_name,email=:email,mobile=:mobile,
        date_of_birth=:dob,gender=:gender,anniversary_date=:anniversary,updated_at=:updated_at
    WHERE id=:id
');
$stmt->execute([
    'first_name'=>$firstName,'last_name'=>$lastName,'email'=>$email,'mobile'=>$mobile,
    'dob'=>$dob !== '' ? $dob : null,'gender'=>$gender !== '' ? $gender : null,
    'anniversary'=>$anniversary !== '' ? $anniversary : null,
    'updated_at'=>date('Y-m-d H:i:s'),'id'=>$userId
]);

$stmt = $db->prepare('SELECT * FROM users WHERE id=:id LIMIT 1');
$stmt->execute(['id'=>$userId]);
$user=$stmt->fetch();

json_response(200,['success'=>true,'message'=>'Profile updated successfully.','user'=>public_user($user)]);
