<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/notifications.php';

/**
 * Read a boolean/numeric expiry config from platform_settings.
 */
function reward_expiry_cfg(string $key, $default = null)
{
    static $cache = [];
    if (array_key_exists($key, $cache)) return $cache[$key];

    $stmt = get_db()->prepare('SELECT value_json FROM platform_settings WHERE setting_key = ?');
    $stmt->execute([$key]);
    $raw = $stmt->fetchColumn();
    if ($raw === false) return $cache[$key] = $default;

    $decoded = json_decode((string)$raw, true);
    return $cache[$key] = ($decoded['value'] ?? $default);
}

/**
 * Find all active earned points that have expired (expires_at <= now).
 */
function reward_expiry_find_expired(int $limit = 500): array
{
    $db = get_db();
    $stmt = $db->prepare(
        'SELECT user_id, DATE(expires_at) AS expiry_date,
                SUM(points) AS total_points,
                COUNT(*) AS entry_count
         FROM customer_reward_ledger
         WHERE status = "active"
           AND points > 0
           AND expires_at IS NOT NULL
           AND expires_at <= UTC_TIMESTAMP()
         GROUP BY user_id, DATE(expires_at)
         ORDER BY expires_at ASC
         LIMIT ' . (int)$limit
    );
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Find upcoming expiries within N days.
 */
function reward_expiry_find_upcoming(int $minDays, int $maxDays, int $limit = 1000): array
{
    $db = get_db();
    $stmt = $db->prepare(
        'SELECT user_id, DATE(expires_at) AS expiry_date,
                SUM(points) AS total_points,
                COUNT(*) AS entry_count
         FROM customer_reward_ledger
         WHERE status = "active"
           AND points > 0
           AND expires_at IS NOT NULL
           AND DATE(expires_at) BETWEEN DATE_ADD(CURDATE(), INTERVAL ? DAY)
                                    AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
         GROUP BY user_id, DATE(expires_at)
         ORDER BY expires_at ASC
         LIMIT ' . (int)$limit
    );
    $stmt->execute([$minDays, $maxDays]);
    return $stmt->fetchAll();
}

/**
 * Has this exact (user, type, expiry_date) notification been sent?
 */
function reward_expiry_already_sent(string $userId, string $type, string $expiryDate): bool
{
    $stmt = get_db()->prepare(
        'SELECT 1 FROM reward_expiry_notifications
         WHERE user_id = ? AND notification_type = ? AND expiry_date = ?
         LIMIT 1'
    );
    $stmt->execute([$userId, $type, $expiryDate]);
    return $stmt->fetchColumn() !== false;
}

/**
 * Record a notification was sent (idempotent).
 */
function reward_expiry_mark_sent(string $userId, string $type, string $expiryDate, int $points, array $meta = []): void
{
    try {
        get_db()->prepare(
            'INSERT INTO reward_expiry_notifications
               (id, user_id, notification_type, expiry_date, points_at_send, sent_at, metadata_json)
             VALUES (?, ?, ?, ?, ?, UTC_TIMESTAMP(), ?)
             ON DUPLICATE KEY UPDATE sent_at = VALUES(sent_at), points_at_send = VALUES(points_at_send)'
        )->execute([
            uuid_string(),
            $userId,
            $type,
            $expiryDate,
            $points,
            $meta ? json_encode($meta, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null,
        ]);
    } catch (Throwable $e) {
        error_log('reward_expiry_mark_sent failed: ' . $e->getMessage());
    }
}

/**
 * Sweep: mark expired ledger entries + reconcile the balance cache.
 */
function reward_expiry_sweep(): array
{
    $db = get_db();

    // 1. Mark all past-expiry active rows as 'expired'
    $sweep = $db->prepare(
        'UPDATE customer_reward_ledger
         SET status = "expired"
         WHERE status = "active"
           AND points > 0
           AND expires_at IS NOT NULL
           AND expires_at <= UTC_TIMESTAMP()'
    );
    $sweep->execute();
    $entriesExpired = $sweep->rowCount();

    if ($entriesExpired === 0) {
        return ['entries_expired' => 0, 'users_affected' => 0];
    }

    // 2. Reconcile balance for every affected user.
    $userStmt = $db->query(
        'SELECT DISTINCT user_id FROM customer_reward_ledger WHERE status = "expired"'
    );
    $affectedUsers = array_column($userStmt->fetchAll(), 'user_id');

    $recalc = $db->prepare(
        'SELECT COALESCE(SUM(points), 0)
         FROM customer_reward_ledger
         WHERE user_id = ?
           AND status = "active"
           AND (points < 0 OR expires_at IS NULL OR expires_at > UTC_TIMESTAMP())'
    );

    $update = $db->prepare(
        'UPDATE customer_rewards
         SET balance_points = GREATEST(0, ?),
             updated_at = UTC_TIMESTAMP()
         WHERE user_id = ?'
    );

    foreach ($affectedUsers as $uid) {
        $recalc->execute([$uid]);
        $trueBalance = max(0, (int)$recalc->fetchColumn());
        $update->execute([$trueBalance, $uid]);
    }

    return [
        'entries_expired' => $entriesExpired,
        'users_affected'  => count($affectedUsers),
    ];
}

/**
 * Send warnings for a given window. Stage name = warn_30d / warn_7d / warn_1d.
 */
function reward_expiry_send_warnings(int $minDays, int $maxDays, string $stage): int
{
    $rows = reward_expiry_find_upcoming($minDays, $maxDays);
    $sent = 0;

    foreach ($rows as $row) {
        $userId     = (string)$row['user_id'];
        $expiryDate = (string)$row['expiry_date'];
        $points     = (int)$row['total_points'];

        if ($points <= 0) continue;
        if (reward_expiry_already_sent($userId, $stage, $expiryDate)) continue;

        $expiryTs = strtotime($expiryDate . ' 23:59:59 UTC');
        $daysLeft = max(0, (int)ceil(($expiryTs - time()) / 86400));

        if ($stage === 'warn_30d') {
            $title = 'Your reward points will expire soon';
        } elseif ($stage === 'warn_7d') {
            $title = 'Reminder: reward points expiring in 7 days';
        } elseif ($stage === 'warn_1d') {
            $title = 'Last chance: reward points expire tomorrow';
        } else {
            $title = 'Reward points expiring';
        }

        $dayWord = ($daysLeft === 1) ? 'day' : 'days';
        $message = sprintf(
            'You have %s Craft Points expiring on %s (in %d %s). Redeem them at checkout before they are gone.',
            number_format($points),
            date('d M Y', strtotime($expiryDate)),
            $daysLeft,
            $dayWord
        );

        try {
            create_notification(
                'customer',
                $userId,
                'reward_expiring',
                $title,
                $message,
                '/portals/modules/customer/rewards.php',
                [
                    'points'      => $points,
                    'expiry_date' => $expiryDate,
                    'days_left'   => $daysLeft,
                    'stage'       => $stage,
                ]
            );

            reward_expiry_mark_sent($userId, $stage, $expiryDate, $points, [
                'days_left' => $daysLeft,
            ]);
            $sent++;
        } catch (Throwable $e) {
            error_log("reward_expiry warning failed for {$userId}: " . $e->getMessage());
        }
    }

    return $sent;
}

/**
 * Send post-expiry notifications (once per user + expiry_date).
 */
function reward_expiry_send_expired_notifications(): int
{
    $rows = reward_expiry_find_expired();
    $sent = 0;

    foreach ($rows as $row) {
        $userId     = (string)$row['user_id'];
        $expiryDate = (string)$row['expiry_date'];
        $points     = (int)$row['total_points'];

        if ($points <= 0) continue;
        if (reward_expiry_already_sent($userId, 'expired', $expiryDate)) continue;

        try {
            create_notification(
                'customer',
                $userId,
                'reward_expired',
                'Reward points expired',
                sprintf(
                    '%s Craft Points expired on %s. Keep earning new points with every delivered order!',
                    number_format($points),
                    date('d M Y', strtotime($expiryDate))
                ),
                '/portals/modules/customer/rewards.php',
                ['points' => $points, 'expiry_date' => $expiryDate]
            );

            reward_expiry_mark_sent($userId, 'expired', $expiryDate, $points);
            $sent++;
        } catch (Throwable $e) {
            error_log("reward_expiry expired-notif failed for {$userId}: " . $e->getMessage());
        }
    }

    return $sent;
}
