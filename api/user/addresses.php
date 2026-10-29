<?php
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/india.php';
require_once __DIR__ . '/../lib/authorization.php'; // <-- ADD THIS LINE

send_cors_headers();

$principal = require_principal(['customer']);
$userId = $principal['id'];
$db = get_db();
// ... your require_once lines ...

// Add this missing function to validate the mobile number
if (!function_exists('is_valid_indian_mobile')) {
    function is_valid_indian_mobile(string $phone): bool {
        // Remove any spaces, dashes, or +91
        $cleanPhone = preg_replace('/\D+/', '', $phone);
        
        // Check if it's exactly 10 digits and starts with 6, 7, 8, or 9
        return preg_match('/^[6-9][0-9]{9}$/', $cleanPhone) === 1;
    }
}

send_cors_headers();
$principal = require_principal(['customer']);
$userId = $principal['id'];
$db = get_db();

// ... the rest of your file ...

function address_public(array $a): array {
    return [
        'id'=>$a['id'],'label'=>$a['label'],'fullName'=>$a['full_name'],'phone'=>$a['phone'],
        'pincode'=>$a['pincode'],'addressLine1'=>$a['address_line1'],'addressLine2'=>$a['address_line2'],
        'city'=>$a['city'],'state'=>$a['state'],'landmark'=>$a['landmark'],'isDefault'=>(bool)$a['is_default'],
        'createdAt'=>$a['created_at'],'updatedAt'=>$a['updated_at']
    ];
}
function validate_address(array $b): array {
    $required=['label','fullName','phone','pincode','addressLine1','city','state'];
    foreach($required as $k) if(trim((string)($b[$k]??''))==='') json_response(422,['success'=>false,'message'=>ucwords(preg_replace('/([A-Z])/', ' $1',$k)).' is required.']);
    $phone=preg_replace('/\\D+/', '', (string)($b['phone']??''));
    if(!is_valid_indian_mobile($phone)) json_response(422,['success'=>false,'message'=>'Enter a valid Indian mobile number.']);
    if(!preg_match('/^[1-9][0-9]{5}$/',(string)($b['pincode']??''))) json_response(422,['success'=>false,'message'=>'Enter a valid six-digit PIN code.']);
    $state=canonical_india_state((string)$b['state']);
    if($state===null) json_response(422,['success'=>false,'message'=>'Choose a valid Indian state or union territory.']);
    if(mb_strlen(trim((string)$b['city']))<2||mb_strlen((string)$b['city'])>100||mb_strlen((string)$b['fullName'])>120||mb_strlen((string)$b['addressLine1'])>255||mb_strlen((string)$b['label'])>60) json_response(422,['success'=>false,'message'=>'Address name, city or street length is invalid.']);
    $b['state']=$state;
    return $b;
}

if ($_SERVER['REQUEST_METHOD']==='GET') {
    $stmt=$db->prepare('SELECT * FROM user_addresses WHERE user_id=:user ORDER BY is_default DESC, created_at DESC');
    $stmt->execute(['user'=>$userId]);
    json_response(200,['success'=>true,'addresses'=>array_map('address_public',$stmt->fetchAll())]);
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $b=validate_address(get_json_body());
    $id=uuid_string(); $now=date('Y-m-d H:i:s');
    $db->beginTransaction();
    try {
        $isDefault=!empty($b['isDefault'])?1:0;
        if($isDefault) $db->prepare('UPDATE user_addresses SET is_default=0 WHERE user_id=:user')->execute(['user'=>$userId]);
        $stmt=$db->prepare('INSERT INTO user_addresses
          (id,user_id,label,full_name,phone,pincode,address_line1,address_line2,city,state,landmark,is_default,created_at)
          VALUES (:id,:user,:label,:full_name,:phone,:pincode,:line1,:line2,:city,:state,:landmark,:default,:created)');
        $stmt->execute([
          'id'=>$id,'user'=>$userId,'label'=>trim($b['label']),'full_name'=>trim($b['fullName']),'phone'=>trim($b['phone']),
          'pincode'=>trim($b['pincode']),'line1'=>trim($b['addressLine1']),'line2'=>trim((string)($b['addressLine2']??'')) ?: null,
          'city'=>trim($b['city']),'state'=>trim($b['state']),'landmark'=>trim((string)($b['landmark']??'')) ?: null,
          'default'=>$isDefault,'created'=>$now
        ]);
        $db->commit();
        $stmt=$db->prepare('SELECT * FROM user_addresses WHERE id=:id AND user_id=:user');
        $stmt->execute(['id'=>$id,'user'=>$userId]);
        json_response(201,['success'=>true,'message'=>'Address saved successfully.','address'=>address_public($stmt->fetch())]);
    } catch(Throwable $e){$db->rollBack(); json_response(500,['success'=>false,'message'=>'Unable to save address.']);}
}

$id=trim((string)($_GET['id']??''));
if($id==='') json_response(422,['success'=>false,'message'=>'Address id is required.']);

if($_SERVER['REQUEST_METHOD']==='DELETE'){
    $stmt=$db->prepare('DELETE FROM user_addresses WHERE id=:id AND user_id=:user');
    $stmt->execute(['id'=>$id,'user'=>$userId]);
    if(!$stmt->rowCount()) json_response(404,['success'=>false,'message'=>'Address not found.']);
    json_response(200,['success'=>true,'message'=>'Address deleted successfully.']);
}

if($_SERVER['REQUEST_METHOD']==='PATCH'){
    $b=validate_address(get_json_body());
    $db->beginTransaction();
    try {
        if(!empty($b['isDefault'])) $db->prepare('UPDATE user_addresses SET is_default=0 WHERE user_id=:user')->execute(['user'=>$userId]);
        $stmt=$db->prepare('UPDATE user_addresses SET label=:label,full_name=:full_name,phone=:phone,pincode=:pincode,
          address_line1=:line1,address_line2=:line2,city=:city,state=:state,landmark=:landmark,is_default=:default,updated_at=:updated
          WHERE id=:id AND user_id=:user');
        $stmt->execute([
          'label'=>trim($b['label']),'full_name'=>trim($b['fullName']),'phone'=>trim($b['phone']),'pincode'=>trim($b['pincode']),
          'line1'=>trim($b['addressLine1']),'line2'=>trim((string)($b['addressLine2']??'')) ?: null,'city'=>trim($b['city']),
          'state'=>trim($b['state']),'landmark'=>trim((string)($b['landmark']??'')) ?: null,
          'default'=>!empty($b['isDefault'])?1:0,'updated'=>date('Y-m-d H:i:s'),'id'=>$id,'user'=>$userId
        ]);
        if(!$stmt->rowCount()){ $db->rollBack(); json_response(404,['success'=>false,'message'=>'Address not found or unchanged.']); }
        $db->commit();
        $stmt=$db->prepare('SELECT * FROM user_addresses WHERE id=:id AND user_id=:user');
        $stmt->execute(['id'=>$id,'user'=>$userId]);
        json_response(200,['success'=>true,'message'=>'Address updated successfully.','address'=>address_public($stmt->fetch())]);
    } catch(Throwable $e){$db->rollBack(); json_response(500,['success'=>false,'message'=>'Unable to update address.']);}
}
json_response(405,['success'=>false,'message'=>'Method not allowed.']);
