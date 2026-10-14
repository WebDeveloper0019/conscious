<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/idempotency.php';
require_once __DIR__ . '/../lib/rewards.php';
require_once __DIR__ . '/../lib/reward-expiry.php';

send_cors_headers();
$principal = require_principal(['customer']);
$db        = get_db();
$userId    = $principal['id'];
$method    = $_SERVER['REQUEST_METHOD'] ?? 'GET';

function reward_expire_due(PDO $db, string $userId): void
{
    $db->prepare(
        "UPDATE customer_reward_ledger
         SET status='expired'
         WHERE user_id=:user AND status='active'
           AND points > 0 AND expires_at IS NOT NULL AND expires_at <= UTC_TIMESTAMP()"
    )->execute(['user' => $userId]);

    // Reconcile balance cache from ledger truth
    $balStmt = $db->prepare(
        "SELECT COALESCE(SUM(points),0) FROM customer_reward_ledger
         WHERE user_id = :user AND status = 'active'
           AND (points < 0 OR expires_at IS NULL OR expires_at > UTC_TIMESTAMP())"
    );
    $balStmt->execute(['user' => $userId]);
    $trueBalance = max(0, (int)$balStmt->fetchColumn());

    $db->prepare(
        "UPDATE customer_rewards SET balance_points = :bal, updated_at = UTC_TIMESTAMP()
         WHERE user_id = :user"
    )->execute(['bal' => $trueBalance, 'user' => $userId]);
}

// ────────── GET: balance + paginated history ──────────
if ($method === 'GET') {
    reward_expire_due($db, $userId);

    // Pagination input
    $page  = max(1, (int)($_GET['page']  ?? 1));
    $limit = min(100, max(1, (int)($_GET['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;

    // Optional filter: entry_type
    $typeFilter = strtolower(trim((string)($_GET['entry_type'] ?? '')));
    $allowedTypes = ['earned', 'redeemed', 'refund', 'clawback', 'expired'];

    $where = 'user_id = :user';
    $params = ['user' => $userId];

    if ($typeFilter !== '' && $typeFilter !== 'all') {
        if (!in_array($typeFilter, $allowedTypes, true)) {
            json_response(400, ['success' => false, 'message' => 'Invalid entry_type filter.']);
        }
        $where .= ' AND entry_type = :entry_type';
        $params['entry_type'] = $typeFilter;
    }

    // Balance (from cache table)
    $balStmt = $db->prepare('SELECT balance_points, lifetime_earned, lifetime_redeemed, tier FROM customer_rewards WHERE user_id = ?');
    $balStmt->execute([$userId]);
    $acct = $balStmt->fetch() ?: ['balance_points' => 0, 'lifetime_earned' => 0, 'lifetime_redeemed' => 0, 'tier' => 'bronze'];

    // Total count for pagination
    $countStmt = $db->prepare("SELECT COUNT(*) FROM customer_reward_ledger WHERE $where");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    // Page of transactions
    $stmt = $db->prepare(
        "SELECT id, points, entry_type, reference_type, reference_id,
                description, expires_at, status, created_at
         FROM customer_reward_ledger
         WHERE $where
         ORDER BY created_at DESC, id DESC
         LIMIT $limit OFFSET $offset"
    );
    $stmt->execute($params);

    $history = [];
    foreach ($stmt->fetchAll() as $row) {
        $rowStatus = $row['status'];
        if ($rowStatus === 'active' && $row['expires_at'] !== null
            && strtotime((string)$row['expires_at']) <= time() && $row['points'] > 0) {
            $rowStatus = 'expired';
        }

        $history[] = [
            'id'            => $row['id'],
            'points'        => (int)$row['points'],
            'entryType'     => $row['entry_type'],
            'referenceType' => $row['reference_type'],
            'referenceId'   => $row['reference_id'],
            'description'   => $row['description'],
            'expiresAt'     => $row['expires_at'],
            'status'        => $rowStatus,
            'createdAt'     => $row['created_at'],
        ];
    }

    $totalPages = $total > 0 ? (int)ceil($total / $limit) : 0;

    json_response(200, [
        'success'   => true,
        'balance'   => (int)$acct['balance_points'],
        'points'    => (int)$acct['balance_points'],
        'lifetime'  => [
            'earned'   => (int)$acct['lifetime_earned'],
            'redeemed' => (int)$acct['lifetime_redeemed'],
        ],
        'tier'      => $acct['tier'],
        'transactions' => $history,
        'pagination' => [
            'page'       => $page,
            'limit'      => $limit,
            'total'      => $total,
            'totalPages' => $totalPages,
            'hasNext'    => $page < $totalPages,
            'hasPrev'    => $page > 1,
        ],
    ]);
}

// ────────── POST — unchanged behaviour ──────────
if ($method !== 'POST') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

$body   = get_json_body();
$action = strtolower(trim((string)($body['action'] ?? '')));

if (!in_array($action, ['earn', 'redeem', 'expire'], true)) {
    json_response(422, ['success' => false, 'message' => 'Action must be earn, redeem or expire.']);
}

if ($action === 'earn') {
    json_response(403, ['success' => false, 'message' => 'Points are awarded automatically when your order is delivered.']);
}

if ($action === 'expire') {
    reward_expire_due($db, $userId);
    json_response(200, ['success' => true, 'message' => 'Expired reward points have been updated.']);
}

// redeem
$points  = (int)($body['points'] ?? 0);
$orderId = (int)($body['order_id'] ?? 0);

if ($points < 1 || $points > 100000) {
    json_response(422, ['success' => false, 'message' => 'Redeem between 1 and 100000 points.']);
}
if ($orderId <= 0) {
    json_response(422, ['success' => false, 'message' => 'An order_id is required to redeem points.']);
}

$orderStmt = $db->prepare('SELECT id, order_number, subtotal, user_id FROM orders WHERE id = ? AND user_id = ? LIMIT 1');
$orderStmt->execute([$orderId, $userId]);
$order = $orderStmt->fetch();
if (!$order) json_response(404, ['success' => false, 'message' => 'Order not found.']);

$idem = begin_idempotent_request('customer.rewards.redeem', $principal, ['action' => 'redeem', 'points' => $points, 'order_id' => $orderId]);

try {
    $db->beginTransaction();
    reward_expire_due($db, $userId);

    $applied = reward_redeem_for_order($userId, (int)$order['id'], $points, (float)$order['subtotal'], (string)$order['order_number']);
    $db->commit();

    $balStmt = $db->prepare('SELECT balance_points FROM customer_rewards WHERE user_id = ?');
    $balStmt->execute([$userId]);
    $newBalance = (int)$balStmt->fetchColumn();

    $response = [
        'success'         => true,
        'message'         => 'Reward points redeemed.',
        'pointsRedeemed'  => $applied,
        'discountAmount'  => (float)$applied * 1.0,
        'remainingPoints' => $newBalance,
    ];
    finish_idempotent_request((int)$idem['id'], 200, $response);
    json_response(200, $response);
} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    $response = ['success' => false, 'message' => $e->getMessage()];
    finish_idempotent_request((int)$idem['id'], 422, $response);
    json_response(422, $response);
}