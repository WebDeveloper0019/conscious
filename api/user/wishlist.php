<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
send_cors_headers();
$principal=require_principal(['customer']);$userId=$principal['id'];$db=get_db();$method=$_SERVER['REQUEST_METHOD']??'GET';
$vendorApproval=APP_ENV==='local'?'v.verification_status IN ("approved","demo_fixture")':'v.verification_status="approved"';
function wishlist_public(array $row):array{return ['id'=>(int)$row['id'],'productId'=>$row['product_id'],'productName'=>$row['live_name']?:$row['product_name'],'productPrice'=>$row['live_price']!==null?(float)$row['live_price']:($row['product_price']!==null?(float)$row['product_price']:null),'productImage'=>$row['live_image']?:$row['product_image'],'vendorName'=>$row['live_vendor']?:$row['vendor_name'],'available'=>!empty($row['live_vendor']),'createdAt'=>$row['created_at']];}
if($method==='GET'){
    $stmt=$db->prepare('SELECT w.*,p.name AS live_name,p.selling_price AS live_price,v.display_name AS live_vendor,(SELECT CONCAT("../files.php?id=",f.id) FROM product_images pi JOIN file_assets f ON f.id=pi.file_asset_id WHERE pi.product_id=p.id AND f.visibility="public" AND f.scan_status="clean" AND f.deleted_at IS NULL ORDER BY pi.is_primary DESC,pi.sort_order LIMIT 1) AS live_image FROM wishlist_items w LEFT JOIN products p ON p.id=w.product_id AND p.published_snapshot_json IS NOT NULL AND p.status NOT IN ("suspended","archived") LEFT JOIN vendors v ON v.id=p.vendor_id AND v.status="active" AND '.$vendorApproval.' WHERE w.user_id=:user ORDER BY w.created_at DESC');
    $stmt->execute(['user'=>$userId]);$items=array_map('wishlist_public',$stmt->fetchAll());json_response(200,['success'=>true,'items'=>$items,'count'=>count($items)]);
}
if($method==='POST'){
    $body=get_json_body();$productId=trim((string)($body['productId']??''));if(!preg_match('/^[a-f0-9-]{36}$/i',$productId))json_response(422,['success'=>false,'message'=>'Choose a valid product.']);
    $product=$db->prepare('SELECT p.id,p.name,p.selling_price,v.display_name AS vendor_name,(SELECT CONCAT("../files.php?id=",f.id) FROM product_images pi JOIN file_assets f ON f.id=pi.file_asset_id WHERE pi.product_id=p.id AND f.visibility="public" AND f.scan_status="clean" AND f.deleted_at IS NULL ORDER BY pi.is_primary DESC,pi.sort_order LIMIT 1) AS image_url FROM products p JOIN vendors v ON v.id=p.vendor_id WHERE p.id=:id AND p.published_snapshot_json IS NOT NULL AND p.status NOT IN ("suspended","archived") AND v.status="active" AND '.$vendorApproval.' LIMIT 1');
    $product->execute(['id'=>$productId]);$row=$product->fetch();if(!$row)json_response(404,['success'=>false,'message'=>'Product is not currently available.']);
    $db->prepare('INSERT INTO wishlist_items (user_id,product_id,product_name,product_price,product_image,vendor_name,created_at) VALUES (:user,:product,:name,:price,:image,:vendor,UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE product_name=VALUES(product_name),product_price=VALUES(product_price),product_image=VALUES(product_image),vendor_name=VALUES(vendor_name)')->execute(['user'=>$userId,'product'=>$productId,'name'=>$row['name'],'price'=>$row['selling_price'],'image'=>$row['image_url'],'vendor'=>$row['vendor_name']]);
    json_response(200,['success'=>true,'message'=>'Added to wishlist.']);
}
if($method==='DELETE'){
    $productId=trim((string)($_GET['productId']??''));if($productId==='')json_response(422,['success'=>false,'message'=>'Product id is required.']);$stmt=$db->prepare('DELETE FROM wishlist_items WHERE user_id=:user AND product_id=:product');$stmt->execute(['user'=>$userId,'product'=>$productId]);json_response(200,['success'=>true,'message'=>'Removed from wishlist.','removed'=>$stmt->rowCount()>0]);
}
json_response(405,['success'=>false,'message'=>'Method not allowed.']);
