<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/razorpay.php';
require_once __DIR__ . '/../lib/influencer-service-orders.php';

send_cors_headers();
if (APP_ENV !== 'local' || razorpay_is_configured()) json_response(404, ['success' => false, 'message' => 'Local demo payment is unavailable.']);
$context = require_vendor(true);
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
$paymentId = trim((string)(get_json_body()['payment_id'] ?? ''));
if (!preg_match('/^[a-f0-9-]{36}$/i', $paymentId)) json_response(422, ['success' => false, 'message' => 'Valid service payment id is required.']);
$db = get_db();
$db->beginTransaction();
try {
    $stmt = $db->prepare(
        'SELECT p.*,o.vendor_id,o.influencer_id,o.package_id,o.order_number,o.status AS order_status,
                o.payment_status AS order_payment_status,o.reservation_expires_at
         FROM influencer_service_payments p
         JOIN influencer_service_orders o ON o.id=p.order_id
         WHERE p.id=:id AND o.vendor_id=:vendor FOR UPDATE'
    );
    $stmt->execute(['id' => $paymentId, 'vendor' => $context['vendor']['id']]);
    $payment = $stmt->fetch();
    if (!$payment || !str_starts_with((string)$payment['provider_order_id'], 'mock_service_')) {
        json_response(404, ['success' => false, 'message' => 'Local demo payment not found.']);
    }
    $result = service_order_capture($db, $payment, 'mock_pay_' . bin2hex(random_bytes(8)));
    $db->commit();
    json_response(200, [
        'success' => true, 'local_demo' => true, 'service_order_id' => $payment['order_id'],
        'order_number' => $payment['order_number'], 'refund_queued' => !empty($result['refund_queued']),
        'message' => 'Local demo payment recorded. No real money was charged.',
    ]);
} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    throw $e;
}
