<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
require_once __DIR__.'/../lib/admin.php';
send_cors_headers();
$context=require_admin('customers.manage');$db=get_db();$method=$_SERVER['REQUEST_METHOD']??'GET';
if($method==='GET'){
    $stmt=$db->query('SELECT f.id,f.customer_id,f.scope_key,f.vendor_id,v.display_name AS vendor_name,f.request_count,f.window_days,f.threshold_count,f.status,f.vendor_note,f.vendor_noted_at,f.admin_note,f.admin_reviewed_at,f.last_return_id,f.first_flagged_at,f.updated_at FROM customer_return_review_flags f LEFT JOIN vendors v ON v.id=f.vendor_id ORDER BY FIELD(f.status,"open","monitoring","cleared"),f.updated_at DESC LIMIT 200');
    json_response(200,['success'=>true,'flags'=>$stmt->fetchAll()]);
}
if($method==='PATCH'){
    $body=get_json_body();$id=trim((string)($body['flag_id']??''));$status=trim((string)($body['status']??''));$note=trim((string)($body['note']??''));
    if(!in_array($status,['open','monitoring','cleared'],true)||mb_strlen($note)<20||mb_strlen($note)>2000)json_response(422,['success'=>false,'message'=>'Choose a review status and explain the decision in 20–2000 characters.']);
    $check=$db->prepare('SELECT id,customer_id,status FROM customer_return_review_flags WHERE id=:id');$check->execute(['id'=>$id]);$flag=$check->fetch();if(!$flag)json_response(404,['success'=>false,'message'=>'Review flag not found.']);
    $db->prepare('UPDATE customer_return_review_flags SET status=:status,admin_note=:note,admin_reviewed_by=:admin,admin_reviewed_at=UTC_TIMESTAMP(),updated_at=UTC_TIMESTAMP() WHERE id=:id')->execute(['status'=>$status,'note'=>$note,'admin'=>$context['admin']['id'],'id'=>$id]);
    record_status_change('customer_return_review_flag',$id,$flag['status'],$status,$note);
    audit_event('admin.customer_return_pattern_reviewed','customer_return_review_flag',$id,['status'=>$status,'note'=>$note]);
    json_response(200,['success'=>true,'message'=>'Return pattern review saved. Customer account status was not changed.']);
}
json_response(405,['success'=>false,'message'=>'Method not allowed.']);
