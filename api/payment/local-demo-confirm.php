<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
require_once __DIR__.'/../lib/payment.php';
require_once __DIR__.'/../lib/razorpay.php';
send_cors_headers();
if (APP_ENV !== 'local' || razorpay_is_configured()) json_response(404,['success'=>false,'message'=>'Not found.']);
$auth = require_auth();
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') json_response(405,['success'=>false,'message'=>'Method not allowed.']);
$attemptId = trim((string)(get_json_body()['payment_attempt_id'] ?? ''));
if ($attemptId === '') json_response(422,['success'=>false,'message'=>'Payment attempt is required.']);
$db = get_db();
$db->beginTransaction();
try {
    $query = $db->prepare('SELECT p.id,p.order_id,p.provider,p.provider_order_id,p.status,o.payment_status,o.status AS order_status,o.order_number,q.expires_at FROM payment_attempts p JOIN orders o ON o.id=p.order_id JOIN checkout_quotes q ON q.id=o.checkout_quote_id WHERE p.id=:attempt AND o.user_id=:user FOR UPDATE');
    $query->execute(['attempt'=>$attemptId,'user'=>auth_user_id($auth)]);
    $attempt = $query->fetch();
    if (!$attempt) json_response(404,['success'=>false,'message'=>'Payment attempt not found.']);
    if ($attempt['provider']!=='online' || $attempt['provider_order_id']!=='mock_'.$attemptId || $attempt['status']!=='created' || $attempt['payment_status']!=='pending' || $attempt['order_status']!=='payment_pending') json_response(409,['success'=>false,'message'=>'This order is not awaiting a local demo payment.']);
    if (strtotime((string)$attempt['expires_at']) <= time()) json_response(409,['success'=>false,'message'=>'Payment reservation expired. Place a new order.']);
    $reference = 'local_demo_'.bin2hex(random_bytes(12));
    confirm_online_order((int)$attempt['order_id'],$reference);
    audit_event('payment.local_demo_confirmed','order',(string)$attempt['order_id'],['payment_attempt_id'=>$attemptId]);
    $db->commit();
    json_response(200,['success'=>true,'message'=>'Local demo payment simulated; no money was charged.','order_id'=>(int)$attempt['order_id'],'order_number'=>$attempt['order_number']]);
} catch (Throwable $error) {
    if ($db->inTransaction()) $db->rollBack();
    throw $error;
}
