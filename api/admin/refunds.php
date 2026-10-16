<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
require_once __DIR__.'/../lib/admin.php';require_once __DIR__.'/../lib/notifications.php';
send_cors_headers();$context=require_admin('returns.manage');$db=get_db();$method=$_SERVER['REQUEST_METHOD']??'GET';if($method==='GET'){$stmt=$db->query('SELECT f.*,o.order_number,r.vendor_id,r.user_id FROM refunds f JOIN orders o ON o.id=f.order_id LEFT JOIN return_requests r ON r.id=f.return_request_id ORDER BY f.created_at DESC LIMIT 300');json_response(200,['success'=>true,'refunds'=>$stmt->fetchAll()]);}
if($method==='POST'){$body=get_json_body();$id=trim((string)($body['refund_id']??''));$action=trim((string)($body['action']??''));$reference=trim((string)($body['provider_reference']??''));if(!in_array($action,['approve','reject','mark_processing','mark_processed','mark_failed'],true))json_response(422,['success'=>false,'message'=>'Invalid refund action.']);$next=['approve'=>'approved','reject'=>'rejected','mark_processing'=>'processing','mark_processed'=>'processed','mark_failed'=>'failed'][$action];$map=['recommended'=>['approved','rejected'],'approved'=>['processing'],'processing'=>['processed','failed'],'failed'=>['processing']];if($next==='processed'&&mb_strlen($reference)<4)json_response(422,['success'=>false,'message'=>'Payment provider refund reference is required.']);$db->beginTransaction();try{$stmt=$db->prepare('SELECT f.*,r.vendor_id,r.user_id FROM refunds f LEFT JOIN return_requests r ON r.id=f.return_request_id WHERE f.id=:id FOR UPDATE');$stmt->execute(['id'=>$id]);$refund=$stmt->fetch();if(!$refund)json_response(404,['success'=>false,'message'=>'Refund not found.']);assert_transition($refund['status'],$next,$map);$db->prepare('UPDATE refunds SET status=:status,provider_reference=COALESCE(:reference,provider_reference),approved_by=IF(:status="approved",:admin,approved_by),approved_at=IF(:status="approved",UTC_TIMESTAMP(),approved_at),processed_at=IF(:status="processed",UTC_TIMESTAMP(),processed_at),updated_at=UTC_TIMESTAMP() WHERE id=:id')->execute(['status'=>$next,'reference'=>$reference?:null,'admin'=>$context['admin']['id'],'id'=>$id]);
if($next==='processed'&&$refund['vendor_id']){
    $db->prepare('INSERT INTO vendor_ledger_entries
        (id,vendor_id,entry_type,amount,currency,balance_bucket,reference_type,reference_id,description,created_at)
        VALUES (:id,:vendor,"refund_reversal",:amount,"INR","reversed","refund",:reference,"Customer refund reversal",UTC_TIMESTAMP())')
        ->execute([
            'id'=>uuid_string(),
            'vendor'=>$refund['vendor_id'],
            'amount'=>-abs((float)$refund['amount']),
            'reference'=>$id
        ]);

    $db->prepare('UPDATE return_requests SET
        status="refunded",resolution="refund",completed_at=UTC_TIMESTAMP(),updated_at=UTC_TIMESTAMP()
        WHERE id=:id')->execute(['id'=>$refund['return_request_id']]);

    // ── Reverse earned reward points (return-linked only) ──
    if (!empty($refund['return_request_id'])) {
        try {
            require_once __DIR__ . '/../lib/rewards.php';

            $oStmt = $db->prepare('SELECT user_id, total_amount, order_number FROM orders WHERE id = ? LIMIT 1');
            $oStmt->execute([$refund['order_id']]);
            $ord = $oStmt->fetch();

            if ($ord) {
                reward_clawback_for_return(
                    (string)$ord['user_id'],
                    (int)$refund['order_id'],
                    (float)$refund['amount'],
                    (float)$ord['total_amount'],
                    (string)$ord['order_number'],
                    (string)$refund['return_request_id']
                );
            }
        } catch (Throwable $e) {
            error_log('Admin refund clawback failed for refund ' . $id . ': ' . $e->getMessage());
        }
    }
}
    if($refund['user_id'])
    create_notification('customer',$refund['user_id'],'refund_update','Refund status updated','Your refund is now '.$next.'.','../returns.php',['refund_id'=>$id]);record_status_change('refund',$id,$refund['status'],$next);audit_event('admin.refund_'.$action,'refund',$id,['provider_reference'=>$reference?:null]);$db->commit();json_response(200,['success'=>true,'message'=>'Refund status updated.','status'=>$next]);}catch(Throwable $e)
    {if($db->inTransaction())
    $db->rollBack();throw $e;}}
json_response(405,['success'=>false,'message'=>'Method not allowed.']);
