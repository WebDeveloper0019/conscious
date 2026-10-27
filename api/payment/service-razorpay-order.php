<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/razorpay.php';

send_cors_headers();
$context = require_vendor(true);
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
$paymentId = trim((string)(get_json_body()['payment_id'] ?? ''));
if (!preg_match('/^[a-f0-9-]{36}$/i', $paymentId)) json_response(422, ['success' => false, 'message' => 'Valid service payment id is required.']);
$db = get_db();
$db->beginTransaction();
try {
    $stmt = $db->prepare(
        'SELECT p.*,o.vendor_id,o.order_number,o.status AS order_status,o.payment_status AS order_payment_status,o.reservation_expires_at
         FROM influencer_service_payments p
         JOIN influencer_service_orders o ON o.id=p.order_id
         WHERE p.id=:id AND o.vendor_id=:vendor FOR UPDATE'
    );
    $stmt->execute(['id' => $paymentId, 'vendor' => $context['vendor']['id']]);
    $payment = $stmt->fetch();
    if (!$payment) json_response(404, ['success' => false, 'message' => 'Service payment not found.']);
    if ($payment['status'] !== 'created' || $payment['order_status'] !== 'pending_payment' || $payment['order_payment_status'] !== 'pending') {
        json_response(409, ['success' => false, 'message' => 'This service order is not awaiting payment.']);
    }
    if (strtotime((string)$payment['reservation_expires_at']) <= time()) {
        json_response(409, ['success' => false, 'message' => 'The package reservation expired. Start a new order.']);
    }
    $providerOrderId = trim((string)($payment['provider_order_id'] ?? ''));
    $mock = APP_ENV === 'local' && !razorpay_is_configured();
    if ($providerOrderId !== '') {
        if (str_starts_with($providerOrderId, 'mock_service_') !== $mock) {
            json_response(409, ['success' => false, 'message' => 'This payment was created for a different gateway configuration.']);
        }
    } elseif ($mock) {
        $providerOrderId = 'mock_service_' . str_replace('-', '', $paymentId);
    } else {
        if (!razorpay_is_configured()) json_response(503, ['success' => false, 'message' => 'Razorpay is not configured.']);
        $remote = razorpay_request('POST', 'orders', [
            'amount' => (int)round((float)$payment['amount'] * 100),
            'currency' => $payment['currency'],
            'receipt' => $payment['order_number'],
            'notes' => ['conscious_service_order_id' => (string)$payment['order_id']],
        ]);
        $providerOrderId = trim((string)($remote['id'] ?? ''));
        if ($providerOrderId === '') throw new RuntimeException('Razorpay did not return an order id.');
    }
    if (empty($payment['provider_order_id'])) {
        $db->prepare('UPDATE influencer_service_payments SET provider_order_id=:provider_order,updated_at=UTC_TIMESTAMP() WHERE id=:id')
           ->execute(['provider_order' => $providerOrderId, 'id' => $paymentId]);
    }
    $db->commit();
    json_response(200, [
        'success' => true, 'gateway' => $mock ? 'mock' : 'razorpay',
        'key_id' => $mock ? null : RAZORPAY_KEY_ID, 'order_id' => $providerOrderId,
        'amount' => (int)round((float)$payment['amount'] * 100), 'currency' => $payment['currency'],
        'payment_id' => $paymentId, 'service_order_id' => $payment['order_id'], 'order_number' => $payment['order_number'],
    ]);
} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    throw $e;
}
