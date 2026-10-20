<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') {http_response_code(404);exit;}
require_once __DIR__.'/../lib/helpers.php';
require_once __DIR__.'/../lib/commerce.php';
require_once __DIR__.'/../lib/payment.php';
require_once __DIR__.'/../lib/razorpay.php';
$db = get_db();
$expiredQuotes = 0;
$expiredBookings = 0;
$expiredPayments = 0;
$reconciledPayments = 0;
$heldForReconciliation = 0;

$db->beginTransaction();
try {
    $quotes = $db->query('SELECT id FROM checkout_quotes WHERE status="open" AND expires_at<=UTC_TIMESTAMP() FOR UPDATE')->fetchAll();
    foreach ($quotes as $quote) {
        release_quote_reservations((string)$quote['id']);
        $db->prepare('UPDATE checkout_quotes SET status="expired" WHERE id=:id')->execute(['id'=>$quote['id']]);
        $expiredQuotes++;
    }
    $bookings = $db->query('SELECT id,ticket_type_id,quantity FROM event_bookings WHERE status="payment_pending" AND reservation_expires_at<=UTC_TIMESTAMP() FOR UPDATE')->fetchAll();
    foreach ($bookings as $booking) {
        $db->prepare('UPDATE event_ticket_types SET reserved_quantity=GREATEST(0,reserved_quantity-:qty),updated_at=UTC_TIMESTAMP() WHERE id=:ticket')->execute(['qty'=>$booking['quantity'],'ticket'=>$booking['ticket_type_id']]);
        $db->prepare('UPDATE event_bookings SET status="expired",cancelled_at=UTC_TIMESTAMP() WHERE id=:id')->execute(['id'=>$booking['id']]);
        $expiredBookings++;
    }
    $db->prepare('UPDATE sustainability_claims SET public_status="hidden",review_status="expired",updated_at=UTC_TIMESTAMP() WHERE valid_until<CURDATE() AND review_status="approved"')->execute();
    $db->prepare('UPDATE vendor_certificates SET verification_status="expired",updated_at=UTC_TIMESTAMP() WHERE valid_until IS NOT NULL AND valid_until<CURDATE() AND verification_status="verified"')->execute();
    $db->prepare('UPDATE auth_sessions SET revoked_at=UTC_TIMESTAMP(),revoke_reason="expired" WHERE revoked_at IS NULL AND expires_at<=UTC_TIMESTAMP()')->execute();
    $db->commit();
} catch (Throwable $error) {
    if ($db->inTransaction()) $db->rollBack();
    fwrite(STDERR,$error->getMessage().PHP_EOL);
    exit(1);
}

// A converted online quote is no longer "open", but its stock remains reserved
// until payment is captured or the attempt is safely reconciled and expired.
$pending = $db->query('SELECT o.id AS order_id,o.checkout_quote_id,p.id AS attempt_id,p.provider_order_id,p.amount,p.currency FROM orders o JOIN checkout_quotes q ON q.id=o.checkout_quote_id JOIN payment_attempts p ON p.order_id=o.id AND p.provider="online" AND p.status="created" WHERE o.status="payment_pending" AND o.payment_status="pending" AND q.expires_at<=UTC_TIMESTAMP() ORDER BY q.expires_at LIMIT 100')->fetchAll();
foreach ($pending as $attempt) {
    $providerOrderId = (string)($attempt['provider_order_id'] ?? '');
    $capturedId = null;
    $unsafeToRelease = false;
    if ($providerOrderId !== '' && !str_starts_with($providerOrderId,'mock_')) {
        if (!razorpay_is_configured()) {
            $unsafeToRelease = true;
        } else {
            try {
                $remote = razorpay_request('GET','orders/'.rawurlencode($providerOrderId).'/payments');
                foreach ((array)($remote['items'] ?? []) as $payment) {
                    if (($payment['order_id'] ?? '') !== $providerOrderId) continue;
                    if (($payment['status'] ?? '') === 'captured' && (int)($payment['amount'] ?? -1) === (int)round((float)$attempt['amount']*100) && strtoupper((string)($payment['currency'] ?? '')) === strtoupper((string)$attempt['currency'])) $capturedId = (string)$payment['id'];
                    elseif (in_array($payment['status'] ?? '',['authorized','created'],true)) $unsafeToRelease = true;
                }
            } catch (Throwable $error) {
                $unsafeToRelease = true;
                error_log('Razorpay reconciliation deferred for order '.$attempt['order_id'].': '.$error->getMessage());
            }
        }
    }
    if ($capturedId === null && $unsafeToRelease) {$heldForReconciliation++;continue;}
    $db->beginTransaction();
    try {
        $lock = $db->prepare('SELECT status,payment_status FROM orders WHERE id=:id FOR UPDATE');
        $lock->execute(['id'=>$attempt['order_id']]);
        $order = $lock->fetch();
        if ($order && $order['status']==='payment_pending' && $order['payment_status']==='pending') {
            if ($capturedId !== null) {confirm_online_order((int)$attempt['order_id'],$capturedId);$reconciledPayments++;}
            else {fail_online_order((int)$attempt['order_id'],'Payment reservation expired without a captured payment.');$expiredPayments++;}
        }
        $db->commit();
    } catch (Throwable $error) {
        if ($db->inTransaction()) $db->rollBack();
        $heldForReconciliation++;
        error_log('Payment maintenance failed for order '.$attempt['order_id'].': '.$error->getMessage());
    }
}
fwrite(STDOUT,"Maintenance complete: {$expiredQuotes} quotes, {$expiredBookings} bookings, {$expiredPayments} unpaid orders expired; {$reconciledPayments} captured orders confirmed; {$heldForReconciliation} payment attempts held for reconciliation.\n");
