<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

function reward_config(string $key, float $default): float
{
    $stmt = get_db()->prepare('SELECT value FROM customer_reward_rules WHERE rule_key=:rule LIMIT 1');
    $stmt->execute(['rule' => $key]);
    $value = $stmt->fetchColumn();
    return $value === false ? $default : (float)$value;
}

function reward_balance(string $userId, bool $lock = false): array
{
    $db = get_db();
    $db->prepare(
        'INSERT INTO customer_rewards (user_id,balance_points,lifetime_earned,lifetime_redeemed,updated_at)
         VALUES (:user,0,0,0,UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE user_id=user_id'
    )->execute(['user' => $userId]);
    $sql = 'SELECT * FROM customer_rewards WHERE user_id=:user' . ($lock ? ' FOR UPDATE' : '');
    $stmt = $db->prepare($sql);
    $stmt->execute(['user' => $userId]);
    $account = $stmt->fetch() ?: ['balance_points' => 0, 'lifetime_earned' => 0, 'lifetime_redeemed' => 0];
    $db->prepare(
        'UPDATE customer_reward_ledger SET status="expired"
         WHERE user_id=:user AND status="active" AND points>0 AND expires_at IS NOT NULL AND expires_at<=UTC_TIMESTAMP()'
    )->execute(['user' => $userId]);
    $sum = $db->prepare('SELECT GREATEST(0,COALESCE(SUM(points),0)) FROM customer_reward_ledger WHERE user_id=:user AND status="active"');
    $sum->execute(['user' => $userId]);
    $account['balance_points'] = (int)$sum->fetchColumn();
    $db->prepare('UPDATE customer_rewards SET balance_points=:balance,updated_at=UTC_TIMESTAMP() WHERE user_id=:user')
       ->execute(['balance' => $account['balance_points'], 'user' => $userId]);
    return $account;
}

function reward_reference_exists(string $userId, int $orderId, string $entryType): bool
{
    $stmt = get_db()->prepare(
        'SELECT 1 FROM customer_reward_ledger
         WHERE user_id=:user AND entry_type=:type AND reference_type="order" AND reference_id=:order_id LIMIT 1'
    );
    $stmt->execute(['user' => $userId, 'type' => $entryType, 'order_id' => (string)$orderId]);
    return $stmt->fetchColumn() !== false;
}

function reward_earn_for_order(string $userId, int $orderId, float $orderTotal, string $orderNumber): int
{
    if ($orderTotal <= 0 || reward_reference_exists($userId, $orderId, 'earned')) return 0;
    $db = get_db();
    reward_balance($userId, true);
    if (reward_reference_exists($userId, $orderId, 'earned')) return 0;
    $points = max(1, (int)floor($orderTotal * reward_config('earn_rate_percent', 2.0) / 100));
    $expiryDays = max(1, (int)reward_config('expiry_days', 365));
    $expiresAt = (new DateTimeImmutable('now', new DateTimeZone('UTC')))
        ->modify('+' . $expiryDays . ' days')->format('Y-m-d H:i:s');
    $db->prepare(
        'INSERT INTO customer_reward_ledger
         (id,user_id,points,entry_type,reference_type,reference_id,description,expires_at,status,created_at)
         VALUES (:id,:user,:points,"earned","order",:order_id,:description,
                 :expires_at,"active",UTC_TIMESTAMP())'
    )->execute([
        'id' => uuid_string(), 'user' => $userId, 'points' => $points,
        'order_id' => (string)$orderId, 'description' => 'Reward for order #' . $orderNumber,
        'expires_at' => $expiresAt,
    ]);
    $db->prepare(
        'UPDATE customer_rewards SET balance_points=balance_points+:points,
         lifetime_earned=lifetime_earned+:points,updated_at=UTC_TIMESTAMP() WHERE user_id=:user'
    )->execute(['points' => $points, 'user' => $userId]);
    return $points;
}

function reward_award_if_eligible(int $orderId): int
{
    $stmt = get_db()->prepare(
        'SELECT user_id,order_number,total_amount,status,payment_status FROM orders WHERE id=:id FOR UPDATE'
    );
    $stmt->execute(['id' => $orderId]);
    $order = $stmt->fetch();
    if (!$order || $order['status'] !== 'delivered' || $order['payment_status'] !== 'paid') return 0;
    return reward_earn_for_order((string)$order['user_id'], $orderId, (float)$order['total_amount'], (string)$order['order_number']);
}

function reward_redeem_for_order(string $userId, int $orderId, int $requestedPoints, float $subtotal, string $orderNumber): int
{
    if ($requestedPoints <= 0) return 0;
    if (reward_reference_exists($userId, $orderId, 'redeemed')) {
        throw new RuntimeException('Reward points were already redeemed for this order.');
    }
    $db = get_db();
    $account = reward_balance($userId, true);
    $minimum = max(1, (int)reward_config('min_redeem_points', 100));
    $pointValue = max(0.01, reward_config('point_value_rupees', 1.0));
    $cap = (int)floor(($subtotal * reward_config('redeem_max_percent', 20.0) / 100) / $pointValue);
    if ($requestedPoints < $minimum || $requestedPoints > (int)$account['balance_points'] || $requestedPoints > $cap) {
        throw new RuntimeException('Reward balance or redemption limit changed. Please recalculate checkout.');
    }
    $db->prepare(
        'INSERT INTO customer_reward_ledger
         (id,user_id,points,entry_type,reference_type,reference_id,description,expires_at,status,created_at)
         VALUES (:id,:user,:points,"redeemed","order",:order_id,:description,NULL,"active",UTC_TIMESTAMP())'
    )->execute([
        'id' => uuid_string(), 'user' => $userId, 'points' => -$requestedPoints,
        'order_id' => (string)$orderId, 'description' => 'Redeemed on order #' . $orderNumber,
    ]);
    $db->prepare(
        'UPDATE customer_rewards SET balance_points=balance_points-:points,
         lifetime_redeemed=lifetime_redeemed+:points,updated_at=UTC_TIMESTAMP() WHERE user_id=:user'
    )->execute(['points' => $requestedPoints, 'user' => $userId]);
    return $requestedPoints;
}

function reward_refund_for_order(string $userId, int $orderId, string $orderNumber): int
{
    if (reward_reference_exists($userId, $orderId, 'refund')) return 0;
    $db = get_db();
    $stmt = $db->prepare(
        'SELECT COALESCE(SUM(ABS(points)),0) FROM customer_reward_ledger
         WHERE user_id=:user AND entry_type="redeemed" AND reference_type="order" AND reference_id=:order_id'
    );
    $stmt->execute(['user' => $userId, 'order_id' => (string)$orderId]);
    $points = (int)$stmt->fetchColumn();
    if ($points <= 0) return 0;
    reward_balance($userId, true);
    $db->prepare(
        'INSERT INTO customer_reward_ledger
         (id,user_id,points,entry_type,reference_type,reference_id,description,expires_at,status,created_at)
         VALUES (:id,:user,:points,"refund","order",:order_id,:description,NULL,"active",UTC_TIMESTAMP())'
    )->execute([
        'id' => uuid_string(), 'user' => $userId, 'points' => $points,
        'order_id' => (string)$orderId, 'description' => 'Points refunded for cancelled order #' . $orderNumber,
    ]);
    $db->prepare('UPDATE customer_rewards SET balance_points=balance_points+:points,updated_at=UTC_TIMESTAMP() WHERE user_id=:user')
       ->execute(['points' => $points, 'user' => $userId]);
    return $points;
}
