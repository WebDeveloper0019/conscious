<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/idempotency.php';

send_cors_headers();
$principal = require_principal(['customer']);
$db = get_db();
$userId = $principal['id'];
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

function reward_expire_due(PDO $db, string $userId): void {
    $stmt = $db->prepare(
        "UPDATE customer_reward_ledger
         SET status='expired'
         WHERE user_id=:user AND status='active'
           AND points > 0 AND expires_at IS NOT NULL AND expires_at <= UTC_TIMESTAMP()"
    );
    $stmt->execute(['user' => $userId]);
}

if ($method === 'GET') {
    $balanceStmt = $db->prepare(
        "SELECT COALESCE(SUM(points),0)
         FROM customer_reward_ledger
         WHERE user_id=:user AND status='active'
           AND (points < 0 OR expires_at IS NULL OR expires_at > UTC_TIMESTAMP())"
    );
    $balanceStmt->execute(['user' => $userId]);
    $balance = max(0, (int)$balanceStmt->fetchColumn());

    $stmt = $db->prepare(
        "SELECT id,points,entry_type,reference_type,reference_id,description,expires_at,status,created_at
         FROM customer_reward_ledger
         WHERE user_id=:user
         ORDER BY created_at DESC LIMIT 100"
    );
    $stmt->execute(['user' => $userId]);

    $history = [];
    foreach ($stmt->fetchAll() as $row) {
        $status = ($row['status']==='active' && $row['expires_at'] !== null && strtotime((string)$row['expires_at']) <= time()) ? 'expired' : $row['status'];
        $history[] = [
            'id'=>$row['id'], 'points'=>(int)$row['points'],
            'entryType'=>$row['entry_type'], 'referenceType'=>$row['reference_type'],
            'referenceId'=>$row['reference_id'], 'description'=>$row['description'],
            'expiresAt'=>$row['expires_at'], 'status'=>$status,
            'createdAt'=>$row['created_at'],
        ];
    }

    json_response(200, ['success'=>true,'balance'=>$balance,'points'=>$balance,'transactions'=>$history]);
}

if ($method !== 'POST') {
    json_response(405, ['success'=>false,'message'=>'Method not allowed.']);
}

$body = get_json_body();
$action = strtolower(trim((string)($body['action'] ?? '')));
if (!in_array($action, ['earn','redeem','expire'], true)) {
    json_response(422, ['success'=>false,'message'=>'Action must be earn, redeem or expire.']);
}

if ($action === 'expire') {
    reward_expire_due($db, $userId);
    json_response(200, ['success'=>true,'message'=>'Expired reward points have been updated.']);
}

if ($action === 'earn') {
    $referenceType = strtolower(trim((string)($body['reference_type'] ?? '')));
    $referenceId = trim((string)($body['reference_id'] ?? ''));
    if ($referenceType !== 'order' || !preg_match('/^[0-9]+$/', $referenceId)) {
        json_response(422, ['success'=>false,'message'=>'A valid delivered order reference is required to earn points.']);
    }

    $order = $db->prepare(
        "SELECT id,total_amount,status FROM orders WHERE id=:id AND user_id=:user LIMIT 1"
    );
    $order->execute(['id'=>$referenceId,'user'=>$userId]);
    $orderRow = $order->fetch();
    if (!$orderRow) json_response(404, ['success'=>false,'message'=>'Order not found.']);
    if ($orderRow['status'] !== 'delivered') {
        json_response(422, ['success'=>false,'message'=>'Reward points can only be earned after delivery.']);
    }

    // Server-calculated reward: 1 point per completed ₹100, minimum 1.
    $points = max(1, (int)floor((float)$orderRow['total_amount'] / 100));
    $payload = ['action'=>'earn','reference_type'=>'order','reference_id'=>$referenceId];
    $idem = begin_idempotent_request('customer.rewards.earn', $principal, $payload);

    try {
        $db->beginTransaction();
        $existing = $db->prepare(
            "SELECT id,points,status FROM customer_reward_ledger
             WHERE user_id=:user AND entry_type='earned'
               AND reference_type='order' AND reference_id=:ref
             LIMIT 1 FOR UPDATE"
        );
        $existing->execute(['user'=>$userId,'ref'=>$referenceId]);
        $row = $existing->fetch();
        if ($row) {
            $response = ['success'=>true,'message'=>'Reward for this order was already recorded.','pointsEarned'=>(int)$row['points'],'transactionId'=>$row['id'],'duplicate'=>true];
            $db->commit();
            finish_idempotent_request((int)$idem['id'],200,$response);
            json_response(200,$response);
        }

        $id = uuid_string();
        $db->prepare(
            "INSERT INTO customer_reward_ledger
             (id,user_id,points,entry_type,reference_type,reference_id,description,expires_at,status,created_at)
             VALUES (:id,:user,:points,'earned','order',:ref,'Reward points for delivered order',DATE_ADD(UTC_TIMESTAMP(),INTERVAL 365 DAY),'active',UTC_TIMESTAMP())"
        )->execute(['id'=>$id,'user'=>$userId,'points'=>$points,'ref'=>$referenceId]);

        $db->commit();
        $response = ['success'=>true,'message'=>'Reward points earned.','pointsEarned'=>$points,'transactionId'=>$id,'duplicate'=>false];
        finish_idempotent_request((int)$idem['id'],201,$response);
        json_response(201,$response);
    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        $response=['success'=>false,'message'=>'Unable to record reward points.'];
        finish_idempotent_request((int)$idem['id'],500,$response);
        json_response(500,$response);
    }
}

$points = (int)($body['points'] ?? 0);
if ($points < 1 || $points > 100000) {
    json_response(422, ['success'=>false,'message'=>'Redeem between 1 and 100000 points.']);
}
$payload = ['action'=>'redeem','points'=>$points];
$idem = begin_idempotent_request('customer.rewards.redeem', $principal, $payload);

try {
    $db->beginTransaction();
    reward_expire_due($db, $userId);

    $balanceStmt = $db->prepare(
        "SELECT COALESCE(SUM(points),0) FROM customer_reward_ledger
         WHERE user_id=:user AND status='active'
           AND (points < 0 OR expires_at IS NULL OR expires_at > UTC_TIMESTAMP())"
    );
    $balanceStmt->execute(['user'=>$userId]);
    $balance = max(0,(int)$balanceStmt->fetchColumn());
    if ($points > $balance) {
        $db->rollBack();
        $response=['success'=>false,'message'=>'Insufficient reward points.','availablePoints'=>$balance];
        finish_idempotent_request((int)$idem['id'],422,$response);
        json_response(422,$response);
    }

    $id=uuid_string();
    $db->prepare(
        "INSERT INTO customer_reward_ledger
         (id,user_id,points,entry_type,reference_type,reference_id,description,expires_at,status,created_at)
         VALUES (:id,:user,:points,'redeemed','reward_redemption',:ref,'Reward points redeemed',NULL,'active',UTC_TIMESTAMP())"
    )->execute(['id'=>$id,'user'=>$userId,'points'=>-$points,'ref'=>$id]);

    $db->commit();
    $response=['success'=>true,'message'=>'Reward points redeemed.','pointsRedeemed'=>$points,'transactionId'=>$id,'remainingPoints'=>$balance-$points];
    finish_idempotent_request((int)$idem['id'],200,$response);
    json_response(200,$response);
} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    $response=['success'=>false,'message'=>'Unable to redeem reward points.'];
    finish_idempotent_request((int)$idem['id'],500,$response);
    json_response(500,$response);
}
