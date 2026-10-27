<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/razorpay.php';
require_once __DIR__ . '/../lib/influencer-service-orders.php';

send_cors_headers();
$context = require_vendor(true);
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
if (!razorpay_is_configured()) json_response(503, ['success' => false, 'message' => 'Razorpay is not configured.']);
$body = get_json_body();
$paymentId = trim((string)($body['payment_id'] ?? ''));
$providerOrderId = trim((string)($body['razorpay_order_id'] ?? ''));
$providerPaymentId = trim((string)($body['razorpay_payment_id'] ?? ''));
$signature = trim((string)($body['razorpay_signature'] ?? ''));
if (!preg_match('/^[a-f0-9-]{36}$/i', $paymentId) || $providerOrderId === '' || $providerPaymentId === ''
    || !preg_match('/^[a-f0-9]{64}$/i', $signature)) {
    json_response(422, ['success' => false, 'message' => 'Razorpay payment details are incomplete.']);
}
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
    if (!$payment || $payment['provider'] !== 'razorpay') json_response(404, ['success' => false, 'message' => 'Service payment not found.']);
    if ($payment['provider_order_id'] !== $providerOrderId || str_starts_with($providerOrderId, 'mock_service_')) {
        json_response(409, ['success' => false, 'message' => 'Razorpay order does not match this service order.']);
    }
    if (!hash_equals(razorpay_signature($providerOrderId, $providerPaymentId), strtolower($signature))) {
        json_response(400, ['success' => false, 'message' => 'Razorpay signature verification failed.']);
    }
    if ($payment['status'] === 'paid' && $payment['order_payment_status'] === 'paid') {
        $db->commit();
        json_response(200, ['success' => true, 'message' => 'Payment was already verified.', 'service_order_id' => $payment['order_id']]);
    }
    $remote = razorpay_request('GET', 'payments/' . rawurlencode($providerPaymentId));
    if (($remote['order_id'] ?? '') !== $providerOrderId || ($remote['status'] ?? '') !== 'captured'
        || (int)($remote['amount'] ?? -1) !== (int)round((float)$payment['amount'] * 100)
        || strtoupper((string)($remote['currency'] ?? '')) !== strtoupper((string)$payment['currency'])) {
        json_response(409, ['success' => false, 'message' => 'Payment is not captured or does not match this service order.']);
    }
    $result = service_order_capture($db, $payment, $providerPaymentId);
    $db->commit();
    audit_event('vendor.influencer_service_payment_verified', 'influencer_service_order', (string)$payment['order_id'], ['provider_order_id' => $providerOrderId]);
    json_response(200, [
        'success' => true,
        'message' => !empty($result['refund_queued']) ? 'Payment arrived after the reservation expired. A refund was queued.' : 'Payment verified. The Conscious India team will coordinate the collaboration.',
        'service_order_id' => $payment['order_id'], 'refund_queued' => !empty($result['refund_queued']),
    ]);
} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    throw $e;
}
