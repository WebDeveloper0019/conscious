<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
send_cors_headers();

$principal = require_principal(['customer']);
$userId = $principal['id'];
 $db=get_db();
if(($_SERVER['REQUEST_METHOD']??'GET')==='GET'){
    $stmt=$db->prepare('SELECT * FROM users WHERE id=:id LIMIT 1'); $stmt->execute(['id'=>$userId]); $user=$stmt->fetch();
    if(!$user) json_response(404,['success'=>false,'message'=>'User not found.']);
    json_response(200,['success'=>true,'user'=>public_user($user)]);
}
if(!in_array($_SERVER['REQUEST_METHOD']??'', ['PUT','PATCH'], true)) json_response(405,['success'=>false,'message'=>'Method not allowed.']);
$body=get_json_body(); $first=trim((string)($body['firstName']??$body['first_name']??'')); $last=trim((string)($body['lastName']??$body['last_name']??''));
$mobile=preg_replace('/\D+/','',(string)($body['mobile']??$body['phone']??'')); $dob=trim((string)($body['dateOfBirth']??'')); $gender=trim((string)($body['gender']??'')); $anniversary=trim((string)($body['anniversaryDate']??''));
if(mb_strlen($first)<2||mb_strlen($first)>60||mb_strlen($last)<2||mb_strlen($last)>60) json_response(422,['success'=>false,'message'=>'Enter valid first and last names.']);
if(!is_valid_indian_mobile($mobile)) json_response(422,['success'=>false,'message'=>'Enter a valid 10-digit Indian mobile number.']);
foreach([$dob,$anniversary] as $date) if($date!==''&&!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)) json_response(422,['success'=>false,'message'=>'Dates must use YYYY-MM-DD format.']);
$stmt=$db->prepare('UPDATE users SET first_name=:first,last_name=:last,mobile=:mobile,date_of_birth=:dob,gender=:gender,anniversary_date=:anniversary,updated_at=:updated WHERE id=:id');
$stmt->execute(['first'=>$first,'last'=>$last,'mobile'=>$mobile,'dob'=>$dob?:null,'gender'=>$gender?:null,'anniversary'=>$anniversary?:null,'updated'=>date('Y-m-d H:i:s'),'id'=>$userId]);
$stmt=$db->prepare('SELECT * FROM users WHERE id=:id LIMIT 1'); $stmt->execute(['id'=>$userId]);
json_response(200,['success'=>true,'message'=>'Profile updated successfully.','user'=>public_user($stmt->fetch())]);
