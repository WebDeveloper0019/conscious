<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
require_once __DIR__.'/../lib/idempotency.php';
send_cors_headers();
$principal=require_principal(['influencer']);
$id=$principal['id'];
$db=get_db();
$method=$_SERVER['REQUEST_METHOD']??'GET';
if($method==='GET'){
    $stmt=$db->prepare('SELECT * FROM influencer_withdrawals WHERE influencer_id=:id ORDER BY requested_at DESC');
    $stmt->execute(['id'=>$id]);
    json_response(200,['success'=>true,'withdrawals'=>$stmt->fetchAll()]);
}
if($method==='POST'){
    $body=get_json_body();
    $rawAmount=$body['amount']??null;
    if(!is_numeric($rawAmount)||!is_finite((float)$rawAmount)||round((float)$rawAmount,2)<100)
        json_response(422,['success'=>false,'message'=>'Minimum withdrawal is ₹100.']);
    $amount=round((float)$rawAmount,2);
    $idem=begin_idempotent_request('influencer.withdrawal',$principal,$body);
    $db->beginTransaction();
    try{
        // Serialise all requests for one creator, including requests with different idempotency keys.
        $owner=$db->prepare('SELECT id FROM influencers WHERE id=:id FOR UPDATE');
        $owner->execute(['id'=>$id]);
        $profile=$db->prepare('SELECT verification_status FROM influencer_payout_profiles WHERE influencer_id=:id');
        $profile->execute(['id'=>$id]);
        if($profile->fetchColumn()!=='verified'){
            $db->rollBack();
            $response=['success'=>false,'message'=>'Your payout profile must be verified.'];
            finish_idempotent_request((int)$idem['id'],422,$response);
            json_response(422,$response);
        }
        $earnings=$db->prepare('SELECT COALESCE(SUM(amount),0) FROM influencer_ledger_entries WHERE influencer_id=:id AND status="eligible"');
        $earnings->execute(['id'=>$id]);
        $reserved=$db->prepare('SELECT COALESCE(SUM(amount),0) FROM influencer_withdrawals WHERE influencer_id=:id AND status IN ("requested","approved","processing","paid")');
        $reserved->execute(['id'=>$id]);
        $available=round((float)$earnings->fetchColumn()-(float)$reserved->fetchColumn(),2);
        if($amount>$available){
            $db->rollBack();
            $response=['success'=>false,'message'=>'Withdrawal exceeds eligible balance.'];
            finish_idempotent_request((int)$idem['id'],422,$response);
            json_response(422,$response);
        }
        $withdrawal=uuid_string();
        $db->prepare('INSERT INTO influencer_withdrawals (id,influencer_id,amount,status,requested_at) VALUES (:id,:owner,:amount,"requested",UTC_TIMESTAMP())')->execute(['id'=>$withdrawal,'owner'=>$id,'amount'=>$amount]);
        $response=['success'=>true,'message'=>'Withdrawal requested.','withdrawal_id'=>$withdrawal];
        finish_idempotent_request((int)$idem['id'],201,$response);
        $db->commit();
        json_response(201,$response);
    }catch(Throwable $e){
        if($db->inTransaction())$db->rollBack();
        throw $e;
    }
}
json_response(405,['success'=>false,'message'=>'Method not allowed.']);
