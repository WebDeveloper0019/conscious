<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/notifications.php';
require_once __DIR__ . '/admin-notify.php';

const INFLUENCER_SERVICE_GST_RATE = 18.00;
const INFLUENCER_SERVICE_RESERVATION_MINUTES = 20;

function service_order_number(): string
{
    return 'CIS-' . gmdate('ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
}

function service_order_json_list(mixed $value, string $label, int $max = 20): array
{
    if ($value === null || $value === '') return [];
    if (!is_array($value) || !array_is_list($value) || count($value) > $max) {
        json_response(422, ['success' => false, 'message' => "Check the {$label} list."]);
    }
    $clean = [];
    foreach ($value as $item) {
        if (!is_string($item) || mb_strlen(trim($item)) < 1 || mb_strlen(trim($item)) > 180) {
            json_response(422, ['success' => false, 'message' => "Each {$label} entry must be 1-180 characters."]);
        }
        $clean[] = trim($item);
    }
    return array_values(array_unique($clean));
}

function service_order_quote(string $packageId, bool $forUpdate = false): array
{
    if (!preg_match('/^[a-f0-9-]{36}$/i', $packageId)) {
        json_response(422, ['success' => false, 'message' => 'Choose a valid creator package.']);
    }
    $sql = 'SELECT p.*,i.full_name AS influencer_name,i.handle AS influencer_handle,
                   i.status AS influencer_status,i.verification_status AS influencer_verification,i.email_verified
            FROM influencer_packages p
            JOIN influencers i ON i.id=p.influencer_id
            WHERE p.id=:id' . ($forUpdate ? ' FOR UPDATE' : '');
    $stmt = get_db()->prepare($sql);
    $stmt->execute(['id' => $packageId]);
    $package = $stmt->fetch();
    if (!$package || $package['status'] !== 'active'
        || $package['influencer_status'] !== 'active'
        || $package['influencer_verification'] !== 'approved'
        || !(int)$package['email_verified']) {
        json_response(404, ['success' => false, 'message' => 'This creator package is not available.']);
    }
    $base = round((float)$package['price_inr'], 2);
    $rate = INFLUENCER_SERVICE_GST_RATE;
    $gst = round($base * $rate / 100, 2);
    $package['base_amount'] = $base;
    $package['gst_rate'] = $rate;
    $package['gst_amount'] = $gst;
    $package['total_amount'] = round($base + $gst, 2);
    return $package;
}

function service_order_record_history(
    PDO $db,
    string $orderId,
    ?string $from,
    string $to,
    ?string $note,
    string $actorType,
    string $actorId
): void {
    $db->prepare(
        'INSERT INTO influencer_service_order_history
         (id,order_id,from_status,to_status,note,actor_type,actor_id,created_at)
         VALUES (:id,:order_id,:from_status,:to_status,:note,:actor_type,:actor_id,UTC_TIMESTAMP())'
    )->execute([
        'id' => uuid_string(), 'order_id' => $orderId, 'from_status' => $from,
        'to_status' => $to, 'note' => $note ? mb_substr($note, 0, 1000) : null,
        'actor_type' => $actorType, 'actor_id' => $actorId,
    ]);
}

function service_order_capture(PDO $db, array $payment, string $providerPaymentId): array
{
    if ($payment['status'] === 'paid' && $payment['order_payment_status'] === 'paid') {
        return ['already_processed' => true, 'order_id' => $payment['order_id']];
    }
    if ($payment['status'] !== 'created' || $payment['order_payment_status'] !== 'pending'
        || $payment['order_status'] !== 'pending_payment') {
        throw new RuntimeException('This service order is not awaiting payment.');
    }
    $orderId = (string)$payment['order_id'];
    $late = strtotime((string)$payment['reservation_expires_at']) <= time();
    $db->prepare(
        'UPDATE influencer_service_payments
         SET status="paid",provider_payment_id=:provider_payment,captured_at=UTC_TIMESTAMP(),updated_at=UTC_TIMESTAMP()
         WHERE id=:id'
    )->execute(['provider_payment' => $providerPaymentId, 'id' => $payment['id']]);

    if ($late) {
        $db->prepare(
            'UPDATE influencer_service_orders
             SET status="cancel_pending_refund",payment_status="refund_pending",updated_at=UTC_TIMESTAMP()
             WHERE id=:id'
        )->execute(['id' => $orderId]);
        service_order_record_history($db, $orderId, 'pending_payment', 'cancel_pending_refund', 'Payment arrived after the capacity reservation expired.', 'system', '00000000-0000-0000-0000-000000000000');
        service_order_enqueue_refund($db, $orderId, 'Late payment after reservation expiry', 'late-payment-' . $orderId);
        admin_create_task('service_payment_reconciliation', 'Late creator-package payment requires review', 'A Razorpay payment arrived after its capacity reservation expired. A full refund was queued.', 'urgent', 'influencer_service_order', $orderId, ['payment_id' => $payment['id']]);
        return ['already_processed' => false, 'order_id' => $orderId, 'refund_queued' => true];
    }

    $db->prepare(
        'UPDATE influencer_service_orders
         SET status="paid",payment_status="paid",paid_at=UTC_TIMESTAMP(),updated_at=UTC_TIMESTAMP()
         WHERE id=:id'
    )->execute(['id' => $orderId]);
    service_order_record_history($db, $orderId, 'pending_payment', 'paid', 'Razorpay payment verified.', 'system', '00000000-0000-0000-0000-000000000000');
    admin_create_task(
        'influencer_service_coordination',
        'Coordinate creator package order ' . $payment['order_number'],
        'Contact the vendor and creator, confirm the brief and agree the working schedule.',
        'high',
        'influencer_service_order',
        $orderId,
        ['vendor_id' => $payment['vendor_id'], 'influencer_id' => $payment['influencer_id'], 'package_id' => $payment['package_id']]
    );
    create_notification('vendor', $payment['vendor_id'], 'creator_package_paid', 'Creator package payment confirmed', 'Payment for ' . $payment['order_number'] . ' was verified. Our team will coordinate the next steps.', null, ['order_id' => $orderId]);
    create_notification('influencer', $payment['influencer_id'], 'creator_package_order', 'New paid creator package order', 'A vendor purchased your package. The Conscious India team will contact both parties.', null, ['order_id' => $orderId]);
    return ['already_processed' => false, 'order_id' => $orderId, 'refund_queued' => false];
}

function service_order_fail_payment(PDO $db, array $payment, string $message): void
{
    if ($payment['status'] !== 'created' || $payment['order_payment_status'] !== 'pending') return;
    $db->prepare('UPDATE influencer_service_payments SET status="failed",failure_message=:message,updated_at=UTC_TIMESTAMP() WHERE id=:id')
       ->execute(['message' => mb_substr($message, 0, 1000), 'id' => $payment['id']]);
    $db->prepare('UPDATE influencer_service_orders SET status="payment_failed",payment_status="failed",updated_at=UTC_TIMESTAMP() WHERE id=:id')
       ->execute(['id' => $payment['order_id']]);
    service_order_record_history($db, (string)$payment['order_id'], 'pending_payment', 'payment_failed', $message, 'system', '00000000-0000-0000-0000-000000000000');
}

function service_order_complete_earning(PDO $db, array $order, string $adminId): void
{
    $exists = $db->prepare('SELECT ledger_entry_id FROM influencer_service_earnings WHERE order_id=:order_id');
    $exists->execute(['order_id' => $order['id']]);
    if ($exists->fetchColumn()) return;
    $ledgerId = uuid_string();
    $db->prepare(
        'INSERT INTO influencer_ledger_entries
         (id,influencer_id,entry_type,amount,status,reference_type,reference_id,created_at)
         VALUES (:id,:influencer,"service_earning",:amount,"eligible","influencer_service_order",:reference,UTC_TIMESTAMP())'
    )->execute([
        'id' => $ledgerId, 'influencer' => $order['influencer_id'],
        'amount' => $order['influencer_earning_amount'], 'reference' => $order['id'],
    ]);
    $db->prepare(
        'INSERT INTO influencer_service_earnings (order_id,influencer_id,ledger_entry_id,amount,created_at)
         VALUES (:order_id,:influencer,:ledger,:amount,UTC_TIMESTAMP())'
    )->execute([
        'order_id' => $order['id'], 'influencer' => $order['influencer_id'],
        'ledger' => $ledgerId, 'amount' => $order['influencer_earning_amount'],
    ]);
}

function service_order_enqueue_refund(PDO $db, string $orderId, string $reason, string $requestKey): array
{
    $stmt = $db->prepare(
        'SELECT o.id,o.total_amount,o.payment_status,p.id AS payment_id,p.provider_payment_id,p.status AS payment_record_status,p.currency
         FROM influencer_service_orders o
         JOIN influencer_service_payments p ON p.order_id=o.id
         WHERE o.id=:id FOR UPDATE'
    );
    $stmt->execute(['id' => $orderId]);
    $order = $stmt->fetch();
    if (!$order || $order['payment_record_status'] !== 'paid' || empty($order['provider_payment_id'])) {
        throw new RuntimeException('A captured Razorpay payment is required before a refund can be queued.');
    }
    $existing = $db->prepare('SELECT * FROM influencer_service_refunds WHERE request_key=:request_key LIMIT 1');
    $existing->execute(['request_key' => $requestKey]);
    $refund = $existing->fetch();
    if ($refund) return $refund;
    $id = uuid_string();
    $db->prepare(
        'INSERT INTO influencer_service_refunds
         (id,order_id,payment_id,request_key,amount,currency,reason,status,attempts,max_attempts,next_attempt_at,created_at,updated_at)
         VALUES (:id,:order_id,:payment_id,:request_key,:amount,:currency,:reason,"queued",0,5,UTC_TIMESTAMP(),UTC_TIMESTAMP(),UTC_TIMESTAMP())'
    )->execute([
        'id' => $id, 'order_id' => $orderId, 'payment_id' => $order['payment_id'],
        'request_key' => $requestKey, 'amount' => $order['total_amount'], 'currency' => $order['currency'],
        'reason' => mb_substr($reason, 0, 1000),
    ]);
    return ['id' => $id, 'status' => 'queued', 'amount' => $order['total_amount']];
}

function service_order_reverse_earning(PDO $db, string $orderId, string $influencerId): void
{
    $earning = $db->prepare('SELECT amount FROM influencer_service_earnings WHERE order_id=:id');
    $earning->execute(['id' => $orderId]);
    $amount = $earning->fetchColumn();
    if ($amount === false) return;
    try {
        $db->prepare(
            'INSERT INTO influencer_ledger_entries
             (id,influencer_id,entry_type,amount,status,reference_type,reference_id,created_at)
             VALUES (:id,:influencer,"service_refund",:amount,"eligible","influencer_service_refund",:reference,UTC_TIMESTAMP())'
        )->execute([
            'id' => uuid_string(), 'influencer' => $influencerId,
            'amount' => -abs((float)$amount), 'reference' => $orderId,
        ]);
    } catch (PDOException $e) {
        if ((string)$e->getCode() !== '23000') throw $e;
    }
}
