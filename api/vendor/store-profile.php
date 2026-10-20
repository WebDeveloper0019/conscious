<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
require_once __DIR__.'/../lib/india.php';
send_cors_headers();
$context=require_vendor(true);
$vendorId=$context['vendor']['id'];
$db=get_db();
$method=$_SERVER['REQUEST_METHOD']??'GET';
if($method==='GET'){
    $profile=$db->prepare('SELECT store_name,store_slug,tagline,description,support_email,support_mobile,video_url,is_public,logo_file_id,banner_file_id FROM vendor_store_profiles WHERE vendor_id=:vendor');
    $profile->execute(['vendor'=>$vendorId]);
    $address=$db->prepare('SELECT address_line1,address_line2,city,district,state,pincode FROM vendor_addresses WHERE vendor_id=:vendor AND address_type="dispatch" ORDER BY is_primary DESC LIMIT 1');
    $address->execute(['vendor'=>$vendorId]);
    $policies=$db->prepare('SELECT return_policy,cancellation_policy,damage_policy,handmade_policy,materials_sourcing,packaging_approach,maker_payment_model FROM vendor_settings WHERE vendor_id=:vendor');
    $policies->execute(['vendor'=>$vendorId]);
    json_response(200,['success'=>true,'store'=>$profile->fetch()?:null,'dispatch_address'=>$address->fetch()?:null,'policies'=>$policies->fetch()?:null]);
}
if($method==='PATCH'){
    $body=get_json_body();
    $section=trim((string)($body['section']??''));
    if($section==='store'){
        $name=trim((string)($body['store_name']??''));$tagline=trim((string)($body['tagline']??''));$description=trim((string)($body['description']??''));$email=strtolower(trim((string)($body['support_email']??'')));$mobile=preg_replace('/\D+/','',(string)($body['support_mobile']??''));$video=trim((string)($body['video_url']??''));
        if(mb_strlen($name)<2||mb_strlen($name)>160||mb_strlen($tagline)>180||mb_strlen($description)<100||mb_strlen($description)>3000||!filter_var($email,FILTER_VALIDATE_EMAIL)||!preg_match('/^[6-9][0-9]{9}$/',$mobile)||($video!==''&&!filter_var($video,FILTER_VALIDATE_URL)))json_response(422,['success'=>false,'message'=>'Check store name, description, support contact, and optional video URL.']);
        $public=!empty($body['is_public'])?1:0;
        $db->prepare('INSERT INTO vendor_store_profiles (vendor_id,store_name,tagline,description,support_email,support_mobile,video_url,is_public,created_at,updated_at) VALUES (:vendor,:name,:tagline,:description,:email,:mobile,:video,:public,UTC_TIMESTAMP(),UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE store_name=VALUES(store_name),tagline=VALUES(tagline),description=VALUES(description),support_email=VALUES(support_email),support_mobile=VALUES(support_mobile),video_url=VALUES(video_url),is_public=VALUES(is_public),updated_at=UTC_TIMESTAMP()')->execute(['vendor'=>$vendorId,'name'=>$name,'tagline'=>$tagline?:null,'description'=>$description,'email'=>$email,'mobile'=>$mobile,'video'=>$video?:null,'public'=>$public]);
        audit_event('vendor.store_profile_updated','vendor',$vendorId,['is_public'=>$public]);
        json_response(200,['success'=>true,'message'=>'Store profile saved.']);
    }
    if($section==='dispatch'){
        $line1=trim((string)($body['address_line1']??''));$line2=trim((string)($body['address_line2']??''));$city=trim((string)($body['city']??''));$district=trim((string)($body['district']??''));$state=canonical_india_state((string)($body['state']??''));$pin=trim((string)($body['pincode']??''));
        if(mb_strlen($line1)<10||mb_strlen($line1)>180||mb_strlen($line2)>180||mb_strlen($city)<2||mb_strlen($city)>80||mb_strlen($district)>80||!$state||!preg_match('/^[1-9][0-9]{5}$/',$pin))json_response(422,['success'=>false,'message'=>'Enter a complete Indian dispatch address and valid PIN code.']);
        $id=uuid_string();$db->beginTransaction();try{$db->prepare('DELETE FROM vendor_addresses WHERE vendor_id=:vendor AND address_type="dispatch"')->execute(['vendor'=>$vendorId]);$db->prepare('INSERT INTO vendor_addresses (id,vendor_id,address_type,address_line1,address_line2,city,district,state,pincode,is_primary,created_at) VALUES (:id,:vendor,"dispatch",:line1,:line2,:city,:district,:state,:pin,1,UTC_TIMESTAMP())')->execute(['id'=>$id,'vendor'=>$vendorId,'line1'=>$line1,'line2'=>$line2?:null,'city'=>$city,'district'=>$district?:null,'state'=>$state,'pin'=>$pin]);$db->commit();}catch(Throwable $e){if($db->inTransaction())$db->rollBack();throw $e;}
        audit_event('vendor.dispatch_address_updated','vendor',$vendorId,[]);json_response(200,['success'=>true,'message'=>'Dispatch address saved.']);
    }
    json_response(422,['success'=>false,'message'=>'Unknown store profile section.']);
}
json_response(405,['success'=>false,'message'=>'Method not allowed.']);
