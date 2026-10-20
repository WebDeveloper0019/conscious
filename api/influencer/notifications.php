<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
send_cors_headers();$principal=require_principal(['influencer']);$id=$principal['id'];$db=get_db();$method=$_SERVER['REQUEST_METHOD']??'GET';
if($method==='GET'){$stmt=$db->prepare('SELECT id,type,title,message,read_at,created_at FROM notifications WHERE recipient_type="influencer" AND recipient_id=:owner AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 100');$stmt->execute(['owner'=>$id]);json_response(200,['success'=>true,'notifications'=>$stmt->fetchAll()]);}
if($method==='PATCH'){$body=get_json_body();$notification=trim((string)($body['id']??''));$stmt=$db->prepare('UPDATE notifications SET read_at=COALESCE(read_at,UTC_TIMESTAMP()) WHERE id=:id AND recipient_type="influencer" AND recipient_id=:owner AND deleted_at IS NULL');$stmt->execute(['id'=>$notification,'owner'=>$id]);json_response(200,['success'=>true,'message'=>'Notification marked as read.']);}
json_response(405,['success'=>false,'message'=>'Method not allowed.']);
