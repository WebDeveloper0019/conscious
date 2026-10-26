<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';

$webhookBody = file_get_contents('php://input');
$webhookSignature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';
$webhookSecret = getenv('RAZORPAY_WEBHOOK_SECRET') ?: 'your_webhook_secret_here';

if (APP_ENV !== 'local') {
    $expectedSignature = hash_hmac('sha256', $webhookBody, $webhookSecret);
    if (!hash_equals($expectedSignature, $webhookSignature)) {
        http_response_code(400); exit('Invalid signature');
    }
}

$data = json_decode($webhookBody, true);
if (!$data || !isset($data['event'])) {
    http_response_code(400); exit('Invalid payload');
}

$db = get_db();

if ($data['event'] === 'payment.captured') {
    $paymentEntity = $data['payload']['payment']['entity'];
    $razorpayOrderId = $paymentEntity['order_id'];
    $razorpayPaymentId = $paymentEntity['id'];

    $db->beginTransaction();
    try {
        $stmt = $db->prepare('SELECT id, order_id, status FROM service_payments WHERE razorpay_order_id = :rzp FOR UPDATE');
        $stmt->execute(['rzp' => $razorpayOrderId]);
        $payment = $stmt->fetch();

        if ($payment && $payment['status'] !== 'captured') {
            $db->prepare('UPDATE service_payments SET status = "captured", razorpay_payment_id = :pid WHERE id = :id')
               ->execute(['pid' => $razorpayPaymentId, 'id' => $payment['id']]);

            $db->prepare('UPDATE service_orders SET status = "paid" WHERE id = :oid')
               ->execute(['oid' => $payment['order_id']]);

            $db->prepare('INSERT INTO service_order_status_history (id, order_id, status, notes, changed_by_type) VALUES (:id, :oid, "paid", "Razorpay Webhook Captured", "system")')
               ->execute(['id' => uuid_string(), 'oid' => $payment['order_id']]);

            $infId = $db->query("SELECT influencer_id FROM service_orders WHERE id = '{$payment['order_id']}'")->fetchColumn();
            $db->prepare('INSERT INTO influencer_notifications (id, influencer_id, title, message) VALUES (:id, :inf, "New Paid Order", "A vendor purchased your package.")')
               ->execute(['id' => uuid_string(), 'inf' => $infId]);

            $db->commit();
        } else {
            $db->commit();
        }
    } catch (Throwable $e) {
        $db->rollBack();
        http_response_code(500); exit('Database error');
    }
}

http_response_code(200);
echo 'Webhook processed';