<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
send_cors_headers();

$principal = require_principal(['vendor']);
$vendorId = $principal['id']; 

$db = get_db();
$stmt = $db->prepare('
    SELECT o.*, s.package_title, s.deliverables_json, i.full_name AS influencer_name, i.handle AS influencer_handle
    FROM service_orders o
    JOIN service_order_snapshots s ON s.order_id = o.id
    JOIN influencers i ON i.id = o.influencer_id
    WHERE o.vendor_id = :vid
    ORDER BY o.created_at DESC
');
$stmt->execute(['vid' => $vendorId]);
$orders = $stmt->fetchAll();

foreach ($orders as &$order) {
    $order['deliverables'] = json_decode($order['deliverables_json'] ?? '[]', true);
    unset($order['deliverables_json']);
    
    // Fetch milestones (status history)
    $hist = $db->prepare('SELECT status, notes, created_at FROM service_order_status_history WHERE order_id = :oid ORDER BY created_at ASC');
    $hist->execute(['oid' => $order['id']]);
    $order['history'] = $hist->fetchAll();
}
json_response(200, ['success' => true, 'orders' => $orders]);