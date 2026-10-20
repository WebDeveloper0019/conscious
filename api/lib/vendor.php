<?php
declare(strict_types=1);
require_once __DIR__.'/authorization.php';

function public_vendor(array $vendor): array {return ['id'=>$vendor['id'],'vendor_type'=>$vendor['vendor_type'],'owner_name'=>$vendor['owner_name'],'display_name'=>$vendor['display_name'],'email'=>$vendor['email'],'mobile'=>$vendor['mobile'],'status'=>$vendor['status'],'verification_status'=>$vendor['verification_status'],'onboarding_status'=>$vendor['onboarding_status'],'created_at'=>$vendor['created_at']];}
function require_vendor(bool $sellingRequired=false): array {
    $principal=require_principal(['vendor']);$stmt=get_db()->prepare('SELECT * FROM vendors WHERE id=:id LIMIT 1');$stmt->execute(['id'=>$principal['id']]);$vendor=$stmt->fetch();
    if(!$vendor)json_response(401,['success'=>false,'message'=>'Vendor account no longer exists.']);
    if(in_array($vendor['status'],['suspended','blocked','inactive'],true))json_response(403,['success'=>false,'message'=>'This vendor account is not currently permitted to operate.','code'=>'VENDOR_ACCOUNT_RESTRICTED']);
    $localDemo=APP_ENV==='local'&&$vendor['verification_status']==='demo_fixture';
    if($sellingRequired&&($vendor['status']!=='active'||(!($vendor['verification_status']==='approved'||$localDemo))||$vendor['onboarding_status']!=='completed'))json_response(403,['success'=>false,'message'=>'Complete and receive approval for vendor onboarding before using selling tools.','code'=>'VENDOR_SELLING_LOCKED']);
    return ['principal'=>$principal,'vendor'=>$vendor];
}
function current_vendor_application(string $vendorId,bool $forUpdate=false): array {
    $sql='SELECT * FROM vendor_applications WHERE vendor_id=:vendor ORDER BY version DESC LIMIT 1'.($forUpdate?' FOR UPDATE':'');$stmt=get_db()->prepare($sql);$stmt->execute(['vendor'=>$vendorId]);$application=$stmt->fetch();if(!$application)throw new RuntimeException('Vendor application is missing.');return $application;
}
function vendor_next_route(array $vendor): string {
    if(($vendor['verification_status']==='approved'||(APP_ENV==='local'&&$vendor['verification_status']==='demo_fixture'))&&$vendor['onboarding_status']==='completed'&&$vendor['status']==='active')return '../dashboard.php';
    if(in_array($vendor['verification_status'],['submitted','under_review','changes_requested','rejected'],true))return 'status.php';return '../onboarding.php?type='.rawurlencode($vendor['vendor_type']);
}
