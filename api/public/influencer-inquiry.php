<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
require_once __DIR__.'/../lib/notifications.php';
send_cors_headers();
if(($_SERVER['REQUEST_METHOD']??'')!=='POST')json_response(405,['success'=>false,'message'=>'Method not allowed.']);
if(rate_limit('creator-inquiry|'.request_fingerprint(request_ip()),5,3600))json_response(429,['success'=>false,'message'=>'Too many inquiries. Please try again later.']);
$body=get_json_body();
$handle=strtolower(trim((string)($body['handle']??'')));
$name=trim((string)($body['name']??''));
$email=strtolower(trim((string)($body['email']??'')));
$phone=preg_replace('/\D+/','',(string)($body['phone']??''));
$package=trim((string)($body['package']??''));
$message=trim((string)($body['message']??''));
if(!preg_match('/^[a-z0-9._-]{3,100}$/',$handle)||mb_strlen($name)<2||mb_strlen($name)>140||!is_valid_email($email)||!preg_match('/^[6-9][0-9]{9}$/',$phone)||!in_array($package,['Basic','Gold','Platinum'],true)||mb_strlen($message)>3000)
    json_response(422,['success'=>false,'message'=>'Check your name, email, Indian mobile, package and message.']);
$db=get_db();
$creator=$db->prepare('SELECT id FROM influencers WHERE handle=:handle AND status="active" AND email_verified=1 LIMIT 1');
$creator->execute(['handle'=>$handle]);
$id=$creator->fetchColumn();
if(!$id)json_response(404,['success'=>false,'message'=>'This creator is not available for inquiries.']);
$inquiry=uuid_string();
$db->prepare('INSERT INTO influencer_inquiries (id,influencer_id,sender_name,sender_email,sender_phone,package_name,message,status,created_at) VALUES (:id,:owner,:name,:email,:phone,:package,:message,"new",UTC_TIMESTAMP())')->execute(['id'=>$inquiry,'owner'=>$id,'name'=>$name,'email'=>$email,'phone'=>$phone,'package'=>$package,'message'=>$message?:null]);
create_notification('influencer',$id,'new_inquiry','New partnership inquiry','A new '.$package.' inquiry is waiting in Creator Studio.',null,['inquiry_id'=>$inquiry]);
json_response(201,['success'=>true,'message'=>'Your inquiry has been sent to the creator.','inquiry_id'=>$inquiry]);
