<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
send_cors_headers();
$principal=require_principal(['influencer']);
$id=$principal['id'];$db=get_db();$method=$_SERVER['REQUEST_METHOD']??'GET';
if($method==='GET'){
    $stmt=$db->prepare('SELECT id,sender_name,sender_email,sender_phone,package_name,message,status,created_at,read_at FROM influencer_inquiries WHERE influencer_id=:owner ORDER BY created_at DESC LIMIT 100');
    $stmt->execute(['owner'=>$id]);
    json_response(200,['success'=>true,'inquiries'=>$stmt->fetchAll()]);
}
if($method==='PATCH'){
    $body=get_json_body();$inquiry=trim((string)($body['id']??''));$status=trim((string)($body['status']??''));
    if(!in_array($status,['read','closed'],true))json_response(422,['success'=>false,'message'=>'Invalid inquiry status.']);
    $stmt=$db->prepare('UPDATE influencer_inquiries SET status=:status,read_at=COALESCE(read_at,UTC_TIMESTAMP()),closed_at=IF(:status2="closed",UTC_TIMESTAMP(),closed_at) WHERE id=:id AND influencer_id=:owner AND status<>"closed"');
    $stmt->execute(['status'=>$status,'status2'=>$status,'id'=>$inquiry,'owner'=>$id]);
    if($stmt->rowCount()===0)json_response(404,['success'=>false,'message'=>'Inquiry was not found or is already closed.']);
    json_response(200,['success'=>true,'message'=>'Inquiry updated.']);
}
json_response(405,['success'=>false,'message'=>'Method not allowed.']);
