<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';

send_cors_headers();
$principal = require_principal(['customer']);
$db = get_db();
$userId = $principal['id'];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    header('Allow: GET');
    json_response(405, [
        'success' => false,
        'message' => 'Reward points can currently be viewed here. Earning and redemption are processed by the order workflow.',
    ]);
}

$balanceStmt = $db->prepare(
    "SELECT COALESCE(SUM(points),0)
     FROM customer_reward_ledger
     WHERE user_id=:user AND status='active'
       AND (points < 0 OR expires_at IS NULL OR expires_at > UTC_TIMESTAMP())"
);
$balanceStmt->execute(['user' => $userId]);
$balance = max(0, (int)$balanceStmt->fetchColumn());

$stmt = $db->prepare(
    'SELECT id,points,entry_type,reference_type,reference_id,description,expires_at,status,created_at
     FROM customer_reward_ledger
     WHERE user_id=:user
     ORDER BY created_at DESC LIMIT 100'
);
$stmt->execute(['user' => $userId]);

$history = [];
foreach ($stmt->fetchAll() as $row) {
    $status = $row['status'];
    if ($status === 'active' && $row['expires_at'] !== null && strtotime((string)$row['expires_at']) <= time()) {
        $status = 'expired';
    }
    $history[] = [
        'id' => $row['id'],
        'points' => (int)$row['points'],
        'entryType' => $row['entry_type'],
        'referenceType' => $row['reference_type'],
        'referenceId' => $row['reference_id'],
        'description' => $row['description'],
        'expiresAt' => $row['expires_at'],
        'status' => $status,
        'createdAt' => $row['created_at'],
    ];
}

json_response(200, [
    'success' => true,
    'balance' => $balance,
    'points' => $balance,
    'transactions' => $history,
]);
