<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
send_cors_headers();
$principal = require_principal(['influencer']);
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
$id = trim((string)($_GET['id'] ?? ''));
$params = ['influencer' => $principal['id']];
$where = 'o.influencer_id=:influencer AND o.payment_status IN ("paid","refund_pending","refunded")';
if ($id !== '') {
    if (!preg_match('/^[a-f0-9-]{36}$/i', $id)) json_response(422, ['success' => false, 'message' => 'Invalid service order.']);
    $where .= ' AND o.id=:id';
    $params['id'] = $id;
}
$stmt = get_db()->prepare(
    'SELECT o.id,o.order_number,o.brief,o.target_products_json,o.requested_dates_json,o.currency,o.base_amount,
            o.gst_amount,o.total_amount,o.influencer_earning_amount,o.status,o.payment_status,o.paid_at,o.completed_at,
            o.created_at,s.package_title,s.deliverables_json,s.platforms_json,s.turnaround_days,s.revisions,
            s.usage_rights,s.vendor_requirements,s.cancellation_terms,v.display_name AS vendor_name
     FROM influencer_service_orders o
     JOIN influencer_service_order_snapshots s ON s.order_id=o.id
     JOIN vendors v ON v.id=o.vendor_id
     WHERE ' . $where . '
     ORDER BY o.created_at DESC LIMIT 100'
);
$stmt->execute($params);
$orders = $stmt->fetchAll();
foreach ($orders as &$order) {
    foreach (['deliverables_json', 'platforms_json', 'target_products_json', 'requested_dates_json'] as $field) {
        $order[$field] = json_decode((string)($order[$field] ?? ''), true) ?: [];
    }
}
unset($order);
if ($id !== '' && !$orders) json_response(404, ['success' => false, 'message' => 'Service order not found.']);
json_response(200, ['success' => true, 'orders' => $orders]);
