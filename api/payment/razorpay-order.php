<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
require_once __DIR__.'/../lib/razorpay.php';
send_cors_headers();
$auth = require_auth();
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') json_response(405,['success'=>false,'message'=>'Method not allowed.']);
$attemptId = trim((string)(get_json_body()['payment_attempt_id'] ?? ''));
if ($attemptId === '') json_response(422,['success'=>false,'message'=>'Payment attempt is required.']);
$db = get_db();
$db->beginTransaction();
try {
    $query = $db->prepare('SELECT p.*,o.user_id,o.order_number,o.payment_status AS order_payment_status,o.status AS order_status,q.expires_at FROM payment_attempts p JOIN orders o ON o.id=p.order_id JOIN checkout_quotes q ON q.id=o.checkout_quote_id WHERE p.id=:id AND o.user_id=:user FOR UPDATE');
    $query->execute(['id'=>$attemptId,'user'=>auth_user_id($auth)]);
    $attempt = $query->fetch();
    if (!$attempt) json_response(404,['success'=>false,'message'=>'Payment attempt not found.']);
    if ($attempt['provider']!=='online' || $attempt['status']!=='created' || $attempt['order_payment_status']!=='pending' || $attempt['order_status']!=='payment_pending') json_response(409,['success'=>false,'message'=>'This order is not awaiting online payment.']);
    if (strtotime((string)$attempt['expires_at']) <= time()) json_response(409,['success'=>false,'message'=>'The payment reservation expired. Place a new order.']);
    $providerOrderId = (string)($attempt['provider_order_id'] ?? '');
    $mock = APP_ENV === 'local' && !razorpay_is_configured();
    if ($providerOrderId !== '') {
        if (str_starts_with($providerOrderId,'mock_') !== $mock) json_response(409,['success'=>false,'message'=>'This payment was created for a different gateway configuration.']);
    } elseif ($mock) {
        $providerOrderId = 'mock_'.$attemptId;
    } else {
        if (!razorpay_is_configured()) json_response(503,['success'=>false,'message'=>'Razorpay is not configured.']);
        $remote = razorpay_request('POST','orders',['amount'=>(int)round((float)$attempt['amount']*100),'currency'=>$attempt['currency'],'receipt'=>$attempt['order_number'],'notes'=>['conscious_order_id'=>(string)$attempt['order_id']]]);
        $providerOrderId = trim((string)($remote['id'] ?? ''));
        if ($providerOrderId === '') throw new RuntimeException('Razorpay did not return an order id.');
    }
    if (empty($attempt['provider_order_id'])) $db->prepare('UPDATE payment_attempts SET provider_order_id=:provider_order,updated_at=UTC_TIMESTAMP() WHERE id=:id')->execute(['provider_order'=>$providerOrderId,'id'=>$attemptId]);
    $db->commit();
    json_response(200,['success'=>true,'gateway'=>$mock?'mock':'razorpay','key_id'=>$mock?null:RAZORPAY_KEY_ID,'order_id'=>$providerOrderId,'amount'=>(int)round((float)$attempt['amount']*100),'currency'=>$attempt['currency'],'payment_attempt_id'=>$attemptId]);
} catch (Throwable $error) {
    if ($db->inTransaction()) $db->rollBack();
    throw $error;
}
