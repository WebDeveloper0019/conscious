<?php
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
send_cors_headers();
$principal = require_principal(['customer']); $userId = $principal['id']; $db=get_db();
if($_SERVER['REQUEST_METHOD']!=='GET') json_response(405,['success'=>false,'message'=>'Method not allowed.']);
$orderNumber=trim((string)($_GET['orderNumber']??'')); $id=(int)($_GET['id']??0);
if($orderNumber==='' && !$id) json_response(422,['success'=>false,'message'=>'Order number or id is required.']);
$sql=$orderNumber!==''?'SELECT * FROM orders WHERE user_id=:user AND order_number=:value LIMIT 1':'SELECT * FROM orders WHERE user_id=:user AND id=:value LIMIT 1';
$stmt=$db->prepare($sql); $stmt->execute(['user'=>$userId,'value'=>$orderNumber!==''?$orderNumber:$id]); $o=$stmt->fetch();
if(!$o) json_response(404,['success'=>false,'message'=>'Order not found.']);
$stmt=$db->prepare('SELECT product_id,product_name,quantity,unit_price,image FROM order_items WHERE order_id=:order ORDER BY id');
$stmt->execute(['order'=>$o['id']]);
$items=[]; foreach($stmt->fetchAll() as $i)$items[]=['productId'=>$i['product_id'],'productName'=>$i['product_name'],'quantity'=>(int)$i['quantity'],'unitPrice'=>(float)$i['unit_price'],'image'=>$i['image']];
json_response(200,['success'=>true,'order'=>['id'=>(int)$o['id'],'orderNumber'=>$o['order_number'],'status'=>$o['status'],'subtotal'=>(float)$o['subtotal'],'shippingAmount'=>(float)$o['shipping_amount'],'discountAmount'=>(float)$o['discount_amount'],'totalAmount'=>(float)$o['total_amount'],'createdAt'=>$o['created_at'],'updatedAt'=>$o['updated_at'],'items'=>$items]]);
