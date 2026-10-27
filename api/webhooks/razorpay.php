<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/payment.php';
require_once __DIR__ . '/../lib/influencer-service-orders.php';

send_cors_headers();
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}
if (RAZORPAY_WEBHOOK_SECRET === '') {
    json_response(503, ['success' => false, 'message' => 'Razorpay webhook is not configured.']);
}

$raw = file_get_contents('php://input') ?: '';
if (strlen($raw) > 1048576) {
    json_response(413, ['success' => false, 'message' => 'Webhook payload is too large.']);
}
$signature = trim((string)($_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? ''));
$expected = hash_hmac('sha256', $raw, RAZORPAY_WEBHOOK_SECRET);
if ($signature === '' || !hash_equals($expected, $signature)) {
    json_response(401, ['success' => false, 'message' => 'Invalid Razorpay webhook signature.']);
}

$payload = json_decode($raw, true);
if (!is_array($payload)) {
    json_response(400, ['success' => false, 'message' => 'Invalid webhook payload.']);
}
$eventType = trim((string)($payload['event'] ?? ''));
if (!in_array($eventType, ['payment.captured', 'order.paid', 'payment.failed'], true)) {
    json_response(200, ['success' => true, 'message' => 'Event ignored.']);
}

$eventId = trim((string)($_SERVER['HTTP_X_RAZORPAY_EVENT_ID'] ?? ($payload['id'] ?? '')));
if ($eventId === '') {
    json_response(422, ['success' => false, 'message' => 'Razorpay event id is required.']);
}
$paymentEntity = is_array($payload['payload']['payment']['entity'] ?? null) ? $payload['payload']['payment']['entity'] : [];
$orderEntity = is_array($payload['payload']['order']['entity'] ?? null) ? $payload['payload']['order']['entity'] : [];
$providerOrderId = trim((string)($paymentEntity['order_id'] ?? $orderEntity['id'] ?? ''));
$providerPaymentId = trim((string)($paymentEntity['id'] ?? $eventId));
if ($providerOrderId === '') {
    json_response(422, ['success' => false, 'message' => 'Razorpay order id is missing.']);
}

$rawAmountPaise = $paymentEntity['amount'] ?? $orderEntity['amount_paid'] ?? $orderEntity['amount'] ?? null;
$amount = $rawAmountPaise === null ? null : ((float)$rawAmountPaise / 100);
$currency = strtoupper(trim((string)($paymentEntity['currency'] ?? $orderEntity['currency'] ?? 'INR')));
$db = get_db();
$db->beginTransaction();
try {
    $recordId = uuid_string();
    try {
        $db->prepare(
            'INSERT INTO payment_webhook_events
             (id,provider,provider_event_id,event_type,payload_hash,processing_status,received_at)
             VALUES (:record_id,"razorpay",:provider_event,:event_type,:payload_hash,"processing",UTC_TIMESTAMP())'
        )->execute([
            'record_id' => $recordId, 'provider_event' => $eventId, 'event_type' => $eventType,
            'payload_hash' => hash('sha256', $raw),
        ]);
    } catch (PDOException $e) {
        if ((string)$e->getCode() === '23000') {
            $db->rollBack();
            json_response(200, ['success' => true, 'message' => 'Webhook already processed.']);
        }
        throw $e;
    }

    $attemptStmt = $db->prepare(
        'SELECT p.*,o.payment_status,o.status AS order_status
         FROM payment_attempts p JOIN orders o ON o.id=p.order_id
         WHERE p.provider="online" AND p.provider_order_id=:provider_order FOR UPDATE'
    );
    $attemptStmt->execute(['provider_order' => $providerOrderId]);
    $attempt = $attemptStmt->fetch();
    if (!$attempt) {
        $serviceStmt = $db->prepare(
            'SELECT p.*,o.vendor_id,o.influencer_id,o.package_id,o.order_number,
                    o.status AS order_status,o.payment_status AS order_payment_status,o.reservation_expires_at
             FROM influencer_service_payments p
             JOIN influencer_service_orders o ON o.id=p.order_id
             WHERE p.provider="razorpay" AND p.provider_order_id=:provider_order FOR UPDATE'
        );
        $serviceStmt->execute(['provider_order' => $providerOrderId]);
        $servicePayment = $serviceStmt->fetch();
        if (!$servicePayment) {
            throw new RuntimeException('Razorpay order does not belong to a local payment attempt.');
        }
        if ($amount !== null && $amount > 0 && abs((float)$servicePayment['amount'] - $amount) > 0.01) {
            throw new RuntimeException('Razorpay payment amount does not match the service order.');
        }
        if ($currency !== $servicePayment['currency']) {
            throw new RuntimeException('Razorpay payment currency does not match the service order.');
        }
        if ($eventType === 'payment.failed') {
            $reason = trim((string)($paymentEntity['error_description'] ?? $paymentEntity['error_reason'] ?? 'Razorpay payment failed.'));
            service_order_fail_payment($db, $servicePayment, mb_substr($reason, 0, 500));
        } else {
            service_order_capture($db, $servicePayment, $providerPaymentId);
        }
        $db->prepare(
            'UPDATE payment_webhook_events
             SET processing_status="processed",processed_at=UTC_TIMESTAMP()
             WHERE id=:processed_event'
        )->execute(['processed_event' => $recordId]);
        audit_event('razorpay.service_webhook_processed', 'influencer_service_order', (string)$servicePayment['order_id'], [
            'event_id' => $eventId, 'type' => $eventType, 'provider_order_id' => $providerOrderId,
        ], ['type' => 'system', 'id' => '00000000-0000-0000-0000-000000000000']);
        $db->commit();
        json_response(200, ['success' => true, 'message' => 'Razorpay service-payment webhook processed.']);
    }
    if ($amount !== null && $amount > 0 && abs((float)$attempt['amount'] - $amount) > 0.01) {
        throw new RuntimeException('Razorpay payment amount does not match the checkout.');
    }
    if ($currency !== $attempt['currency']) {
        throw new RuntimeException('Razorpay payment currency does not match the checkout.');
    }

    if ($eventType === 'payment.failed') {
        $reason = trim((string)($paymentEntity['error_description'] ?? $paymentEntity['error_reason'] ?? 'Razorpay payment failed.'));
        fail_online_order((int)$attempt['order_id'], mb_substr($reason, 0, 500));
    } else {
        confirm_online_order((int)$attempt['order_id'], $providerPaymentId);
    }
    $db->prepare(
        'UPDATE payment_webhook_events
         SET processing_status="processed",processed_at=UTC_TIMESTAMP()
         WHERE id=:processed_event'
    )->execute(['processed_event' => $recordId]);
    audit_event('razorpay.webhook_processed', 'order', (string)$attempt['order_id'], [
        'event_id' => $eventId, 'type' => $eventType, 'provider_order_id' => $providerOrderId,
    ], ['type' => 'system', 'id' => '00000000-0000-0000-0000-000000000000']);
    $db->commit();
    json_response(200, ['success' => true, 'message' => 'Razorpay webhook processed.']);
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    throw $e;
}
