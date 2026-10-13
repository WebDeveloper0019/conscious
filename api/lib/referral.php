<?php
declare(strict_types=1);
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';

const REFERRAL_REWARD_POINTS = 500;          // 1 point = ₹1 in your UI
const REFERRAL_ENTRY_TYPE    = 'referral_bonus';
const REFERRAL_REF_TYPE      = 'referral';

/**
 * Get (or lazily create) a user's referral code.
 * Codes are 8 chars, uppercase alnum, avoiding ambiguous 0/O/1/I.
 */
function referral_code_for(string $userId): string {
    $db = get_db();
    $stmt = $db->prepare('SELECT code FROM referral_codes WHERE user_id=:u LIMIT 1');
    $stmt->execute(['u' => $userId]);
    $code = $stmt->fetchColumn();
    if ($code) return (string)$code;

    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // no 0,O,1,I
    $maxTries = 8;
    for ($i = 0; $i < $maxTries; $i++) {
        $code = '';
        for ($j = 0; $j < 8; $j++) $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        try {
            $db->prepare('INSERT INTO referral_codes (user_id, code, created_at) VALUES (:u,:c,UTC_TIMESTAMP())')
               ->execute(['u' => $userId, 'c' => $code]);
            return $code;
        } catch (PDOException $e) {
            if ((string)$e->getCode() !== '23000') throw $e; // collision → retry
        }
    }
    throw new RuntimeException('Could not allocate a referral code.');
}

/**
 * Record that $refereeId signed up using $code.
 * Safe to call multiple times — the UNIQUE(referee_id) makes it idempotent.
 */
function referral_attach_on_signup(string $refereeId, string $code): void {
    $code = strtoupper(trim($code));
    if ($code === '' || !preg_match('/^[A-Z0-9]{4,20}$/', $code)) return;

    $db = get_db();
    $stmt = $db->prepare('SELECT user_id FROM referral_codes WHERE code=:c LIMIT 1');
    $stmt->execute(['c' => $code]);
    $referrerId = $stmt->fetchColumn();
    if (!$referrerId || $referrerId === $refereeId) return; // no self-referral

    try {
        $db->prepare(
            'INSERT INTO referrals (id,referrer_id,referee_id,code_used,status,created_at)
             VALUES (:id,:r,:e,:c,"pending",UTC_TIMESTAMP())'
        )->execute([
            'id' => uuid_string(), 'r' => $referrerId, 'e' => $refereeId, 'c' => $code,
        ]);
    } catch (PDOException $e) {
        if ((string)$e->getCode() !== '23000') throw $e; // already referred — ignore
    }
}

/**
 * Called when a user's FIRST paid order is confirmed.
 * If they were referred, credit the referrer's ledger and mark the referral completed.
 * Idempotent thanks to the UNIQUE key on customer_reward_ledger.
 */
function referral_reward_on_first_order(string $buyerId): void {
    $db = get_db();

    // Only fire for the buyer's very first paid order.
    $paid = $db->prepare("SELECT COUNT(*) FROM orders WHERE user_id=:u AND payment_status='paid'");
    $paid->execute(['u' => $buyerId]);
    if ((int)$paid->fetchColumn() !== 1) return;

    $stmt = $db->prepare('SELECT * FROM referrals WHERE referee_id=:e AND status="pending" LIMIT 1');
    $stmt->execute(['e' => $buyerId]);
    $ref = $stmt->fetch();
    if (!$ref) return;

    $referrerId = (string)$ref['referrer_id'];
    $points     = REFERRAL_REWARD_POINTS;
    $ledgerId   = uuid_string();

    try {
        $db->prepare(
            'INSERT INTO customer_reward_ledger
                (id,user_id,points,entry_type,reference_type,reference_id,description,created_at,expires_at,status)
             VALUES
                (:id,:u,:p,:et,:rt,:ri,:d,UTC_TIMESTAMP(),NULL,"active")'
        )->execute([
            'id' => $ledgerId,
            'u'  => $referrerId,
            'p'  => $points,
            'et' => REFERRAL_ENTRY_TYPE,
            'rt' => REFERRAL_REF_TYPE,
            'ri' => (string)$ref['id'],
            'd'  => 'Referral bonus — your friend made their first purchase',
        ]);
    } catch (PDOException $e) {
        if ((string)$e->getCode() === '23000') return; // already rewarded
        throw $e;
    }

    $db->prepare('UPDATE referrals SET status="completed", reward_points=:p, completed_at=UTC_TIMESTAMP() WHERE id=:id')
       ->execute(['p' => $points, 'id' => $ref['id']]);
}