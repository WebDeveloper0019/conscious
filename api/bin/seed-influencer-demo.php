<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require_once __DIR__.'/../lib/helpers.php';
if(APP_ENV!=='local'){fwrite(STDERR,"Demo seeding is allowed only in APP_ENV=local.\n");exit(1);}
$email='creator.demo@conscious.test';
$password='DemoCreator#2026!';
$db=get_db();
$existing=$db->prepare('SELECT id FROM influencers WHERE email=:email LIMIT 1');
$existing->execute(['email'=>$email]);
if(!$existing->fetch()){
    $id=uuid_string();
    $db->beginTransaction();
    try{
        $db->prepare('INSERT INTO influencers (id,full_name,email,mobile,password_hash,email_verified,handle,bio,platforms_json,audience_json,status,verification_status,created_at) VALUES (:id,:name,:email,:mobile,:password,1,:handle,:bio,:platforms,:audience,"application_draft","not_submitted",UTC_TIMESTAMP())')->execute(['id'=>$id,'name'=>'Demo Conscious Creator','email'=>$email,'mobile'=>'9876543210','password'=>password_hash($password,PASSWORD_DEFAULT),'handle'=>'demo-conscious-creator','bio'=>'I share practical stories about slow living, handmade goods and responsible shopping with an India-wide audience. This is a local demo profile only.','platforms'=>json_encode([['name'=>'Instagram','url'=>'https://www.instagram.com/','followers'=>1200]],JSON_UNESCAPED_SLASHES),'audience'=>json_encode(['region'=>'India-wide'])]);
        $db->prepare('INSERT INTO principal_roles (principal_type,principal_id,role_id,granted_at) SELECT "influencer",:id,id,UTC_TIMESTAMP() FROM roles WHERE code="influencer"')->execute(['id'=>$id]);
        $db->commit();
    }catch(Throwable $error){if($db->inTransaction())$db->rollBack();throw $error;}
}
fwrite(STDOUT,"Local demo creator: {$email}\nPassword: {$password}\nStatus: application draft; no documents or approvals were fabricated.\n");
