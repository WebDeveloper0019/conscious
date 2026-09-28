<?php
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
send_cors_headers();
$payload=require_auth(); $userId=auth_user_id($payload); $db=get_db();

function wishlist_public(array $r):array{return[
 'id'=>(int)$r['id'],'productId'=>$r['product_id'],'productName'=>$r['product_name'],
 'productPrice'=>$r['product_price']!==null?(float)$r['product_price']:null,'productImage'=>$r['product_image'],
 'vendorName'=>$r['vendor_name'],'createdAt'=>$r['created_at']
];}

if($_SERVER['REQUEST_METHOD']==='GET'){
 $stmt=$db->prepare('SELECT * FROM wishlist_items WHERE user_id=:user ORDER BY created_at DESC');
 $stmt->execute(['user'=>$userId]); $items=array_map('wishlist_public',$stmt->fetchAll());
 json_response(200,['success'=>true,'items'=>$items,'count'=>count($items)]);
}
if($_SERVER['REQUEST_METHOD']==='POST'){
 $b=get_json_body(); $productId=trim((string)($b['productId']??''));
 if($productId==='') json_response(422,['success'=>false,'message'=>'Product id is required.']);
 $stmt=$db->prepare('INSERT INTO wishlist_items (user_id,product_id,product_name,product_price,product_image,vendor_name,created_at)
 VALUES (:user,:product,:name,:price,:image,:vendor,:created)
 ON DUPLICATE KEY UPDATE product_name=:name2,product_price=:price2,product_image=:image2,vendor_name=:vendor2');
 $price=isset($b['productPrice'])&&$b['productPrice']!==''?(float)$b['productPrice']:null;
 $stmt->execute(['user'=>$userId,'product'=>$productId,'name'=>trim((string)($b['productName']??''))?:null,'price'=>$price,
  'image'=>trim((string)($b['productImage']??''))?:null,'vendor'=>trim((string)($b['vendorName']??''))?:null,'created'=>date('Y-m-d H:i:s'),
  'name2'=>trim((string)($b['productName']??''))?:null,'price2'=>$price,'image2'=>trim((string)($b['productImage']??''))?:null,'vendor2'=>trim((string)($b['vendorName']??''))?:null]);
 $stmt=$db->prepare('SELECT * FROM wishlist_items WHERE user_id=:user AND product_id=:product'); $stmt->execute(['user'=>$userId,'product'=>$productId]);
 json_response(201,['success'=>true,'message'=>'Added to wishlist.','item'=>wishlist_public($stmt->fetch())]);
}
if($_SERVER['REQUEST_METHOD']==='DELETE'){
 $productId=trim((string)($_GET['productId']??'')); $id=(int)($_GET['id']??0);
 if($productId==='' && !$id) json_response(422,['success'=>false,'message'=>'Product id or wishlist id is required.']);
 $sql=$productId!==''?'DELETE FROM wishlist_items WHERE user_id=:user AND product_id=:product':'DELETE FROM wishlist_items WHERE user_id=:user AND id=:id';
 $params=$productId!==''?['user'=>$userId,'product'=>$productId]:['user'=>$userId,'id'=>$id];
 $stmt=$db->prepare($sql); $stmt->execute($params);
 if(!$stmt->rowCount()) json_response(404,['success'=>false,'message'=>'Wishlist item not found.']);
 json_response(200,['success'=>true,'message'=>'Removed from wishlist.']);
}
json_response(405,['success'=>false,'message'=>'Method not allowed.']);
