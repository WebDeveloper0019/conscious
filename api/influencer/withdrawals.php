<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
send_cors_headers();

try {
    $principal = require_principal(['influencer']);
    $id = $principal['id'];
    $db = get_db();
    $method = $_SERVER['REQUEST_METHOD']??'GET';

    if($method==='GET'){
        $withdrawals = [];
        try {
            $stmt = $db->prepare('SELECT * FROM influencer_withdrawals WHERE influencer_id=:id ORDER BY created_at DESC');
            $stmt->execute(['id'=>$id]);
            $withdrawals = $stmt->fetchAll();
        } catch(Throwable $e) {}
        
        // Calculate available balance
        $earnings = $db->prepare('SELECT COALESCE(SUM(amount),0) FROM influencer_ledger_entries WHERE influencer_id=:id AND status="eligible"');
        $earnings->execute(['id'=>$id]);
        $totalEarned = (float)$earnings->fetchColumn();

        $reserved = $db->prepare('SELECT COALESCE(SUM(amount),0) FROM influencer_withdrawals WHERE influencer_id=:id AND status IN ("requested","approved","processing","paid")');
        $reserved->execute(['id'=>$id]);
        $totalReserved = (float)$reserved->fetchColumn();

        $available = max(0, $totalEarned - $totalReserved);

        json_response(200,[
            'success'=>true,
            'withdrawals'=>$withdrawals,
            'available_balance' => $available
        ]);
    }

    if($method==='POST'){
        $body = get_json_body();
        $rawAmount = $body['amount']??null;
        
        if(!is_numeric($rawAmount) || round((float)$rawAmount,2) < 100) {
            json_response(422,['success'=>false,'message'=>'Minimum withdrawal is ₹100.']);
        }
        $amount = round((float)$rawAmount, 2);

        // 1. Check if payout profile is verified
        $profile = $db->prepare('SELECT verification_status FROM influencer_payout_profiles WHERE influencer_id=:id');
        $profile->execute(['id'=>$id]);
        if($profile->fetchColumn() !== 'verified') {
            json_response(422,['success'=>false,'message'=>'Your payout profile must be verified by admin before requesting a withdrawal.']);
        }

        // 2. Check eligible balance
        $earnings = $db->prepare('SELECT COALESCE(SUM(amount),0) FROM influencer_ledger_entries WHERE influencer_id=:id AND status="eligible"');
        $earnings->execute(['id'=>$id]);
        $totalEarned = (float)$earnings->fetchColumn();

        $reserved = $db->prepare('SELECT COALESCE(SUM(amount),0) FROM influencer_withdrawals WHERE influencer_id=:id AND status IN ("requested","approved","processing","paid")');
        $reserved->execute(['id'=>$id]);
        $totalReserved = (float)$reserved->fetchColumn();

        $available = round($totalEarned - $totalReserved, 2);

        if($amount > $available){
            json_response(422,['success'=>false,'message'=>"Insufficient balance. Available: ₹$available"]);
        }

        // 3. Create Withdrawal Request
        $withdrawalId = uuid_string();
        $db->prepare('INSERT INTO influencer_withdrawals (id, influencer_id, amount, status, created_at) VALUES (:id, :owner, :amount, "requested", UTC_TIMESTAMP())')
           ->execute(['id' => $withdrawalId, 'owner' => $id, 'amount' => $amount]);

        json_response(201,['success'=>true,'message'=>'Withdrawal requested successfully.']);
    }
    
    json_response(405,['success'=>false,'message'=>'Method not allowed.']);

} catch (Throwable $e) {
    json_response(500, ['success' => false, 'message' => 'DEBUG: ' . $e->getMessage()]);
}