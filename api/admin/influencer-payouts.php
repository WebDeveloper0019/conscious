<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
require_once __DIR__.'/../lib/admin.php';
require_once __DIR__.'/../lib/notifications.php';
send_cors_headers();
$context=require_admin('influencers.manage');
$db=get_db();
$method=$_SERVER['REQUEST_METHOD']??'GET';
if($method==='GET'){
    $profiles=$db->query('SELECT p.influencer_id,i.full_name,i.handle,p.beneficiary_name,p.account_last4,p.pan_last4,p.verification_status,p.updated_at FROM influencer_payout_profiles p JOIN influencers i ON i.id=p.influencer_id ORDER BY p.updated_at DESC LIMIT 200');
    $withdrawals=$db->query('SELECT w.id,w.influencer_id,i.full_name,i.handle,w.amount,w.status,w.payment_reference,w.requested_at,w.reviewed_at,w.paid_at,w.rejection_reason FROM influencer_withdrawals w JOIN influencers i ON i.id=w.influencer_id ORDER BY w.requested_at DESC LIMIT 200');
    json_response(200,['success'=>true,'profiles'=>$profiles->fetchAll(),'withdrawals'=>$withdrawals->fetchAll()]);
}
if($method==='POST'){
    $body=get_json_body();
    $action=trim((string)($body['action']??''));
    $id=trim((string)($body['id']??''));
    $reason=trim((string)($body['reason']??''));
    if($id==='')json_response(422,['success'=>false,'message'=>'Select a payout record.']);
    if(in_array($action,['verify_profile','reject_profile'],true)){
        if($action==='reject_profile'&&mb_strlen($reason)<10)json_response(422,['success'=>false,'message'=>'Explain why the payout profile was rejected.']);
        $db->beginTransaction();
        try{
            $stmt=$db->prepare('SELECT p.verification_status,i.id creator_id FROM influencer_payout_profiles p JOIN influencers i ON i.id=p.influencer_id WHERE p.influencer_id=:id FOR UPDATE');
            $stmt->execute(['id'=>$id]);$profile=$stmt->fetch();
            if(!$profile){$db->rollBack();json_response(404,['success'=>false,'message'=>'Payout profile not found.']);}
            if($profile['verification_status']!=='pending'){$db->rollBack();json_response(409,['success'=>false,'message'=>'Only pending payout profiles can be reviewed.']);}
            $next=$action==='verify_profile'?'verified':'rejected';
            $db->prepare('UPDATE influencer_payout_profiles SET verification_status=:status,updated_at=UTC_TIMESTAMP() WHERE influencer_id=:id')->execute(['status'=>$next,'id'=>$id]);
            create_notification('influencer',$id,'payout_profile','Payout profile reviewed',$next==='verified'?'Your payout profile is verified.':'Your payout profile needs correction. '.$reason,null,[]);
            audit_event('admin.influencer_payout_'.$action,'influencer',$id,['reason'=>$reason]);
            $db->commit();
            json_response(200,['success'=>true,'message'=>'Payout profile updated.']);
        }catch(Throwable $error){if($db->inTransaction())$db->rollBack();throw $error;}
    }
    if(in_array($action,['approve_withdrawal','reject_withdrawal','mark_paid'],true)){
        if($action==='reject_withdrawal'&&mb_strlen($reason)<10)json_response(422,['success'=>false,'message'=>'Explain why the withdrawal was rejected.']);
        $reference=trim((string)($body['payment_reference']??''));
        if($action==='mark_paid'&&(!preg_match('/^[A-Za-z0-9 ._\/-]{6,180}$/',$reference)))json_response(422,['success'=>false,'message'=>'Enter the real bank payment reference before marking paid.']);
        $db->beginTransaction();
        try{
            $stmt=$db->prepare('SELECT * FROM influencer_withdrawals WHERE id=:id FOR UPDATE');$stmt->execute(['id'=>$id]);$withdrawal=$stmt->fetch();
            if(!$withdrawal){$db->rollBack();json_response(404,['success'=>false,'message'=>'Withdrawal not found.']);}
            if(in_array($action,['approve_withdrawal','reject_withdrawal'],true)&&$withdrawal['status']!=='requested'){$db->rollBack();json_response(409,['success'=>false,'message'=>'Only requested withdrawals can be reviewed.']);}
            if($action==='mark_paid'&&!in_array($withdrawal['status'],['approved','processing'],true)){$db->rollBack();json_response(409,['success'=>false,'message'=>'Approve the withdrawal before recording payment.']);}
            if($action!=='reject_withdrawal'){
                $profile=$db->prepare('SELECT verification_status FROM influencer_payout_profiles WHERE influencer_id=:id');
                $profile->execute(['id'=>$withdrawal['influencer_id']]);
                if($profile->fetchColumn()!=='verified'){$db->rollBack();json_response(409,['success'=>false,'message'=>'The creator payout profile must still be verified.']);}
            }
            $next=['approve_withdrawal'=>'approved','reject_withdrawal'=>'rejected','mark_paid'=>'paid'][$action];
            $db->prepare('UPDATE influencer_withdrawals SET status=:status,reviewed_by=:admin,reviewed_at=COALESCE(reviewed_at,UTC_TIMESTAMP()),paid_at=:paid_at,payment_reference=:reference,rejection_reason=:reason WHERE id=:id')->execute(['status'=>$next,'admin'=>$context['admin']['id'],'paid_at'=>$next==='paid'?gmdate('Y-m-d H:i:s'):null,'reference'=>$next==='paid'?$reference:null,'reason'=>$next==='rejected'?$reason:null,'id'=>$id]);
            create_notification('influencer',$withdrawal['influencer_id'],'withdrawal_update','Withdrawal update','Your ₹'.number_format((float)$withdrawal['amount'],2).' withdrawal is '.$next.'.'.($reason!==''?' '.$reason:''),null,[]);
            audit_event('admin.influencer_'.$action,'influencer_withdrawal',$id,['reason'=>$reason,'payment_reference'=>$next==='paid'?$reference:null]);
            $db->commit();
            json_response(200,['success'=>true,'message'=>'Withdrawal updated.','status'=>$next]);
        }catch(Throwable $error){if($db->inTransaction())$db->rollBack();throw $error;}
    }
    json_response(422,['success'=>false,'message'=>'Unsupported payout action.']);
}
json_response(405,['success'=>false,'message'=>'Method not allowed.']);
