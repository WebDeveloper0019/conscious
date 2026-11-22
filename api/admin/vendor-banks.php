<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
require_once __DIR__.'/../lib/admin.php';
send_cors_headers();
$context=require_admin('vendor_applications.review');
$db=get_db();$method=$_SERVER['REQUEST_METHOD']??'GET';
if($method==='GET'){
    $stmt=$db->query('SELECT b.id,b.vendor_id,b.beneficiary_name,b.account_last4,b.verification_status,b.updated_at,v.display_name,v.email FROM vendor_bank_accounts b JOIN vendors v ON v.id=b.vendor_id WHERE b.is_primary=1 ORDER BY b.updated_at DESC LIMIT 100');
    $accounts=$stmt->fetchAll();$proof=$db->prepare('SELECT d.id,d.application_id,d.verification_status,f.id AS file_id,f.scan_status FROM vendor_documents d JOIN file_assets f ON f.id=d.file_asset_id WHERE d.vendor_id=:vendor AND d.document_type="bank_proof" AND d.created_at>=:updated AND f.deleted_at IS NULL ORDER BY d.created_at DESC LIMIT 1');
    foreach($accounts as &$account){$proof->execute(['vendor'=>$account['vendor_id'],'updated'=>$account['updated_at']]);$account['proof']=$proof->fetch()?:null;}unset($account);
    json_response(200,['success'=>true,'accounts'=>$accounts]);
}
if($method!=='POST')json_response(405,['success'=>false,'message'=>'Method not allowed.']);
$body=get_json_body();$id=trim((string)($body['bank_account_id']??''));$decision=(string)($body['decision']??'');$reason=trim((string)($body['reason']??''));
if(!in_array($decision,['verified','rejected'],true))json_response(422,['success'=>false,'message'=>'Choose verify or reject.']);
if($decision==='rejected'&&mb_strlen($reason)<10)json_response(422,['success'=>false,'message'=>'Explain why the bank proof was rejected.']);
$db->beginTransaction();
try{
    $stmt=$db->prepare('SELECT id,vendor_id,updated_at,verification_status FROM vendor_bank_accounts WHERE id=:id FOR UPDATE');$stmt->execute(['id'=>$id]);$account=$stmt->fetch();if(!$account)json_response(404,['success'=>false,'message'=>'Bank account not found.']);
    if($decision==='verified'){$proof=$db->prepare('SELECT d.verification_status,f.scan_status FROM vendor_documents d JOIN file_assets f ON f.id=d.file_asset_id WHERE d.vendor_id=:vendor AND d.document_type="bank_proof" AND d.created_at>=:updated AND f.deleted_at IS NULL ORDER BY d.created_at DESC LIMIT 1');$proof->execute(['vendor'=>$account['vendor_id'],'updated'=>$account['updated_at']]);$document=$proof->fetch();if(!$document||$document['verification_status']!=='verified'||$document['scan_status']!=='clean')json_response(409,['success'=>false,'message'=>'First verify a clean bank proof uploaded after the latest account change.']);}
    $db->prepare('UPDATE vendor_bank_accounts SET verification_status=:status,updated_at=UTC_TIMESTAMP() WHERE id=:id')->execute(['status'=>$decision,'id'=>$id]);
    audit_event('admin.vendor_bank_'.$decision,'vendor_bank_account',$id,['reason'=>$reason?:null]);
    $db->commit();json_response(200,['success'=>true,'message'=>'Bank review saved.']);
}catch(Throwable $error){if($db->inTransaction())$db->rollBack();throw $error;}
