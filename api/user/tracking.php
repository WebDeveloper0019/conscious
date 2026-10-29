<?php
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/authorization.php'; // Added auth file

send_cors_headers();

// 1. Get the secure user ID
$principal = require_principal(['customer']);
$userId = $principal['id'];
$db = get_db();

// 2. Grab the order ID from the URL (e.g., tracking.php?order_id=65)
$orderId = trim((string)($_GET['order_id'] ?? $_GET['id'] ?? ''));

if ($orderId === '') {
    json_response(422, ['success' => false, 'message' => 'Order ID is required.']);
}

// 3. Fetch the order and verify it belongs to this user
$stmt = $db->prepare('SELECT id,order_number,status,payment_status,total_amount,address_snapshot_json,placed_at,created_at FROM orders WHERE id=:id AND user_id=:user');
// FIXED: Replaced auth_user_id($auth) with $userId
$stmt->execute(['id'=>$orderId, 'user'=>$userId]); 
$order = $stmt->fetch();

if(!$order) json_response(404,['success'=>false,'message'=>'Order not found.']);

// 4. Fetch vendor groups and packages
$groups=$db->prepare('SELECT g.*,v.display_name AS vendor_name FROM vendor_order_groups g JOIN vendors v ON v.id=g.vendor_id WHERE g.order_id=:order ORDER BY g.created_at');
$groups->execute(['order'=>$orderId]);
$result=[];

foreach($groups->fetchAll() as $group){
    $items=$db->prepare('SELECT id,product_id,product_name,sku,quantity,status,unit_price,line_total,image FROM order_items WHERE vendor_group_id=:group');
    $items->execute(['group'=>$group['id']]);
    
    $packages=$db->prepare('SELECT * FROM shipment_packages WHERE vendor_group_id=:group ORDER BY created_at');
    $packages->execute(['group'=>$group['id']]);
    
    $packageRows=[];
    foreach($packages->fetchAll() as $package){
        $events=$db->prepare('SELECT status,event_at,location,note FROM shipment_events WHERE package_id=:package ORDER BY event_at DESC,created_at DESC');
        $events->execute(['package'=>$package['id']]);
        $package['events']=$events->fetchAll();
        $packageRows[]=$package;
    }
    
    $group['items']=$items->fetchAll();
    $group['packages']=$packageRows;
    $result[]=$group;
}

$order['address']=json_decode((string)$order['address_snapshot_json'],true);
unset($order['address_snapshot_json']);

json_response(200,['success'=>true,'order'=>$order,'vendor_groups'=>$result]);