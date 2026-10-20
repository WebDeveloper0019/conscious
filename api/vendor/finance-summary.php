<?php
declare(strict_types=1);

require_once __DIR__.'/../lib/helpers.php';
require_once __DIR__.'/../lib/vendor.php';

send_cors_headers();
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    json_response(405, ['success'=>false,'message'=>'Method not allowed.']);
}
$context=require_vendor(true);
$vendorId=$context['vendor']['id'];
$db=get_db();

$ledger=$db->prepare('SELECT id,entry_type,amount,currency,balance_bucket,reference_type,reference_id,description,available_at,created_at FROM vendor_ledger_entries WHERE vendor_id=:vendor ORDER BY created_at DESC,id DESC LIMIT 200');
$ledger->execute(['vendor'=>$vendorId]);
$banks=$db->prepare('SELECT id,beneficiary_name,account_last4,bank_name,verification_status,is_primary FROM vendor_bank_accounts WHERE vendor_id=:vendor ORDER BY is_primary DESC,created_at DESC');
$banks->execute(['vendor'=>$vendorId]);

json_response(200, [
    'success'=>true,
    'ledger'=>$ledger->fetchAll(),
    'bank_accounts'=>$banks->fetchAll(),
]);
