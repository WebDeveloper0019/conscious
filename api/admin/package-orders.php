<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
send_cors_headers();

$db = get_db();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $stmt = $db->prepare('
        SELECT o.*, s.package_title, i.full_name AS influencer_name, i.handle AS influencer_handle, v.display_name AS vendor_name
        FROM service_orders o
        JOIN service_order_snapshots s ON s.order_id = o.id
        JOIN influencers i ON i.id = o.influencer_id
        LEFT JOIN vendors v ON v.id = o.vendor_id
        WHERE o.status IN ("paid", "in_progress", "contact_pending", "scheduled", "delivered")
        ORDER BY o.created_at DESC
    ');
    $stmt->execute();
    json_response(200, ['success' => true, 'orders' => $stmt->fetchAll()]);
}

if ($method === 'POST') {
    $body = get_json_body();
    $orderId = trim($body['id'] ?? '');
    $newStatus = trim($body['status'] ?? ''); 
    $notes = trim($body['notes'] ?? 'Admin updated status');

    $allowedStatuses = ['contact_pending', 'scheduled', 'in_progress', 'delivered', 'completed', 'cancelled'];
    
    if (!$orderId || !in_array($newStatus, $allowedStatuses)) {
        json_response(422, ['success' => false, 'message' => 'Invalid status request.']);
    }

    $db->beginTransaction();
    try {
        $check = $db->prepare('SELECT status, influencer_id, base_amount FROM service_orders WHERE id = :id FOR UPDATE');
        $check->execute(['id' => $orderId]);
        $order = $check->fetch();

        if (!$order || $order['status'] === 'completed' || $order['status'] === 'cancelled') {
            throw new Exception('Order is already processed or closed.');
        }

        // 1. If COMPLETED, pay the creator!
        if ($newStatus === 'completed') {
            $chk = $db->prepare('SELECT id FROM influencer_ledger_entries WHERE reference_type = "service_order" AND reference_id = :oid LIMIT 1');
            $chk->execute(['oid' => $orderId]);
            
            if (!$chk->fetch()) {
                $ledgerId = uuid_string();
                $db->prepare('INSERT INTO influencer_ledger_entries (id, influencer_id, amount, status, reference_type, reference_id, created_at) VALUES (:id, :inf, :amt, "eligible", "service_order", :oid, UTC_TIMESTAMP())')
                   ->execute(['id' => $ledgerId, 'inf' => $order['influencer_id'], 'amt' => $order['base_amount'], 'oid' => $orderId]);
                   
                $db->prepare('INSERT INTO service_earnings_links (id, order_id, influencer_id, ledger_entry_id, commission_amount, created_at) VALUES (:id, :oid, :inf, :lid, :amt, UTC_TIMESTAMP())')
                   ->execute(['id' => uuid_string(), 'oid' => $orderId, 'inf' => $order['influencer_id'], 'lid' => $ledgerId, 'amt' => $order['base_amount']]);
            }
        } 
        // 2. If CANCELLED, handle refund
        else if ($newStatus === 'cancelled') {
            $db->prepare('INSERT INTO service_refunds_disputes (id, order_id, type, amount, reason, status) VALUES (:id, :oid, "refund", :amt, :reason, "processed")')
               ->execute(['id' => uuid_string(), 'oid' => $orderId, 'amt' => $order['base_amount'], 'reason' => $notes]);
        }

        // 3. Update Status
        $db->prepare('UPDATE service_orders SET status = :status, updated_at = UTC_TIMESTAMP() WHERE id = :id')
           ->execute(['status' => $newStatus, 'id' => $orderId]);
           
        // 4. Log History with Notes (Yeh line aapki file mein missing thi)
        $db->prepare('INSERT INTO service_order_status_history (id, order_id, status, notes, changed_by_type) VALUES (:id, :oid, :status, :notes, "admin")')
           ->execute(['id' => uuid_string(), 'oid' => $orderId, 'status' => $newStatus, 'notes' => $notes]);

        $db->commit();
        json_response(200, ['success' => true, 'message' => "Order status updated to " . str_replace('_', ' ', $newStatus)]);
    } catch (Throwable $e) {
        $db->rollBack();
        json_response(400, ['success' => false, 'message' => 'An error occurred while processing the order.']);
    }
}