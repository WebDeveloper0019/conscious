<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/idempotency.php';

send_cors_headers();
$principal = require_principal(['customer']);
$db = get_db();
$userId = $principal['id'];
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $stmt=$db->prepare(
        "SELECT v.id,v.display_name,v.owner_name,v.vendor_type,v.status,v.verification_status,
                sv.created_at AS saved_at
         FROM saved_vendors sv
         JOIN vendors v ON v.id=sv.vendor_id
         WHERE sv.user_id=:user
         ORDER BY sv.created_at DESC"
    );
    $stmt->execute(['user'=>$userId]);
    $vendors=[];
    foreach($stmt->fetchAll() as $v){
        $vendors[]=[
            'id'=>$v['id'],'displayName'=>$v['display_name'],'ownerName'=>$v['owner_name'],
            'vendorType'=>$v['vendor_type'],'status'=>$v['status'],
            'verificationStatus'=>$v['verification_status'],'savedAt'=>$v['saved_at']
        ];
    }
    json_response(200,['success'=>true,'savedVendors'=>$vendors]);
}

$body=get_json_body();
$vendorId=trim((string)($body['vendor_id'] ?? $_GET['vendor_id'] ?? ''));
if($vendorId==='' || !preg_match('/^[A-Za-z0-9_-]{10,100}$/',$vendorId)){
    json_response(422,['success'=>false,'message'=>'A valid vendor_id is required.']);
}
$payload=['vendor_id'=>$vendorId];

if($method==='POST'){
    $idem=begin_idempotent_request('customer.saved_vendor.add',$principal,$payload);
    try{
        $vendor=$db->prepare("SELECT id,status,verification_status FROM vendors WHERE id=:id LIMIT 1");
        $vendor->execute(['id'=>$vendorId]);$vendor=$vendor->fetch();
        if(!$vendor) {
            $response=['success'=>false,'message'=>'Vendor not found.'];
            finish_idempotent_request((int)$idem['id'],404,$response);
            json_response(404,$response);
        }
        if($vendor['status']!=='active' || $vendor['verification_status']!=='approved'){
            $response=['success'=>false,'message'=>'This vendor is not currently available.'];
            finish_idempotent_request((int)$idem['id'],422,$response);
            json_response(422,$response);
        }
        $db->prepare(
            "INSERT INTO saved_vendors(user_id,vendor_id,created_at)
             VALUES(:user,:vendor,UTC_TIMESTAMP())
             ON DUPLICATE KEY UPDATE created_at=created_at"
        )->execute(['user'=>$userId,'vendor'=>$vendorId]);
        $response=['success'=>true,'message'=>'Vendor saved.','vendorId'=>$vendorId];
        finish_idempotent_request((int)$idem['id'],200,$response);
        json_response(200,$response);
    }catch(Throwable $e){
        $response=['success'=>false,'message'=>'Unable to save vendor.'];
        finish_idempotent_request((int)$idem['id'],500,$response);
        json_response(500,$response);
    }
}

if($method==='DELETE'){
    $idem=begin_idempotent_request('customer.saved_vendor.remove',$principal,$payload);
    try{
        $stmt=$db->prepare("DELETE FROM saved_vendors WHERE user_id=:user AND vendor_id=:vendor");
        $stmt->execute(['user'=>$userId,'vendor'=>$vendorId]);
        $response=['success'=>true,'message'=>'Vendor removed from saved vendors.','vendorId'=>$vendorId,'removed'=>$stmt->rowCount()>0];
        finish_idempotent_request((int)$idem['id'],200,$response);
        json_response(200,$response);
    }catch(Throwable $e){
        $response=['success'=>false,'message'=>'Unable to remove vendor.'];
        finish_idempotent_request((int)$idem['id'],500,$response);
        json_response(500,$response);
    }
}

json_response(405,['success'=>false,'message'=>'Method not allowed.']);
