<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
send_cors_headers();
$context=require_vendor(true);$vendorId=$context['vendor']['id'];$db=get_db();$method=$_SERVER['REQUEST_METHOD']??'GET';
if($method==='GET'){
    $stmt=$db->prepare('SELECT f.id,f.customer_id,f.request_count,f.window_days,f.threshold_count,f.status,f.vendor_note,f.vendor_noted_at,f.admin_note,f.admin_reviewed_at,f.last_return_id,f.first_flagged_at,f.updated_at FROM customer_return_review_flags f WHERE f.vendor_id=:vendor ORDER BY f.updated_at DESC LIMIT 100');
    $stmt->execute(['vendor'=>$vendorId]);json_response(200,['success'=>true,'flags'=>$stmt->fetchAll()]);
}
if($method==='POST'){
    $body=get_json_body();$id=trim((string)($body['flag_id']??''));$note=trim((string)($body['note']??''));if(mb_strlen($note)<20||mb_strlen($note)>2000)json_response(422,['success'=>false,'message'=>'Explain your observation in 20–2000 characters.']);
    $stmt=$db->prepare('UPDATE customer_return_review_flags SET vendor_note=:note,vendor_noted_at=UTC_TIMESTAMP(),updated_at=UTC_TIMESTAMP() WHERE id=:id AND vendor_id=:vendor');$stmt->execute(['note'=>$note,'id'=>$id,'vendor'=>$vendorId]);if(!$stmt->rowCount())json_response(404,['success'=>false,'message'=>'Review flag not found for your store.']);
    audit_event('vendor.return_pattern_noted','customer_return_review_flag',$id,['note'=>$note]);json_response(200,['success'=>true,'message'=>'Your note was recorded for admin review.']);
}
json_response(405,['success'=>false,'message'=>'Method not allowed.']);
