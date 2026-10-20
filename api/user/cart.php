<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
require_once __DIR__.'/../lib/commerce.php';
send_cors_headers();
$auth=require_auth();$userId=auth_user_id($auth);$method=$_SERVER['REQUEST_METHOD']??'GET';$db=get_db();
if($method==='GET')json_response(200,['success'=>true,'cart'=>cart_details($userId)]);
if(!in_array($method,['POST','PATCH','DELETE'],true))json_response(405,['success'=>false,'message'=>'Method not allowed.']);
$body=$method==='DELETE'?[]:get_json_body();
$quantity=filter_var($body['quantity']??null,FILTER_VALIDATE_INT);
if($method!=='DELETE'&&($quantity===false||$quantity<1||$quantity>20))json_response(422,['success'=>false,'message'=>'Choose a whole-number quantity from 1 to 20.','errors'=>['quantity'=>'Enter a whole number between 1 and 20.']]);
$db->beginTransaction();
try{
    $owner=$db->prepare('SELECT id FROM users WHERE id=:id FOR UPDATE');$owner->execute(['id'=>$userId]);$owner->fetch();
    $cart=active_cart($userId,true);
    $itemId=trim((string)($method==='DELETE'?($_GET['item_id']??''):($body['item_id']??'')));
    if($method==='DELETE'){
        $item=$db->prepare('SELECT id FROM cart_items WHERE id=:id AND cart_id=:cart');$item->execute(['id'=>$itemId,'cart'=>$cart['id']]);
        if(!$item->fetch())json_response(404,['success'=>false,'message'=>'This item is no longer in your bag. Refresh your bag and try again.']);
        expire_customer_quotes($userId);
        $db->prepare('DELETE FROM cart_items WHERE id=:id AND cart_id=:cart')->execute(['id'=>$itemId,'cart'=>$cart['id']]);
    }else{
        $variant=trim((string)($body['variant_id']??''));
        $existing=$db->prepare('SELECT id,quantity FROM cart_items WHERE cart_id=:cart AND variant_id=:variant FOR UPDATE');
        $existing->execute(['cart'=>$cart['id'],'variant'=>$variant]);$line=$existing->fetch();
        if($method==='PATCH'&&(!$line||$line['id']!==$itemId))json_response(404,['success'=>false,'message'=>'This item is no longer in your bag. Refresh your bag and try again.']);
        $next=$method==='POST'?(int)($line['quantity']??0)+(int)$quantity:(int)$quantity;
        if($next>20)json_response(422,['success'=>false,'message'=>'You can add up to 20 of this item. Your bag already contains '.(int)($line['quantity']??0).'.']);
        expire_customer_quotes($userId);
        $approval=APP_ENV==='local'?'v.verification_status IN ("approved","demo_fixture")':'v.verification_status="approved"';
        $stock=$db->prepare('SELECT p.name,pv.is_active,p.status,v.status AS vendor_status,(i.stock_quantity-i.reserved_quantity) AS available FROM product_variants pv JOIN products p ON p.id=pv.product_id JOIN vendors v ON v.id=p.vendor_id JOIN inventory_levels i ON i.variant_id=pv.id WHERE pv.id=:id AND '.$approval.' FOR UPDATE');
        $stock->execute(['id'=>$variant]);$product=$stock->fetch();
        if(!$product||!(int)$product['is_active']||!in_array($product['status'],['approved','active'],true)||$product['vendor_status']!=='active')json_response(422,['success'=>false,'message'=>'This product is currently unavailable. Choose another product or check back later.']);
        if((int)$product['available']<$next)json_response(409,['success'=>false,'message'=>'Only '.max(0,(int)$product['available']).' of '.$product['name'].' are available. Your bag has not been changed.','code'=>'INSUFFICIENT_STOCK']);
        if($line)$db->prepare('UPDATE cart_items SET quantity=:quantity,updated_at=UTC_TIMESTAMP() WHERE id=:id')->execute(['quantity'=>$next,'id'=>$line['id']]);
        else $db->prepare('INSERT INTO cart_items (id,cart_id,variant_id,quantity,created_at,updated_at) VALUES (:id,:cart,:variant,:quantity,UTC_TIMESTAMP(),UTC_TIMESTAMP())')->execute(['id'=>uuid_string(),'cart'=>$cart['id'],'variant'=>$variant,'quantity'=>$next]);
    }
    $db->prepare('UPDATE carts SET updated_at=UTC_TIMESTAMP() WHERE id=:id')->execute(['id'=>$cart['id']]);
    $result=cart_details($userId);$db->commit();
    json_response(200,['success'=>true,'message'=>$method==='DELETE'?'Item removed from your bag.':'Your bag has been updated.','cart'=>$result]);
}catch(Throwable $error){if($db->inTransaction())$db->rollBack();throw $error;}
