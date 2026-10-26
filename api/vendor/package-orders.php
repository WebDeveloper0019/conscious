<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/idempotency.php';
require_once __DIR__ . '/../lib/influencer-service-orders.php';

send_cors_headers();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$context = require_vendor($method === 'POST');
$principal = $context['principal'];
$vendor = $context['vendor'];
$vendorId = (string)$vendor['id'];
$db = get_db();

if ($method === 'GET') {
    $id = trim((string)($_GET['id'] ?? ''));
    $params = ['vendor' => $vendorId];
    $where = 'o.vendor_id=:vendor';
    if ($id !== '') {
        if (!preg_match('/^[a-f0-9-]{36}$/i', $id)) json_response(422, ['success' => false, 'message' => 'Invalid service order.']);
        $where .= ' AND o.id=:id';
        $params['id'] = $id;
    }
    $stmt = $db->prepare(
        'SELECT o.*,s.package_title,s.deliverables_json,s.platforms_json,s.turnaround_days,s.revisions,
                s.usage_rights,s.vendor_requirements,s.cancellation_terms,
                i.full_name AS influencer_name,i.handle AS influencer_handle,
                p.id AS payment_id,p.provider_order_id,p.status AS payment_record_status
         FROM influencer_service_orders o
         JOIN influencer_service_order_snapshots s ON s.order_id=o.id
         JOIN influencers i ON i.id=o.influencer_id
         LEFT JOIN influencer_service_payments p ON p.order_id=o.id
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
}

if ($method === 'PATCH') {
    $body = get_json_body();
    $orderId = trim((string)($body['order_id'] ?? ''));
    $action = trim((string)($body['action'] ?? ''));
    $reason = trim((string)($body['reason'] ?? ''));
    if (!preg_match('/^[a-f0-9-]{36}$/i', $orderId) || !in_array($action, ['request_cancellation', 'open_dispute'], true)
        || mb_strlen($reason) < 10 || mb_strlen($reason) > 2000) {
        json_response(422, ['success' => false, 'message' => 'Choose a valid order, action and reason of 10-2000 characters.']);
    }
    $db->beginTransaction();
    try {
        $stmt = $db->prepare('SELECT * FROM influencer_service_orders WHERE id=:id AND vendor_id=:vendor FOR UPDATE');
        $stmt->execute(['id' => $orderId, 'vendor' => $vendorId]);
        $order = $stmt->fetch();
        if (!$order) json_response(404, ['success' => false, 'message' => 'Service order not found.']);
        if (!in_array($order['status'], ['paid', 'contact_pending', 'accepted', 'scheduled', 'in_progress', 'delivered', 'revision_requested'], true)) {
            json_response(409, ['success' => false, 'message' => 'This service order cannot be changed from its current status.']);
        }
        $next = $action === 'open_dispute' ? 'disputed' : 'cancellation_requested';
        if ($action === 'open_dispute') {
            $existing = $db->prepare('SELECT id FROM influencer_service_disputes WHERE order_id=:order_id AND status="open" LIMIT 1');
            $existing->execute(['order_id' => $orderId]);
            if (!$existing->fetchColumn()) {
                $db->prepare(
                    'INSERT INTO influencer_service_disputes
                     (id,order_id,opened_by_type,opened_by_id,reason,status,created_at,updated_at)
                     VALUES (:id,:order_id,"vendor",:vendor,:reason,"open",UTC_TIMESTAMP(),UTC_TIMESTAMP())'
                )->execute(['id' => uuid_string(), 'order_id' => $orderId, 'vendor' => $vendorId, 'reason' => $reason]);
            }
        }
        $db->prepare(
            'UPDATE influencer_service_orders
             SET status=:status,cancellation_reason=:cancellation,dispute_reason=:dispute,updated_at=UTC_TIMESTAMP()
             WHERE id=:id'
        )->execute([
            'status' => $next, 'cancellation' => $action === 'request_cancellation' ? $reason : $order['cancellation_reason'],
            'dispute' => $action === 'open_dispute' ? $reason : $order['dispute_reason'], 'id' => $orderId,
        ]);
        service_order_record_history($db, $orderId, $order['status'], $next, $reason, 'vendor', $vendorId);
        admin_create_task(
            $action === 'open_dispute' ? 'influencer_service_dispute' : 'influencer_service_cancellation',
            ($action === 'open_dispute' ? 'Dispute' : 'Cancellation request') . ' for ' . $order['order_number'],
            $reason, $action === 'open_dispute' ? 'urgent' : 'high', 'influencer_service_order', $orderId,
            ['vendor_id' => $vendorId, 'influencer_id' => $order['influencer_id']]
        );
        create_notification('influencer', $order['influencer_id'], 'creator_order_attention', 'Creator collaboration needs review', $order['order_number'] . ' has a ' . str_replace('_', ' ', $next) . ' request. Admin will coordinate.', null, ['order_id' => $orderId]);
        $db->commit();
        audit_event('vendor.influencer_service_' . $action, 'influencer_service_order', $orderId, ['reason' => $reason]);
        json_response(200, ['success' => true, 'status' => $next, 'message' => 'Your request was sent to the coordination team.']);
    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        throw $e;
    }
}

if ($method !== 'POST') json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
$body = get_json_body();
$packageId = trim((string)($body['package_id'] ?? ''));
$brief = trim((string)($body['brief'] ?? ''));
$contactName = trim((string)($body['contact_name'] ?? $vendor['owner_name']));
$contactEmail = strtolower(trim((string)($body['contact_email'] ?? $vendor['email'])));
$contactMobile = preg_replace('/\D+/', '', (string)($body['contact_mobile'] ?? $vendor['mobile']));
$accepted = filter_var($body['accept_terms'] ?? false, FILTER_VALIDATE_BOOL);
if (mb_strlen($brief) < 20 || mb_strlen($brief) > 5000) {
    json_response(422, ['success' => false, 'message' => 'Campaign brief must be 20-5000 characters.']);
}
if (mb_strlen($contactName) < 2 || mb_strlen($contactName) > 140 || !is_valid_email($contactEmail)
    || ($contactMobile !== '' && !is_valid_indian_mobile($contactMobile))) {
    json_response(422, ['success' => false, 'message' => 'Enter valid campaign contact details.']);
}
if (!$accepted) json_response(422, ['success' => false, 'message' => 'Accept the package and cancellation terms before payment.']);
$targetProducts = service_order_json_list($body['target_products'] ?? [], 'target product');
$requestedDates = service_order_json_list($body['requested_dates'] ?? [], 'requested date', 10);
$idem = begin_idempotent_request('vendor.influencer_service_order', $principal, $body);

$db->beginTransaction();
try {
    $package = service_order_quote($packageId, true);
    $capacity = $db->prepare(
        'SELECT COUNT(*) FROM influencer_service_orders
         WHERE package_id=:package_id
           AND (status IN ("paid","contact_pending","accepted","scheduled","in_progress","delivered","revision_requested","disputed","cancellation_requested","cancel_pending_refund")
                OR (status="pending_payment" AND reservation_expires_at>UTC_TIMESTAMP()))'
    );
    $capacity->execute(['package_id' => $packageId]);
    if ((int)$capacity->fetchColumn() >= (int)$package['capacity']) {
        throw new DomainException('This creator package is currently at capacity.');
    }
    $orderId = uuid_string();
    $orderNumber = service_order_number();
    $db->prepare(
        'INSERT INTO influencer_service_orders
         (id,order_number,vendor_id,influencer_id,package_id,brief,contact_name,contact_email,contact_mobile,
          target_products_json,requested_dates_json,currency,base_amount,gst_rate,gst_amount,total_amount,
          influencer_earning_amount,status,payment_status,reservation_expires_at,created_at,updated_at)
         VALUES (:id,:number,:vendor,:influencer,:package,:brief,:contact_name,:contact_email,:contact_mobile,
                 :products,:dates,"INR",:base,:gst_rate,:gst,:total,:earning,"pending_payment","pending",
                 DATE_ADD(UTC_TIMESTAMP(),INTERVAL ' . INFLUENCER_SERVICE_RESERVATION_MINUTES . ' MINUTE),UTC_TIMESTAMP(),UTC_TIMESTAMP())'
    )->execute([
        'id' => $orderId, 'number' => $orderNumber, 'vendor' => $vendorId,
        'influencer' => $package['influencer_id'], 'package' => $packageId, 'brief' => $brief,
        'contact_name' => $contactName, 'contact_email' => $contactEmail,
        'contact_mobile' => $contactMobile ?: null,
        'products' => $targetProducts ? json_encode($targetProducts, JSON_UNESCAPED_UNICODE) : null,
        'dates' => $requestedDates ? json_encode($requestedDates, JSON_UNESCAPED_UNICODE) : null,
        'base' => $package['base_amount'], 'gst_rate' => $package['gst_rate'],
        'gst' => $package['gst_amount'], 'total' => $package['total_amount'],
        'earning' => $package['base_amount'],
    ]);
    $db->prepare(
        'INSERT INTO influencer_service_order_snapshots
         (order_id,package_title,package_description,deliverables_json,platforms_json,turnaround_days,revisions,
          usage_rights,vendor_requirements,tax_treatment,cancellation_terms,base_amount,gst_rate,gst_amount,total_amount,created_at)
         VALUES (:order_id,:title,:description,:deliverables,:platforms,:turnaround,:revisions,:rights,:requirements,
                 :tax,:cancellation,:base,:gst_rate,:gst,:total,UTC_TIMESTAMP())'
    )->execute([
        'order_id' => $orderId, 'title' => $package['title'], 'description' => $package['description'],
        'deliverables' => $package['deliverables_json'], 'platforms' => $package['platforms_json'],
        'turnaround' => $package['turnaround_days'], 'revisions' => $package['revisions'],
        'rights' => $package['usage_rights'], 'requirements' => $package['vendor_requirements'],
        'tax' => $package['tax_treatment'], 'cancellation' => $package['cancellation_terms'],
        'base' => $package['base_amount'], 'gst_rate' => $package['gst_rate'],
        'gst' => $package['gst_amount'], 'total' => $package['total_amount'],
    ]);
    $paymentId = uuid_string();
    $db->prepare(
        'INSERT INTO influencer_service_payments
         (id,order_id,provider,amount,currency,status,created_at,updated_at)
         VALUES (:id,:order_id,"razorpay",:amount,"INR","created",UTC_TIMESTAMP(),UTC_TIMESTAMP())'
    )->execute(['id' => $paymentId, 'order_id' => $orderId, 'amount' => $package['total_amount']]);
    service_order_record_history($db, $orderId, null, 'pending_payment', 'Vendor accepted package terms and started payment.', 'vendor', $vendorId);
    $response = [
        'success' => true,
        'message' => 'Service order reserved. Complete Razorpay payment within ' . INFLUENCER_SERVICE_RESERVATION_MINUTES . ' minutes.',
        'order' => ['id' => $orderId, 'order_number' => $orderNumber, 'status' => 'pending_payment', 'total_amount' => $package['total_amount']],
        'payment' => ['id' => $paymentId, 'provider' => 'razorpay'],
    ];
    finish_idempotent_request((int)$idem['id'], 201, $response);
    $db->commit();
    audit_event('vendor.influencer_service_order_created', 'influencer_service_order', $orderId, ['package_id' => $packageId, 'total_amount' => $package['total_amount']]);
    json_response(201, $response);
} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    $db->prepare('DELETE FROM idempotency_keys WHERE id=:id AND response_status IS NULL')->execute(['id' => $idem['id']]);
    if ($e instanceof DomainException) json_response(409, ['success' => false, 'message' => $e->getMessage()]);
    throw $e;
}
