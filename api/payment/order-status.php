<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
send_cors_headers();
$auth = require_auth();
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') json_response(405,['success'=>false,'message'=>'Method not allowed.']);
$attemptId = trim((string)($_GET['payment_attempt_id'] ?? ''));
if ($attemptId === '') json_response(422,['success'=>false,'message'=>'Payment attempt is required.']);
$stmt = get_db()->prepare('SELECT p.id,p.status AS attempt_status,p.provider_order_id,o.id AS order_id,o.order_number,o.status AS order_status,o.payment_status,q.expires_at FROM payment_attempts p JOIN orders o ON o.id=p.order_id JOIN checkout_quotes q ON q.id=o.checkout_quote_id WHERE p.id=:attempt AND o.user_id=:user LIMIT 1');
$stmt->execute(['attempt'=>$attemptId,'user'=>auth_user_id($auth)]);
$row = $stmt->fetch();
if (!$row) json_response(404,['success'=>false,'message'=>'Payment attempt not found.']);
json_response(200,[
    'success'=>true,
    'order_id'=>(int)$row['order_id'],
    'order_number'=>$row['order_number'],
    'order_status'=>$row['order_status'],
    'payment_status'=>$row['payment_status'],
    'attempt_status'=>$row['attempt_status'],
    'local_demo'=>APP_ENV==='local' && str_starts_with((string)($row['provider_order_id'] ?? ''),'mock_'),
    'reservation_expired'=>strtotime((string)$row['expires_at']) <= time(),
]);
