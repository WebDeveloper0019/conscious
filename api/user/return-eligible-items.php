<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
send_cors_headers();
$auth=require_auth();
if(($_SERVER['REQUEST_METHOD']??'')!=='GET')json_response(405,['success'=>false,'message'=>'Method not allowed.']);
$stmt=get_db()->prepare(
    'SELECT oi.id AS order_item_id,oi.product_name,oi.quantity,oi.cancelled_quantity,oi.returned_quantity,
            oi.status AS item_status,o.order_number,g.status AS group_status,g.policy_snapshot_json,
            (SELECT COALESCE(SUM(r.quantity),0) FROM return_requests r
             WHERE r.order_item_id=oi.id AND r.status NOT IN ("rejected","cancelled")) AS open_quantity
     FROM order_items oi
     JOIN orders o ON o.id=oi.order_id
     JOIN vendor_order_groups g ON g.id=oi.vendor_group_id
     WHERE o.user_id=:user AND (g.status="delivered" OR oi.status="delivered")
     ORDER BY o.created_at DESC,oi.id DESC LIMIT 200'
);
$stmt->execute(['user'=>auth_user_id($auth)]);
$items=[];
foreach($stmt->fetchAll() as $row){
    $available=max(0,(int)$row['quantity']-(int)$row['cancelled_quantity']-(int)$row['returned_quantity']-(int)$row['open_quantity']);
    if($available<1)continue;
    $policy=json_decode((string)$row['policy_snapshot_json'],true)?:[];
    $items[]=[
        'orderItemId'=>(int)$row['order_item_id'],
        'orderNumber'=>$row['order_number'],
        'productName'=>$row['product_name'],
        'eligibleQuantity'=>$available,
        'returnPolicy'=>(string)($policy['return_policy']??''),
    ];
}
json_response(200,['success'=>true,'items'=>$items]);
