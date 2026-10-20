<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
require_once __DIR__.'/../lib/admin.php';
send_cors_headers();
$context=require_admin('influencers.manage');$db=get_db();$method=$_SERVER['REQUEST_METHOD']??'GET';
if($method==='GET'){
    $creator=trim((string)($_GET['influencer_id']??''));
    if($creator==='')json_response(422,['success'=>false,'message'=>'Creator id is required.']);
    $stmt=$db->prepare('SELECT d.id,d.document_type,d.verification_status,d.rejection_reason,d.created_at,f.id AS file_id,f.scan_status,f.mime_type,f.original_name FROM influencer_documents d JOIN file_assets f ON f.id=d.file_asset_id WHERE d.influencer_id=:owner AND f.deleted_at IS NULL ORDER BY d.created_at DESC');
    $stmt->execute(['owner'=>$creator]);
    json_response(200,['success'=>true,'documents'=>$stmt->fetchAll()]);
}
if($method==='POST'){
    $body=get_json_body();$id=trim((string)($body['document_id']??''));$decision=trim((string)($body['decision']??''));$reason=trim((string)($body['reason']??''));
    if(!in_array($decision,['approve','reject'],true)||($decision==='reject'&&mb_strlen($reason)<10))json_response(422,['success'=>false,'message'=>'Choose a valid decision and provide a detailed rejection reason.']);
    $stmt=$db->prepare('SELECT d.*,f.scan_status FROM influencer_documents d JOIN file_assets f ON f.id=d.file_asset_id WHERE d.id=:id AND f.deleted_at IS NULL LIMIT 1');$stmt->execute(['id'=>$id]);$document=$stmt->fetch();
    if(!$document)json_response(404,['success'=>false,'message'=>'Document not found.']);
    if($decision==='approve'&&$document['scan_status']!=='clean')json_response(409,['success'=>false,'message'=>'The document must pass a security scan before approval.']);
    $status=$decision==='approve'?'approved':'rejected';
    $db->prepare('UPDATE influencer_documents SET verification_status=:status,rejection_reason=:reason,reviewed_by=:admin,reviewed_at=UTC_TIMESTAMP(),updated_at=UTC_TIMESTAMP() WHERE id=:id')->execute(['status'=>$status,'reason'=>$decision==='reject'?$reason:null,'admin'=>$context['admin']['id'],'id'=>$id]);
    audit_event('admin.influencer_document_'.$decision,'influencer_document',$id,['creator_id'=>$document['influencer_id'],'reason'=>$reason]);
    json_response(200,['success'=>true,'message'=>'Document review saved.','status'=>$status]);
}
json_response(405,['success'=>false,'message'=>'Method not allowed.']);
