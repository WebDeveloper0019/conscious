<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
require_once __DIR__.'/../lib/notifications.php';
send_cors_headers();

// Ensure the user is an admin
require_admin('orders.oversee');
$db = get_db();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// 1. GET: List all service orders
if ($method === 'GET') {
    $stmt = $db->prepare("
        SELECT o.*, v.display_name AS vendor_name, i.full_name AS influencer_name 
        FROM service_orders o
        JOIN vendors v ON v.id = o.vendor_id
        JOIN influencers i ON i.id = o.influencer_id
        ORDER BY o.created_at DESC
    ");
    $stmt->execute();
    json_response(200, ['success' => true, 'orders' => $stmt->fetchAll()]);
}

// 2. POST: Update order status (Workflow)
if ($method === 'POST') {
    $body = get_json_body();
    $orderId = trim((string)($body['order_id'] ?? ''));
    $newStatus = trim((string)($body['status'] ?? ''));
    $note = trim((string)($body['note'] ?? '')); // Coordination notes

    $allowedTransitions = [
        'paid' => ['contact_pending', 'cancelled'],
        'contact_pending' => ['scheduled', 'cancelled'],
        'scheduled' => ['in_progress', 'cancelled'],
        'in_progress' => ['delivered', 'disputed'],
        'delivered' => ['completed', 'disputed'],
    ];

    if (empty($orderId)) json_response(422, ['success' => false, 'message' => 'Order ID required.']);

    $db->beginTransaction();
    try {
        // Lock the row to prevent double updates
        $stmt = $db->prepare("SELECT status, vendor_id, influencer_id, order_number FROM service_orders WHERE id = :id FOR UPDATE");
        $stmt->execute(['id' => $orderId]);
        $order = $stmt->fetch();

        if (!$order) throw new Exception('Order not found.');
        
        $currentStatus = $order['status'];
        
        // Validate transition
        if (!isset($allowedTransitions[$currentStatus]) || !in_array($newStatus, $allowedTransitions[$currentStatus], true)) {
            throw new Exception("Cannot transition from {$currentStatus} to {$newStatus}.");
        }

        // Update order
        $db->prepare("UPDATE service_orders SET status = :status, updated_at = UTC_TIMESTAMP() WHERE id = :id")
           ->execute(['status' => $newStatus, 'id' => $orderId]);

        // Record in history
        $db->prepare("INSERT INTO order_status_history (order_id, from_status, to_status, changed_by, notes, created_at) 
                      VALUES (:oid, :from, :to, :admin, :note, UTC_TIMESTAMP())")
           ->execute([
               'oid' => $orderId, 
               'from' => $currentStatus, 
               'to' => $newStatus, 
               'admin' => $_SESSION['admin_id'] ?? 'system',
               'note' => $note
           ]);

        // Create Notifications
        $msg = "Order #{$order['order_number']} status updated to " . str_replace('_', ' ', $newStatus);
        create_notification('vendor', $order['vendor_id'], 'order_status', 'Order Update', $msg);
        create_notification('influencer', $order['influencer_id'], 'order_status', 'Order Update', $msg);

        $db->commit();
        json_response(200, ['success' => true, 'message' => 'Order status updated successfully.']);

    } catch (Throwable $e) {
        $db->rollBack();
        json_response(400, ['success' => false, 'message' => $e->getMessage()]);
    }
}

json_response(405, ['success' => false, 'message' => 'Method not allowed.']);