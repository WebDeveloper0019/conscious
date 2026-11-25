<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
require_once __DIR__.'/../lib/admin.php';
require_once __DIR__.'/../lib/notifications.php';
require_once __DIR__.'/../lib/resend.php';
require_once __DIR__.'/../lib/malware-scan.php';
require_once __DIR__.'/../lib/crypto.php';
send_cors_headers();
$context=require_admin('vendor_applications.review');
$db=get_db();$method=$_SERVER['REQUEST_METHOD']??'GET';
if($method==='GET'){
    $requestedId=trim((string)($_GET['id']??''));
    $sql='SELECT b.id,b.vendor_id,b.beneficiary_name,b.account_last4,b.bank_name,b.verification_status,b.created_at,b.updated_at,v.display_name,v.owner_name,v.email,v.mobile,v.vendor_type,v.status AS vendor_status FROM vendor_bank_accounts b JOIN vendors v ON v.id=b.vendor_id WHERE b.is_primary=1';
    if($requestedId!==''){$stmt=$db->prepare($sql.' AND b.id=:id LIMIT 1');$stmt->execute(['id'=>$requestedId]);$account=$stmt->fetch();if(!$account)json_response(404,['success'=>false,'message'=>'Payout account not found.']);$proof=$db->prepare('SELECT d.id,d.application_id,d.verification_status,d.rejection_reason,d.created_at,d.verified_at,f.id AS file_id,f.scan_status,f.original_name,f.mime_type,f.size_bytes FROM vendor_documents d JOIN file_assets f ON f.id=d.file_asset_id WHERE d.vendor_id=:vendor AND d.document_type="bank_proof" AND d.created_at>=:updated AND f.deleted_at IS NULL ORDER BY d.created_at DESC LIMIT 1');$proof->execute(['vendor'=>$account['vendor_id'],'updated'=>$account['updated_at']]);$account['proof']=$proof->fetch()?:null;if($account['proof'])$account['proof']['scan_reviewable']=malware_scan_status_is_reviewable((string)$account['proof']['scan_status'],$db);$history=$db->prepare('SELECT from_status,to_status,reason,actor_type,created_at FROM status_history WHERE entity_type="vendor_bank_account" AND entity_id=:id ORDER BY created_at DESC,id DESC LIMIT 30');$history->execute(['id'=>$requestedId]);json_response(200,['success'=>true,'account'=>$account,'history'=>$history->fetchAll()]);}
    $stmt=$db->query($sql.' ORDER BY b.updated_at DESC LIMIT 100');
    $accounts=$stmt->fetchAll();$proof=$db->prepare('SELECT d.id,d.application_id,d.verification_status,f.id AS file_id,f.scan_status FROM vendor_documents d JOIN file_assets f ON f.id=d.file_asset_id WHERE d.vendor_id=:vendor AND d.document_type="bank_proof" AND d.created_at>=:updated AND f.deleted_at IS NULL ORDER BY d.created_at DESC LIMIT 1');
    foreach($accounts as &$account){$proof->execute(['vendor'=>$account['vendor_id'],'updated'=>$account['updated_at']]);$account['proof']=$proof->fetch()?:null;if($account['proof'])$account['proof']['scan_reviewable']=malware_scan_status_is_reviewable((string)$account['proof']['scan_status'],$db);}unset($account);
    json_response(200,['success'=>true,'accounts'=>$accounts]);
}
if($method!=='POST')json_response(405,['success'=>false,'message'=>'Method not allowed.']);
$body=get_json_body();$id=trim((string)($body['bank_account_id']??''));$action=trim((string)($body['action']??'decision'));
if($action==='reveal'){
    require_admin('payouts.review');$purpose=trim((string)($body['purpose']??''));
    if(mb_strlen($purpose)<10||mb_strlen($purpose)>300)json_response(422,['success'=>false,'message'=>'Provide a payment-related access purpose between 10 and 300 characters.']);
    $sensitive=$db->prepare('SELECT account_number_encrypted,ifsc_encrypted,beneficiary_name,account_last4 FROM vendor_bank_accounts WHERE id=:id AND is_primary=1 LIMIT 1');$sensitive->execute(['id'=>$id]);$bank=$sensitive->fetch();if(!$bank)json_response(404,['success'=>false,'message'=>'Bank account not found.']);
    audit_event('admin.vendor_bank_details_revealed','vendor_bank_account',$id,['purpose'=>$purpose,'fields'=>['account_number','ifsc']]);
    header('Cache-Control: no-store, no-cache, must-revalidate, private');json_response(200,['success'=>true,'beneficiary_name'=>$bank['beneficiary_name'],'account_number'=>decrypt_sensitive((string)$bank['account_number_encrypted']),'ifsc'=>decrypt_sensitive((string)$bank['ifsc_encrypted']),'message'=>'Sensitive bank details revealed for this audited payment purpose.']);
}
$decision=(string)($body['decision']??'');$reason=trim((string)($body['reason']??''));
if(!in_array($decision,['verified','changes_requested','rejected'],true))json_response(422,['success'=>false,'message'=>'Choose approve, request correction or reject.']);
if($decision!=='verified'&&mb_strlen($reason)<10)json_response(422,['success'=>false,'message'=>'Provide a clear vendor-visible reason of at least 10 characters.']);
if(mb_strlen($reason)>500)json_response(422,['success'=>false,'message'=>'Keep the vendor-visible reason within 500 characters.']);
$db->beginTransaction();
try{
    $stmt=$db->prepare('SELECT b.id,b.vendor_id,b.updated_at,b.verification_status,v.email,v.owner_name,v.display_name FROM vendor_bank_accounts b JOIN vendors v ON v.id=b.vendor_id WHERE b.id=:id FOR UPDATE');$stmt->execute(['id'=>$id]);$account=$stmt->fetch();if(!$account)json_response(404,['success'=>false,'message'=>'Bank account not found.']);
    if($decision==='verified'){$proof=$db->prepare('SELECT d.verification_status,f.scan_status FROM vendor_documents d JOIN file_assets f ON f.id=d.file_asset_id WHERE d.vendor_id=:vendor AND d.document_type="bank_proof" AND d.created_at>=:updated AND f.deleted_at IS NULL ORDER BY d.created_at DESC LIMIT 1');$proof->execute(['vendor'=>$account['vendor_id'],'updated'=>$account['updated_at']]);$document=$proof->fetch();if(!$document||$document['verification_status']!=='verified'||!malware_scan_status_is_reviewable((string)$document['scan_status'],$db))json_response(409,['success'=>false,'message'=>'First verify a bank proof that passed security scanning, or use the authorized local testing bypass.']);}
    $previous=(string)$account['verification_status'];$db->prepare('UPDATE vendor_bank_accounts SET verification_status=:status WHERE id=:id')->execute(['status'=>$decision,'id'=>$id]);
    record_status_change('vendor_bank_account',$id,$previous,$decision,$reason?:null);
    $title=$decision==='verified'?'Payout bank approved':($decision==='changes_requested'?'Payout bank correction required':'Payout bank rejected');$vendorMessage=$reason?:'Your payout bank account has been approved for future payout requests.';create_notification('vendor',$account['vendor_id'],'payout_bank_review',$title,$vendorMessage,'../payout-bank.php',['bank_account_id'=>$id,'status'=>$decision]);
    audit_event('admin.vendor_bank_'.$decision,'vendor_bank_account',$id,['reason'=>$reason?:null]);
    $db->commit();$mailBody='<p>Hello '.htmlspecialchars((string)($account['owner_name']?:$account['display_name']),ENT_QUOTES,'UTF-8').',</p><p>'.htmlspecialchars($vendorMessage,ENT_QUOTES,'UTF-8').'</p><p>Open your seller workspace to review or replace the payout account.</p>';[$sent,$mailError]=resend_send_email((string)$account['email'],$title.' — Conscious India',$mailBody);if(!$sent)error_log('Payout bank review email failed: '.$mailError);json_response(200,['success'=>true,'message'=>'Bank review saved and the vendor was notified.','status'=>$decision]);
}catch(Throwable $error){if($db->inTransaction())$db->rollBack();throw $error;}
