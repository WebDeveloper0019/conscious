<?php
declare(strict_types=1);
require_once __DIR__.'/lib/helpers.php';
require_once __DIR__.'/lib/object-storage.php';
require_once __DIR__.'/lib/malware-scan.php';

$id=trim((string)($_GET['id']??''));
$stmt=get_db()->prepare('SELECT * FROM file_assets WHERE id=:id AND deleted_at IS NULL LIMIT 1');
$stmt->execute(['id'=>$id]);
$asset=$stmt->fetch();
if(!$asset){http_response_code(404);exit;}
if(!malware_scan_status_is_reviewable((string)$asset['scan_status'],get_db())){
    http_response_code(423);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode(['success'=>false,'message'=>'This file is locked until the security scan reports clean or the local testing bypass is enabled.']);
    exit;
}

$reviewable=malware_scan_status_is_reviewable((string)$asset['scan_status'],get_db());
$allowed=$asset['visibility']==='public'&&$reviewable;
if(!$allowed){
    $principal=current_auth_session(false);
    $allowed=$principal&&(($principal['principal_type']===$asset['owner_type']&&$principal['principal_id']===$asset['owner_id'])||$principal['principal_type']==='admin');
    if(!$allowed&&$principal&&$principal['principal_type']==='vendor'&&$reviewable){
        $returnAccess=get_db()->prepare('SELECT 1 FROM return_evidence re JOIN return_requests r ON r.id=re.return_request_id WHERE re.file_asset_id=:file_id AND r.vendor_id=:vendor_id LIMIT 1');
        $returnAccess->execute(['file_id'=>$id,'vendor_id'=>$principal['principal_id']]);
        $allowed=(bool)$returnAccess->fetchColumn();
    }
}
if(!$allowed){http_response_code(403);exit;}

try{$contents=file_asset_read($asset);}catch(Throwable $error){error_log('Private asset read failed: '.$error->getMessage());http_response_code(502);exit;}
if($contents===null){http_response_code(404);exit;}
$inline=$asset['visibility']==='public'||($reviewable&&in_array($asset['mime_type'],['image/jpeg','image/png','image/webp'],true));
header('Content-Type: '.$asset['mime_type']);
header('Content-Length: '.strlen($contents));
header('X-Content-Type-Options: nosniff');
header('Content-Disposition: '.($inline?'inline':'attachment').'; filename="'.preg_replace('/[^A-Za-z0-9._-]/','_',basename($asset['original_name'])).'"');
header('Cache-Control: '.($asset['visibility']==='public'?'public, max-age=86400':'private, no-store'));
echo $contents;
