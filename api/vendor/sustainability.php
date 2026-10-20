<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
require_once __DIR__.'/../lib/upload.php';
send_cors_headers();
$context=require_vendor(true);$vendorId=$context['vendor']['id'];$db=get_db();$method=$_SERVER['REQUEST_METHOD']??'GET';
if($method==='GET'){
    $claims=$db->prepare('SELECT c.*,f.scan_status,f.id AS file_id FROM sustainability_claims c JOIN file_assets f ON f.id=c.evidence_file_id WHERE c.vendor_id=:vendor ORDER BY c.created_at DESC');$claims->execute(['vendor'=>$vendorId]);
    $certs=$db->prepare('SELECT c.*,f.scan_status,f.id AS file_id FROM vendor_certificates c JOIN file_assets f ON f.id=c.file_asset_id WHERE c.vendor_id=:vendor ORDER BY c.created_at DESC');$certs->execute(['vendor'=>$vendorId]);
    json_response(200,['success'=>true,'claims'=>$claims->fetchAll(),'certificates'=>$certs->fetchAll()]);
}
if($method==='POST'){
    $type=trim((string)($_POST['record_type']??''));
    if(!in_array($type,['claim','certificate'],true))json_response(422,['success'=>false,'message'=>'Choose a valid evidence record type.']);
    if(!isset($_FILES['evidence']))json_response(422,['success'=>false,'message'=>'Evidence file is required.']);
    if($type==='certificate'){
        $certificate=trim((string)($_POST['certificate_type']??''));$issuer=trim((string)($_POST['issuer']??''));$reference=trim((string)($_POST['reference_number']??''));$from=trim((string)($_POST['valid_from']??''));$until=trim((string)($_POST['valid_until']??''));
        if(mb_strlen($certificate)<2||mb_strlen($certificate)>120||mb_strlen($issuer)<2||mb_strlen($issuer)>180||mb_strlen($reference)>120||($from!==''&&!preg_match('/^\d{4}-\d{2}-\d{2}$/',$from))||($until!==''&&!preg_match('/^\d{4}-\d{2}-\d{2}$/',$until))||($from!==''&&$until!==''&&$until<$from))json_response(422,['success'=>false,'message'=>'Certificate details or validity dates are invalid.']);
        $asset=store_private_upload($_FILES['evidence'],'vendor',$vendorId,'vendor_certificate',['image/jpeg','image/png','application/pdf'],10*1024*1024);
        $id=uuid_string();$db->prepare('INSERT INTO vendor_certificates (id,vendor_id,certificate_type,issuer,reference_number,valid_from,valid_until,file_asset_id,verification_status,created_at,updated_at) VALUES (:id,:vendor,:type,:issuer,:reference,:valid_from,:valid_until,:file,"pending",UTC_TIMESTAMP(),UTC_TIMESTAMP())')->execute(['id'=>$id,'vendor'=>$vendorId,'type'=>$certificate,'issuer'=>$issuer,'reference'=>$reference?:null,'valid_from'=>$from?:null,'valid_until'=>$until?:null,'file'=>$asset['id']]);
        json_response(201,['success'=>true,'message'=>'Certificate uploaded for review.','certificate_id'=>$id]);
    }
    $statement=trim((string)($_POST['claim_statement']??''));$scope=trim((string)($_POST['scope']??''));$issuer=trim((string)($_POST['issuer']??''));$limitations=trim((string)($_POST['limitations']??''));$validDate=trim((string)($_POST['valid_until']??''));$evidenceType=trim((string)($_POST['evidence_type']??'Supporting document'));$claimType=trim((string)($_POST['claim_type']??'Other measurable claim'));$reference=trim((string)($_POST['reference_number']??''));$productId=trim((string)($_POST['product_id']??''));
    $valid=strtotime($validDate);
    if(mb_strlen($statement)<20||mb_strlen($statement)>2000||mb_strlen($scope)<2||mb_strlen($scope)>180||mb_strlen($issuer)<2||mb_strlen($issuer)>180||mb_strlen($limitations)<10||mb_strlen($limitations)>1000||mb_strlen($evidenceType)>120||mb_strlen($claimType)>120||mb_strlen($reference)>120||!preg_match('/^\d{4}-\d{2}-\d{2}$/',$validDate)||$valid===false||$valid<time())json_response(422,['success'=>false,'message'=>'Claim statement, scope, issuer, limitations and future review date are required.']);
    if($productId!==''){$owned=$db->prepare('SELECT id FROM products WHERE id=:product AND vendor_id=:vendor');$owned->execute(['product'=>$productId,'vendor'=>$vendorId]);if(!$owned->fetchColumn())json_response(404,['success'=>false,'message'=>'Product not found in your store.']);}
    $asset=store_private_upload($_FILES['evidence'],'vendor',$vendorId,'sustainability_claim',['image/jpeg','image/png','application/pdf'],10*1024*1024);
    $id=uuid_string();$db->prepare('INSERT INTO sustainability_claims (id,vendor_id,product_id,claim_type,scope,claim_statement,evidence_type,issuer,reference_number,valid_until,limitations,evidence_file_id,review_status,public_status,created_at,updated_at) VALUES (:id,:vendor,:product,:type,:scope,:statement,:evidence_type,:issuer,:reference,:valid_until,:limitations,:file,"pending_review","hidden",UTC_TIMESTAMP(),UTC_TIMESTAMP())')->execute(['id'=>$id,'vendor'=>$vendorId,'product'=>$productId?:null,'type'=>$claimType,'scope'=>$scope,'statement'=>$statement,'evidence_type'=>$evidenceType,'issuer'=>$issuer,'reference'=>$reference?:null,'valid_until'=>$validDate,'limitations'=>$limitations,'file'=>$asset['id']]);
    json_response(201,['success'=>true,'message'=>'Evidence-backed claim submitted for review.','claim_id'=>$id]);
}
json_response(405,['success'=>false,'message'=>'Method not allowed.']);
