<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
require_once __DIR__.'/../lib/notifications.php';
send_cors_headers();

// Ensure the user is a logged-in vendor
$context = require_vendor(true);
$vendorId = $context['vendor']['id'];
$db = get_db();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

// Fetch the vendor's service orders
$stmt = $db->prepare("
    SELECT 
        o.id, o.order_number, o.brief, o.base_amount, o.gst_amount, o.total_amount, 
        o.status, o.created_at,
        s.package_title, s.deliverables_json, s.platforms_json, s.turnaround_days, 
        s.usage_rights, s.vendor_requirements, s.cancellation_terms,
        i.full_name AS influencer_name, i.handle AS influencer_handle
    FROM service_orders o
    JOIN service_order_snapshots s ON s.order_id = o.id
    JOIN influencers i ON i.id = o.influencer_id
    WHERE o.vendor_id = :vendor_id
    ORDER BY o.created_at DESC
");

$stmt->execute(['vendor_id' => $vendorId]);
$orders = $stmt->fetchAll();

// Decode JSON fields for the frontend
foreach ($orders as &$order) {
    $order['deliverables_json'] = json_decode((string)$order['deliverables_json'], true) ?: [];
    $order['platforms_json'] = json_decode((string)$order['platforms_json'], true) ?: [];
}

json_response(200, ['success' => true, 'orders' => $orders]);