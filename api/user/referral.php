<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/referral.php';
send_cors_headers();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

$auth   = require_auth();
$userId = auth_user_id($auth);
$db     = get_db();

try {
    $stmt = $db->prepare('SELECT id,first_name,last_name,email FROM users WHERE id=:u LIMIT 1');
    $stmt->execute(['u' => $userId]);
    $user = $stmt->fetch();
    if (!$user) json_response(404, ['success' => false, 'message' => 'Account not found.']);

    $code = referral_code_for($userId);
    $link = rtrim(APP_URL, '/') . '/signup.html?ref=' . urlencode($code);

    // ── Stats ──────────────────────────────────────────────
    $stmt = $db->prepare(
        'SELECT
            COUNT(*)                                              AS total,
            SUM(status="completed")                               AS completed,
            SUM(status="pending")                                 AS pending,
            COALESCE(SUM(CASE WHEN status="completed" THEN reward_points ELSE 0 END),0) AS earned_points
         FROM referrals WHERE referrer_id=:u'
    );
    $stmt->execute(['u' => $userId]);
    $s = $stmt->fetch() ?: [];

    // Total wallet-equivalent from the ledger (only referral entries for clarity)
    $stmt = $db->prepare(
        "SELECT COALESCE(SUM(points),0) FROM customer_reward_ledger
         WHERE user_id=:u AND entry_type=:et AND status='active'"
    );
    $stmt->execute(['u' => $userId, 'et' => REFERRAL_ENTRY_TYPE]);
    $earnedFromLedger = (int)$stmt->fetchColumn();

    // ── Referred friends ───────────────────────────────────
    $stmt = $db->prepare(
        'SELECT r.status, r.reward_points, r.created_at, r.completed_at,
                u.first_name, u.last_name, u.created_at AS joined_at
         FROM referrals r
         JOIN users u ON u.id = r.referee_id
         WHERE r.referrer_id = :u
         ORDER BY r.created_at DESC
         LIMIT 50'
    );
    $stmt->execute(['u' => $userId]);

    $friends = [];
    foreach ($stmt->fetchAll() as $row) {
        $initial   = strtoupper(mb_substr((string)$row['first_name'], 0, 1));
        $lastName  = (string)$row['last_name'];
        $masked    = $initial . '. ' . ($lastName !== '' ? strtoupper(mb_substr($lastName, 0, 1)) . '.' : '');

        $friends[] = [
            'name'        => trim($masked),
            'status'      => (string)$row['status'],
            'reward'      => (int)$row['reward_points'],
            'joinedAt'    => $row['joined_at'],
            'completedAt' => $row['completed_at'],
            'createdAt'   => $row['created_at'],
        ];
    }

    json_response(200, [
        'success' => true,
        'code'    => $code,
        'link'    => $link,
        'stats'   => [
            'totalReferred'     => (int)($s['total']   ?? 0),
            'completed'         => (int)($s['completed'] ?? 0),
            'pending'           => (int)($s['pending']   ?? 0),
            'totalEarned'       => $earnedFromLedger,
            'rewardPerReferral' => REFERRAL_REWARD_POINTS,
        ],
        'friends' => $friends,
        'empty'   => ['friends' => $friends === []],
    ]);
} catch (Throwable $e) {
    error_log('Referral endpoint failed for user ' . $userId . ': ' . $e->getMessage());
    json_response(500, ['success' => false, 'message' => 'Unable to load referral data right now.']);
}