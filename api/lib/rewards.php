<?php
declare(strict_types=1);
require_once __DIR__ . '/helpers.php';

/**
 * Award points for a delivered order. Idempotent.
 */
function reward_earn_for_order(string $userId, int $orderId, float $orderTotal, string $orderNumber): int
{
    if ($orderTotal <= 0) return 0;
    $points = max(1, (int)floor($orderTotal / 100));
    $db = get_db();

    $chk = $db->prepare(
        "SELECT points FROM customer_reward_ledger
         WHERE user_id = ? AND entry_type = 'earned'
           AND reference_type = 'order' AND reference_id = ? LIMIT 1"
    );
    $chk->execute([$userId, (string)$orderId]);
    if ($chk->fetchColumn() !== false) return 0;

    $db->prepare(
        'INSERT INTO customer_rewards (user_id, balance_points, lifetime_earned, lifetime_redeemed, updated_at)
         VALUES (?, 0, 0, 0, UTC_TIMESTAMP())
         ON DUPLICATE KEY UPDATE user_id = user_id'
    )->execute([$userId]);

    $db->prepare('SELECT balance_points FROM customer_rewards WHERE user_id = ? FOR UPDATE')
       ->execute([$userId]);

    $db->prepare(
        "INSERT INTO customer_reward_ledger
           (id, user_id, points, entry_type, reference_type, reference_id, description, expires_at, status, created_at)
         VALUES (?, ?, ?, 'earned', 'order', ?, ?, DATE_ADD(UTC_TIMESTAMP(), INTERVAL 365 DAY), 'active', UTC_TIMESTAMP())"
    )->execute([
        uuid_string(),
        $userId,
        $points,
        (string)$orderId,
        'Reward for delivered order #' . $orderNumber,
    ]);

    $db->prepare(
        'UPDATE customer_rewards
         SET balance_points = balance_points + ?,
             lifetime_earned = lifetime_earned + ?,
             updated_at = UTC_TIMESTAMP()
         WHERE user_id = ?'
    )->execute([$points, $points, $userId]);

    return $points;
}

/**
 * Redeem points against an order. Applies caps. Returns points actually deducted.
 */
function reward_redeem_for_order(string $userId, int $orderId, int $requestedPoints,
                                 float $orderSubtotal, string $orderNumber): int
{
    if ($requestedPoints <= 0) return 0;
    $db = get_db();

    $db->prepare(
        'INSERT INTO customer_rewards (user_id, balance_points, lifetime_earned, lifetime_redeemed, updated_at)
         VALUES (?, 0, 0, 0, UTC_TIMESTAMP())
         ON DUPLICATE KEY UPDATE user_id = user_id'
    )->execute([$userId]);

    $acctStmt = $db->prepare('SELECT balance_points FROM customer_rewards WHERE user_id = ? FOR UPDATE');
    $acctStmt->execute([$userId]);
    $balance = (int)$acctStmt->fetchColumn();

    if ($balance <= 0) throw new RuntimeException('No reward points available.');

    $maxPercent = 20.0;
    $minPoints  = 100;
    $pointValue = 1.0;

    $maxDiscountRs  = round($orderSubtotal * ($maxPercent / 100), 2);
    $maxPointsByCap = (int)floor($maxDiscountRs / $pointValue);

    $points = min($requestedPoints, $balance, $maxPointsByCap);
    if ($points < $minPoints) {
        throw new RuntimeException("Minimum {$minPoints} points required to redeem.");
    }

    $db->prepare(
        "INSERT INTO customer_reward_ledger
           (id, user_id, points, entry_type, reference_type, reference_id, description, expires_at, status, created_at)
         VALUES (?, ?, ?, 'redeemed', 'order', ?, ?, NULL, 'active', UTC_TIMESTAMP())"
    )->execute([
        uuid_string(),
        $userId,
        -$points,
        (string)$orderId,
        'Redeemed on order #' . $orderNumber,
    ]);

    $db->prepare(
        'UPDATE customer_rewards
         SET balance_points = balance_points - ?,
             lifetime_redeemed = lifetime_redeemed + ?,
             updated_at = UTC_TIMESTAMP()
         WHERE user_id = ?'
    )->execute([$points, $points, $userId]);

    return $points;
}

/**
 * Refund points spent on a cancelled order. Idempotent.
 */
function reward_refund_for_order(string $userId, int $orderId, string $orderNumber): int
{
    $db = get_db();

    $spentStmt = $db->prepare(
        "SELECT COALESCE(SUM(ABS(points)), 0) FROM customer_reward_ledger
         WHERE user_id = ? AND entry_type = 'redeemed'
           AND reference_type = 'order' AND reference_id = ?"
    );
    $spentStmt->execute([$userId, (string)$orderId]);
    $spent = (int)$spentStmt->fetchColumn();
    if ($spent <= 0) return 0;

    $chk = $db->prepare(
        "SELECT COUNT(*) FROM customer_reward_ledger
         WHERE user_id = ? AND entry_type = 'refund'
           AND reference_type = 'order' AND reference_id = ?"
    );
    $chk->execute([$userId, (string)$orderId]);
    if ((int)$chk->fetchColumn() > 0) return 0;

    $db->prepare(
        'INSERT INTO customer_rewards (user_id, balance_points, lifetime_earned, lifetime_redeemed, updated_at)
         VALUES (?, 0, 0, 0, UTC_TIMESTAMP())
         ON DUPLICATE KEY UPDATE user_id = user_id'
    )->execute([$userId]);

    $db->prepare('SELECT balance_points FROM customer_rewards WHERE user_id = ? FOR UPDATE')
       ->execute([$userId]);

    $db->prepare(
        "INSERT INTO customer_reward_ledger
           (id, user_id, points, entry_type, reference_type, reference_id, description, expires_at, status, created_at)
         VALUES (?, ?, ?, 'refund', 'order', ?, ?, NULL, 'active', UTC_TIMESTAMP())"
    )->execute([
        uuid_string(),
        $userId,
        $spent,
        (string)$orderId,
        'Points refunded for cancelled order #' . $orderNumber,
    ]);

    $db->prepare(
        'UPDATE customer_rewards
         SET balance_points = balance_points + ?, updated_at = UTC_TIMESTAMP()
         WHERE user_id = ?'
    )->execute([$spent, $userId]);

    return $spent;
}

/**
 * Claw back points earned on a cancelled order. Idempotent.
 */
function reward_clawback_for_order(string $userId, int $orderId, string $orderNumber): int
{
    $db = get_db();

    $earnedStmt = $db->prepare(
        "SELECT COALESCE(SUM(points), 0) FROM customer_reward_ledger
         WHERE user_id = ? AND entry_type = 'earned'
           AND reference_type = 'order' AND reference_id = ?"
    );
    $earnedStmt->execute([$userId, (string)$orderId]);
    $earned = (int)$earnedStmt->fetchColumn();
    if ($earned <= 0) return 0;

    $chk = $db->prepare(
        "SELECT COUNT(*) FROM customer_reward_ledger
         WHERE user_id = ? AND entry_type = 'clawback'
           AND reference_type = 'order' AND reference_id = ?"
    );
    $chk->execute([$userId, (string)$orderId]);
    if ((int)$chk->fetchColumn() > 0) return 0;

    $db->prepare(
        'INSERT INTO customer_rewards (user_id, balance_points, lifetime_earned, lifetime_redeemed, updated_at)
         VALUES (?, 0, 0, 0, UTC_TIMESTAMP())
         ON DUPLICATE KEY UPDATE user_id = user_id'
    )->execute([$userId]);

    $db->prepare('SELECT balance_points FROM customer_rewards WHERE user_id = ? FOR UPDATE')
       ->execute([$userId]);

    $db->prepare(
        "INSERT INTO customer_reward_ledger
           (id, user_id, points, entry_type, reference_type, reference_id, description, expires_at, status, created_at)
         VALUES (?, ?, ?, 'clawback', 'order', ?, ?, NULL, 'active', UTC_TIMESTAMP())"
    )->execute([
        uuid_string(),
        $userId,
        -$earned,
        (string)$orderId,
        'Clawback for cancelled order #' . $orderNumber,
    ]);

    $db->prepare(
        'UPDATE customer_rewards
         SET balance_points = GREATEST(0, balance_points - ?), updated_at = UTC_TIMESTAMP()
         WHERE user_id = ?'
    )->execute([$earned, $userId]);

    return $earned;
}