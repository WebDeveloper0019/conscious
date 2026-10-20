<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
send_cors_headers();$principal=require_principal(['influencer']);$id=$principal['id'];
if(($_SERVER['REQUEST_METHOD']??'')!=='PATCH')json_response(405,['success'=>false,'message'=>'Method not allowed.']);
$body=get_json_body();$current=(string)($body['current_password']??'');$new=(string)($body['new_password']??'');
if(!is_strong_enough_password($new))json_response(422,['success'=>false,'message'=>'Use 12+ password characters with upper, lower, number and symbol.']);
$db=get_db();$stmt=$db->prepare('SELECT password_hash FROM influencers WHERE id=:id LIMIT 1');$stmt->execute(['id'=>$id]);$hash=$stmt->fetchColumn();
if(!$hash||!password_verify($current,(string)$hash))json_response(401,['success'=>false,'message'=>'Current password is incorrect.']);
$db->prepare('UPDATE influencers SET password_hash=:hash,updated_at=UTC_TIMESTAMP() WHERE id=:id')->execute(['hash'=>password_hash($new,PASSWORD_DEFAULT),'id'=>$id]);
revoke_principal_sessions('influencer',$id,'password_change',$principal['session_id']);
audit_event('influencer.password_changed','influencer',$id,[],['type'=>'influencer','id'=>$id]);
json_response(200,['success'=>true,'message'=>'Password changed. Other sessions have been signed out.']);
