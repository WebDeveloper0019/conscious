<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/admin.php';
require_once __DIR__ . '/../lib/influencer-service-orders.php';

send_cors_headers();
$context = require_admin('influencer_orders.manage');
$adminId = (string)$context['admin']['id'];
$db = get_db();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $status = trim((string)($_GET['status'] ?? ''));
    $params = [];
    $where = '1=1';
    if ($status !== '') {
        $where .= ' AND o.status=:status';
        $params['status'] = $status;
    }
    $stmt = $db->prepare(
        'SELECT o.*,s.package_title,s.deliverables_json,s.platforms_json,s.cancellation_terms,
                v.display_name AS vendor_name,v.owner_name AS vendor_contact,v.email AS vendor_account_email,v.mobile AS vendor_account_mobile,
                i.full_name AS influencer_name,i.handle AS influencer_handle,i.email AS influencer_email,i.mobile AS influencer_mobile,
                p.id AS payment_id,p.provider_order_id,p.provider_payment_id,p.status AS payment_record_status
         FROM influencer_service_orders o
         JOIN influencer_service_order_snapshots s ON s.order_id=o.id
         JOIN vendors v ON v.id=o.vendor_id
         JOIN influencers i ON i.id=o.influencer_id
         LEFT JOIN influencer_service_payments p ON p.order_id=o.id
         WHERE ' . $where . '
         ORDER BY o.created_at DESC LIMIT 300'
    );
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
    foreach ($orders as &$order) {
        foreach (['deliverables_json', 'platforms_json', 'target_products_json', 'requested_dates_json'] as $field) {
            $order[$field] = json_decode((string)($order[$field] ?? ''), true) ?: [];
        }
    }
    unset($order);
    json_response(200, ['success' => true, 'orders' => $orders]);
}

if ($method !== 'PATCH') json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
$body = get_json_body();
$orderId = trim((string)($body['order_id'] ?? ''));
$requestedStatus = trim((string)($body['status'] ?? ''));
$note = trim((string)($body['note'] ?? ''));
if (!preg_match('/^[a-f0-9-]{36}$/i', $orderId) || mb_strlen($note) > 1000) {
    json_response(422, ['success' => false, 'message' => 'Choose a valid service order and note.']);
}
$transitions = [
    'paid' => ['contact_pending', 'accepted', 'cancelled', 'disputed'],
    'contact_pending' => ['accepted', 'scheduled', 'cancelled', 'disputed'],
    'accepted' => ['scheduled', 'in_progress', 'cancelled', 'disputed'],
    'scheduled' => ['in_progress', 'cancelled', 'disputed'],
    'in_progress' => ['delivered', 'cancelled', 'disputed'],
    'delivered' => ['revision_requested', 'completed', 'disputed'],
    'revision_requested' => ['in_progress', 'delivered', 'completed', 'disputed'],
    'cancellation_requested' => ['cancelled', 'in_progress', 'disputed'],
    'disputed' => ['in_progress', 'delivered', 'completed', 'cancelled'],
];
if (!in_array($requestedStatus, ['contact_pending', 'accepted', 'scheduled', 'in_progress', 'delivered', 'revision_requested', 'completed', 'cancelled', 'disputed'], true)) {
    json_response(422, ['success' => false, 'message' => 'Unsupported service-order status.']);
}
if (in_array($requestedStatus, ['cancelled', 'disputed', 'revision_requested'], true) && mb_strlen($note) < 10) {
    json_response(422, ['success' => false, 'message' => 'Provide a reason of at least 10 characters.']);
}

$db->beginTransaction();
try {
    $stmt = $db->prepare('SELECT * FROM influencer_service_orders WHERE id=:id FOR UPDATE');
    $stmt->execute(['id' => $orderId]);
    $order = $stmt->fetch();
    if (!$order) json_response(404, ['success' => false, 'message' => 'Service order not found.']);
    $from = (string)$order['status'];
    if (!isset($transitions[$from]) || !in_array($requestedStatus, $transitions[$from], true)) {
        json_response(409, ['success' => false, 'message' => 'This status change is not allowed from ' . str_replace('_', ' ', $from) . '.']);
    }
    $savedStatus = $requestedStatus;
    $paymentStatus = (string)$order['payment_status'];
    if ($requestedStatus === 'completed') {
        service_order_complete_earning($db, $order, $adminId);
    } elseif ($requestedStatus === 'cancelled') {
        if ($paymentStatus === 'paid') {
            service_order_enqueue_refund($db, $orderId, $note, 'admin-cancel-' . $orderId);
            $savedStatus = 'cancel_pending_refund';
            $paymentStatus = 'refund_pending';
        } else {
            $paymentStatus = 'cancelled';
        }
    } elseif ($requestedStatus === 'disputed') {
        $existing = $db->prepare('SELECT id FROM influencer_service_disputes WHERE order_id=:order_id AND status="open" LIMIT 1');
        $existing->execute(['order_id' => $orderId]);
        if (!$existing->fetchColumn()) {
            $db->prepare(
                'INSERT INTO influencer_service_disputes
                 (id,order_id,opened_by_type,opened_by_id,reason,status,created_at,updated_at)
                 VALUES (:id,:order_id,"admin",:admin,:reason,"open",UTC_TIMESTAMP(),UTC_TIMESTAMP())'
            )->execute(['id' => uuid_string(), 'order_id' => $orderId, 'admin' => $adminId, 'reason' => $note]);
        }
        admin_create_task('influencer_service_dispute', 'Resolve disputed creator order ' . $order['order_number'], $note, 'urgent', 'influencer_service_order', $orderId);
    }
    $db->prepare(
        'UPDATE influencer_service_orders
         SET status=:status,payment_status=:payment_status,
             cancellation_reason=:cancellation_reason,dispute_reason=:dispute_reason,
             completed_at=:completed_at,cancelled_at=:cancelled_at,updated_at=UTC_TIMESTAMP()
         WHERE id=:id'
    )->execute([
        'status' => $savedStatus,
        'payment_status' => $paymentStatus,
        'cancellation_reason' => $requestedStatus === 'cancelled' ? $note : $order['cancellation_reason'],
        'dispute_reason' => $requestedStatus === 'disputed' ? $note : $order['dispute_reason'],
        'completed_at' => $requestedStatus === 'completed' ? gmdate('Y-m-d H:i:s') : $order['completed_at'],
        'cancelled_at' => $savedStatus === 'cancelled' ? gmdate('Y-m-d H:i:s') : $order['cancelled_at'],
        'id' => $orderId,
    ]);
    service_order_record_history($db, $orderId, $from, $savedStatus, $note ?: 'Admin updated the collaboration status.', 'admin', $adminId);
    create_notification('vendor', $order['vendor_id'], 'creator_order_status', 'Creator collaboration updated', $order['order_number'] . ' is now ' . str_replace('_', ' ', $savedStatus) . '.', null, ['order_id' => $orderId]);
    create_notification('influencer', $order['influencer_id'], 'creator_order_status', 'Creator collaboration updated', $order['order_number'] . ' is now ' . str_replace('_', ' ', $savedStatus) . '.', null, ['order_id' => $orderId]);
    $db->commit();
    audit_event('admin.influencer_service_order_status', 'influencer_service_order', $orderId, ['from' => $from, 'to' => $savedStatus, 'note' => $note]);
    json_response(200, ['success' => true, 'status' => $savedStatus, 'payment_status' => $paymentStatus, 'message' => 'Service order updated.']);
} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    throw $e;
}
