<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
require_once __DIR__.'/../lib/vendor.php';
require_once __DIR__.'/../lib/crypto.php';
send_cors_headers();
$context=require_vendor(false);
$vendorId=(string)$context['vendor']['id'];
if(($_SERVER['REQUEST_METHOD']??'')!=='POST')json_response(405,['success'=>false,'message'=>'Method not allowed.']);
$body=get_json_body();
$name=trim((string)($body['beneficiary']??''));
$number=preg_replace('/\s+/','',trim((string)($body['account_number']??'')));
$confirm=preg_replace('/\s+/','',trim((string)($body['account_confirm']??'')));
$ifsc=strtoupper(trim((string)($body['ifsc']??'')));
$errors=[];
if(mb_strlen($name)<2||mb_strlen($name)>150)$errors['beneficiary']='Enter the bank account holder name.';
if(!preg_match('/^[0-9]{8,18}$/',$number))$errors['account_number']='Enter an 8–18 digit account number.';
if($confirm!==$number)$errors['account_confirm']='Account numbers do not match.';
if(!preg_match('/^[A-Z]{4}0[A-Z0-9]{6}$/',$ifsc))$errors['ifsc']='Enter a valid IFSC.';
if($errors)json_response(422,['success'=>false,'message'=>'Correct the highlighted bank details.','errors'=>$errors]);
$db=get_db();$db->beginTransaction();
try{
    $lock=$db->prepare('SELECT id FROM vendors WHERE id=:id FOR UPDATE');$lock->execute(['id'=>$vendorId]);
    $db->prepare('UPDATE vendor_bank_accounts SET is_primary=0 WHERE vendor_id=:vendor AND is_primary=1')->execute(['vendor'=>$vendorId]);
    $id=uuid_string();
    $db->prepare('INSERT INTO vendor_bank_accounts (id,vendor_id,beneficiary_name,account_number_encrypted,account_last4,ifsc_encrypted,verification_status,is_primary,created_at,updated_at) VALUES (:id,:vendor,:name,:number,:last4,:ifsc,"pending",1,UTC_TIMESTAMP(),UTC_TIMESTAMP())')->execute(['id'=>$id,'vendor'=>$vendorId,'name'=>$name,'number'=>encrypt_sensitive($number),'last4'=>substr($number,-4),'ifsc'=>encrypt_sensitive($ifsc)]);
    audit_event('vendor.bank_account_submitted','vendor_bank_account',$id,['last4'=>substr($number,-4)]);
    $db->commit();
    json_response(200,['success'=>true,'message'=>'Bank details saved for verification. Payouts remain locked until approved.']);
}catch(Throwable $error){if($db->inTransaction())$db->rollBack();throw $error;}
