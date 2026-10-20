<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';require_once __DIR__.'/../lib/notifications.php';
send_cors_headers();$context=require_vendor(true);$vendorId=$context['vendor']['id'];$db=get_db();$method=$_SERVER['REQUEST_METHOD']??'GET';
if($method==='GET'){
    $status=trim((string)($_GET['status']??''));
    $stmt=$db->prepare('SELECT r.*,oi.product_name,oi.image,o.order_number FROM return_requests r JOIN order_items oi ON oi.id=r.order_item_id JOIN orders o ON o.id=r.order_id WHERE r.vendor_id=:vendor AND (:status_filter="" OR r.status=:status_value) ORDER BY r.requested_at DESC');
    $stmt->execute(['vendor'=>$vendorId,'status_filter'=>$status,'status_value'=>$status]);
    $returns=$stmt->fetchAll();
    $evidenceStmt=$db->prepare('SELECT re.return_request_id,re.id,re.caption,re.created_at,f.id AS file_id,f.original_name,f.scan_status,f.mime_type FROM return_evidence re JOIN return_requests r ON r.id=re.return_request_id JOIN file_assets f ON f.id=re.file_asset_id WHERE r.vendor_id=:vendor AND f.deleted_at IS NULL ORDER BY re.sort_order,re.created_at');
    $evidenceStmt->execute(['vendor'=>$vendorId]);
    $evidence=[];
    foreach($evidenceStmt->fetchAll() as $row){
        $evidence[$row['return_request_id']][]=[
            'id'=>$row['id'],'caption'=>$row['caption'],'file_name'=>$row['original_name'],
            'scan_status'=>$row['scan_status'],'mime_type'=>$row['mime_type'],
            'url'=>$row['scan_status']==='clean'?'../../../api/files.php?id='.rawurlencode((string)$row['file_id']):null,
        ];
    }
    foreach($returns as &$item)$item['evidence']=$evidence[$item['id']]??[];
    unset($item);
    json_response(200,['success'=>true,'returns'=>$returns]);
}
if($method==='POST'){$body=get_json_body();$id=trim((string)($body['return_id']??''));$decision=trim((string)($body['decision']??''));$note=trim((string)($body['note']??''));if(!in_array($decision,['approve','reject','request_evidence','mark_received','recommend_refund','approve_replacement'],true)||mb_strlen($note)<10)json_response(422,['success'=>false,'message'=>'A valid decision and detailed note are required.']);$next=['approve'=>'approved','reject'=>'rejected','request_evidence'=>'evidence_requested','mark_received'=>'received','recommend_refund'=>'refund_recommended','approve_replacement'=>'replacement_approved'][$decision];$map=['requested'=>['approved','rejected','evidence_requested'],'evidence_requested'=>['approved','rejected'],'approved'=>['received'],'received'=>['refund_recommended','replacement_approved']];$db->beginTransaction();try{$stmt=$db->prepare('SELECT * FROM return_requests WHERE id=:id AND vendor_id=:vendor FOR UPDATE');$stmt->execute(['id'=>$id,'vendor'=>$vendorId]);$request=$stmt->fetch();if(!$request)json_response(404,['success'=>false,'message'=>'Return request not found.']);assert_transition($request['status'],$next,$map);$db->prepare('UPDATE return_requests SET status=:status,vendor_note=:note,reviewed_at=UTC_TIMESTAMP(),updated_at=UTC_TIMESTAMP() WHERE id=:id')->execute(['status'=>$next,'note'=>$note,'id'=>$id]);if($next==='refund_recommended'){$item=$db->prepare('SELECT line_total,quantity FROM order_items WHERE id=:id');$item->execute(['id'=>$request['order_item_id']]);$line=$item->fetch();$amount=round((float)$line['line_total']*((int)$request['quantity']/(int)$line['quantity']),2);$db->prepare('INSERT INTO refunds (id,return_request_id,order_id,amount,status,reason,recommended_by_type,recommended_by_id,created_at,updated_at) VALUES (:id,:return_id,:order,:amount,"recommended",:reason,"vendor",:vendor,UTC_TIMESTAMP(),UTC_TIMESTAMP())')->execute(['id'=>uuid_string(),'return_id'=>$id,'order'=>$request['order_id'],'amount'=>$amount,'reason'=>$note,'vendor'=>$vendorId]);}record_status_change('return_request',$id,$request['status'],$next,$note);create_notification('customer',$request['user_id'],'return_update','Return request updated','Your return request is now '.str_replace('_',' ',$next).'.','../returns.php',['return_id'=>$id]);audit_event('vendor.return_'.$decision,'return_request',$id,['note'=>$note]);$db->commit();json_response(200,['success'=>true,'message'=>'Return request updated.','status'=>$next]);}catch(Throwable $e){if($db->inTransaction())$db->rollBack();throw $e;}}
json_response(405,['success'=>false,'message'=>'Method not allowed.']);
