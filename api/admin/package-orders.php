<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
send_cors_headers();

// If you want to use your admin auth, uncomment the line below:
// require_once __DIR__.'/../lib/admin.php'; require_admin('orders.oversee');

$db = get_db();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $stmt = $db->prepare('
        SELECT o.*, p.title AS package_title, i.full_name AS influencer_name, i.handle AS influencer_handle, v.display_name AS vendor_name
        FROM package_orders o
        JOIN influencer_packages p ON p.id = o.package_id
        JOIN influencers i ON i.id = o.influencer_id
        LEFT JOIN vendors v ON v.id = o.vendor_id
        WHERE o.status IN ("paid", "in_progress")
        ORDER BY o.created_at DESC
    ');
    $stmt->execute();
    json_response(200, ['success' => true, 'orders' => $stmt->fetchAll()]);
}

if ($method === 'POST') {
    $body = get_json_body();
    $orderId = trim($body['id'] ?? '');
    $action = trim($body['action'] ?? ''); // 'complete' or 'refund'

    if (!$orderId || !in_array($action, ['complete', 'refund'])) {
        json_response(422, ['success' => false, 'message' => 'Invalid request.']);
    }

    $db->beginTransaction();
    try {
        // Idempotency Check: Ensure it's not already completed
        $check = $db->prepare('SELECT status, influencer_id, amount_inr FROM package_orders WHERE id = :id FOR UPDATE');
        $check->execute(['id' => $orderId]);
        $order = $check->fetch();

        if (!$order || $order['status'] !== 'paid') {
            throw new Exception('Order cannot be processed or is already completed.');
        }

        if ($action === 'complete') {
            $newStatus = 'completed';
            // Credit the influencer's ledger so they can withdraw the money!
            $db->prepare('INSERT INTO influencer_ledger_entries (id, influencer_id, amount, status, created_at) VALUES (:id, :inf, :amt, "eligible", UTC_TIMESTAMP())')
               ->execute(['id' => uuid_string(), 'inf' => $order['influencer_id'], 'amt' => $order['amount_inr']]);
               
        } else {
            $newStatus = 'cancelled'; // Refunded
        }

        $db->prepare('UPDATE package_orders SET status = :status, updated_at = UTC_TIMESTAMP() WHERE id = :id')
           ->execute(['status' => $newStatus, 'id' => $orderId]);

        $db->commit();
        json_response(200, ['success' => true, 'message' => "Order marked as $newStatus."]);
    } catch (Throwable $e) {
        $db->rollBack();
        json_response(400, ['success' => false, 'message' => $e->getMessage()]);
    }
}