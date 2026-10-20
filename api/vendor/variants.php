<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
require_once __DIR__.'/../lib/catalog.php';
send_cors_headers();
$vendorId = require_vendor(true)['vendor']['id'];
$db = get_db();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$productId = trim((string)($_GET['product_id'] ?? ''));
$product = vendor_product($productId,$vendorId);
if ($method !== 'GET' && !in_array($product['status'],['draft','changes_requested','rejected'],true)) json_response(409,['success'=>false,'message'=>'Create a new product draft version before changing variants.']);

if ($method === 'GET') {
    $query = $db->prepare('SELECT v.*,i.stock_quantity,i.reserved_quantity,i.low_stock_threshold FROM product_variants v JOIN inventory_levels i ON i.variant_id=v.id WHERE v.product_id=:product ORDER BY v.created_at');
    $query->execute(['product'=>$productId]);
    json_response(200,['success'=>true,'variants'=>$query->fetchAll()]);
}
if ($method === 'POST') {
    $body = get_json_body();
    $name = trim((string)($body['name'] ?? ''));
    $sku = strtoupper(trim((string)($body['sku'] ?? '')));
    $stock = filter_var($body['stock'] ?? 0,FILTER_VALIDATE_INT);
    $adjustment = filter_var($body['price_adjustment'] ?? 0,FILTER_VALIDATE_FLOAT);
    if (mb_strlen($name)<1 || mb_strlen($name)>180 || !preg_match('/^[A-Z0-9._-]{2,100}$/',$sku) || $stock===false || $stock<0 || $stock>1000000 || $adjustment===false || abs($adjustment)>1000000) json_response(422,['success'=>false,'message'=>'Variant name, SKU, stock or price adjustment is invalid.']);
    $id = uuid_string();
    $db->beginTransaction();
    try {
        $db->prepare('INSERT INTO product_variants (id,product_id,sku,name,attributes_json,price_adjustment,weight_grams,is_active,created_at,updated_at) VALUES (:id,:product,:sku,:name,:attributes,:price,:weight,1,UTC_TIMESTAMP(),UTC_TIMESTAMP())')->execute(['id'=>$id,'product'=>$productId,'sku'=>$sku,'name'=>$name,'attributes'=>json_encode((array)($body['attributes'] ?? []),JSON_UNESCAPED_SLASHES),'price'=>round((float)$adjustment,2),'weight'=>isset($body['weight_grams'])?max(1,(int)$body['weight_grams']):null]);
        $db->prepare('INSERT INTO inventory_levels (variant_id,stock_quantity,reserved_quantity,low_stock_threshold,updated_at) VALUES (:id,:stock,0,:threshold,UTC_TIMESTAMP())')->execute(['id'=>$id,'stock'=>$stock,'threshold'=>max(0,(int)($body['low_stock_threshold'] ?? 5))]);
        $db->prepare('INSERT INTO inventory_movements (variant_id,vendor_id,movement_type,quantity_delta,reserved_delta,stock_after,reserved_after,note,actor_type,actor_id,created_at) VALUES (:variant,:vendor,"initial",:qty_change,0,:qty_after,0,"Initial variant stock","vendor",:actor,UTC_TIMESTAMP())')->execute(['variant'=>$id,'vendor'=>$vendorId,'qty_change'=>$stock,'qty_after'=>$stock,'actor'=>$vendorId]);
        $db->commit();
    } catch (Throwable $error) {
        if ($db->inTransaction()) $db->rollBack();
        throw $error;
    }
    json_response(201,['success'=>true,'message'=>'Variant created.','variant_id'=>$id]);
}
if ($method === 'PATCH') {
    $body = get_json_body();
    $id = trim((string)($body['variant_id'] ?? ''));
    $name = trim((string)($body['name'] ?? ''));
    $adjustment = filter_var($body['price_adjustment'] ?? 0,FILTER_VALIDATE_FLOAT);
    if (mb_strlen($name)<1 || mb_strlen($name)>180 || $adjustment===false || abs($adjustment)>1000000) json_response(422,['success'=>false,'message'=>'Variant name or price adjustment is invalid.']);
    $query = $db->prepare('UPDATE product_variants SET name=:name,attributes_json=:attributes,price_adjustment=:price,weight_grams=:weight,is_active=:active,updated_at=UTC_TIMESTAMP() WHERE id=:id AND product_id=:product');
    $query->execute(['name'=>$name,'attributes'=>json_encode((array)($body['attributes'] ?? [])),'price'=>round((float)$adjustment,2),'weight'=>isset($body['weight_grams'])?max(1,(int)$body['weight_grams']):null,'active'=>!empty($body['is_active'])?1:0,'id'=>$id,'product'=>$productId]);
    if (!$query->rowCount()) json_response(404,['success'=>false,'message'=>'Variant not found or unchanged.']);
    json_response(200,['success'=>true,'message'=>'Variant updated.']);
}
json_response(405,['success'=>false,'message'=>'Method not allowed.']);
