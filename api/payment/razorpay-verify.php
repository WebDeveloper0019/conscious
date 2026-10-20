<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
require_once __DIR__.'/../lib/payment.php';
require_once __DIR__.'/../lib/razorpay.php';
send_cors_headers();
$auth = require_auth();
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') json_response(405,['success'=>false,'message'=>'Method not allowed.']);
if (!razorpay_is_configured()) json_response(503,['success'=>false,'message'=>'Razorpay is not configured.']);
$body = get_json_body();
$attemptId = trim((string)($body['payment_attempt_id'] ?? ''));
$providerOrderId = trim((string)($body['razorpay_order_id'] ?? ''));
$providerPaymentId = trim((string)($body['razorpay_payment_id'] ?? ''));
$signature = trim((string)($body['razorpay_signature'] ?? ''));
if ($attemptId==='' || $providerOrderId==='' || $providerPaymentId==='' || !preg_match('/^[a-f0-9]{64}$/i',$signature)) json_response(422,['success'=>false,'message'=>'Razorpay payment details are incomplete.']);
$db = get_db();
$db->beginTransaction();
try {
    $query = $db->prepare('SELECT p.*,o.user_id,o.payment_status AS order_payment_status,o.status AS order_status,q.expires_at FROM payment_attempts p JOIN orders o ON o.id=p.order_id JOIN checkout_quotes q ON q.id=o.checkout_quote_id WHERE p.id=:id AND o.user_id=:user FOR UPDATE');
    $query->execute(['id'=>$attemptId,'user'=>auth_user_id($auth)]);
    $attempt = $query->fetch();
    if (!$attempt || $attempt['provider']!=='online') json_response(404,['success'=>false,'message'=>'Payment attempt not found.']);
    if ($attempt['provider_order_id']!==$providerOrderId || str_starts_with($providerOrderId,'mock_')) json_response(409,['success'=>false,'message'=>'Razorpay order does not match this checkout.']);
    $expected = razorpay_signature((string)$attempt['provider_order_id'],$providerPaymentId);
    if (!hash_equals($expected,strtolower($signature))) json_response(400,['success'=>false,'message'=>'Razorpay signature verification failed.']);
    if ($attempt['status']==='paid' && $attempt['order_payment_status']==='paid') {
        $db->commit();
        json_response(200,['success'=>true,'message'=>'Payment was already verified.']);
    }
    if ($attempt['status']!=='created' || $attempt['order_payment_status']!=='pending' || $attempt['order_status']!=='payment_pending') json_response(409,['success'=>false,'message'=>'This order is not awaiting payment.']);
    if (strtotime((string)$attempt['expires_at']) <= time()) json_response(409,['success'=>false,'message'=>'Payment arrived after the stock reservation expired. Contact support for reconciliation.']);
    $remote = razorpay_request('GET','payments/'.rawurlencode($providerPaymentId));
    if (($remote['order_id'] ?? '')!==$providerOrderId || ($remote['status'] ?? '')!=='captured' || (int)($remote['amount'] ?? -1)!==(int)round((float)$attempt['amount']*100) || strtoupper((string)($remote['currency'] ?? ''))!==strtoupper((string)$attempt['currency'])) json_response(409,['success'=>false,'message'=>'Payment is not captured or does not match this order. Contact support if money was deducted.']);
    confirm_online_order((int)$attempt['order_id'],$providerPaymentId);
    $db->commit();
    json_response(200,['success'=>true,'message'=>'Payment verified and order confirmed.','order_id'=>(int)$attempt['order_id']]);
} catch (Throwable $error) {
    if ($db->inTransaction()) $db->rollBack();
    throw $error;
}
