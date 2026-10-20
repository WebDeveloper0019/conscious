<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
send_cors_headers();
$db=get_db();
$id=trim((string)($_GET['id']??''));
// Demo fixtures are visible only on a local installation; production requires approval.
$vendorApproval=APP_ENV==='local' ? 'v.verification_status IN ("approved","demo_fixture")' : 'v.verification_status="approved"';
$imageSql='SELECT pi.alt_text,pi.is_primary,CONCAT("../files.php?id=",f.id) AS url FROM product_images pi JOIN file_assets f ON f.id=pi.file_asset_id WHERE pi.product_id=:id AND f.visibility="public" AND f.scan_status="clean" AND f.deleted_at IS NULL ORDER BY pi.is_primary DESC,pi.sort_order';
if($id!==''){
    $stmt=$db->prepare('SELECT p.*,c.name AS category_name,v.display_name AS vendor_name,v.vendor_type,v.verification_status AS vendor_verification_status,s.store_slug,s.tagline,s.description AS store_description FROM products p JOIN vendors v ON v.id=p.vendor_id LEFT JOIN categories c ON c.id=p.category_id LEFT JOIN vendor_store_profiles s ON s.vendor_id=v.id WHERE p.id=:id AND p.published_snapshot_json IS NOT NULL AND p.status NOT IN ("suspended","archived") AND v.status="active" AND '.$vendorApproval);
    $stmt->execute(['id'=>$id]);$product=$stmt->fetch();
    if(!$product)json_response(404,['success'=>false,'message'=>'Product not found.']);
    $published=json_decode((string)$product['published_snapshot_json'],true)?:[];
    $product=array_replace($product,$published);unset($product['published_snapshot_json']);
    $images=$db->prepare($imageSql);$images->execute(['id'=>$id]);
    $variants=$db->prepare('SELECT pv.id,pv.name,pv.attributes_json,pv.price_adjustment,pv.weight_grams,(i.stock_quantity-i.reserved_quantity) AS available_quantity FROM product_variants pv JOIN inventory_levels i ON i.variant_id=pv.id WHERE pv.product_id=:id AND pv.is_active=1');
    $variants->execute(['id'=>$id]);
    json_response(200,['success'=>true,'product'=>$product,'images'=>$images->fetchAll(),'variants'=>$variants->fetchAll()]);
}
$q=trim((string)($_GET['q']??''));$category=(int)($_GET['category_id']??0);$vendor=trim((string)($_GET['vendor_id']??''));
$limit=max(1,min(60,(int)($_GET['limit']??24)));$offset=max(0,(int)($_GET['offset']??0));
$sql='SELECT p.id,p.name,p.slug,p.short_description,p.mrp,p.selling_price,p.status,p.category_id,c.name AS category_name,p.material,p.origin_state,p.published_at,p.created_at,p.published_snapshot_json,v.id AS vendor_id,v.display_name AS vendor_name,v.vendor_type,v.verification_status AS vendor_verification_status,(SELECT CONCAT("../files.php?id=",f.id) FROM product_images pi JOIN file_assets f ON f.id=pi.file_asset_id WHERE pi.product_id=p.id AND f.visibility="public" AND f.scan_status="clean" AND f.deleted_at IS NULL ORDER BY pi.is_primary DESC,pi.sort_order LIMIT 1) AS image_url FROM products p JOIN vendors v ON v.id=p.vendor_id LEFT JOIN categories c ON c.id=p.category_id WHERE p.published_snapshot_json IS NOT NULL AND p.status NOT IN ("suspended","archived") AND v.status="active" AND '.$vendorApproval;
$params=[];
if($q!==''){$sql.=' AND (p.name LIKE :q OR p.description LIKE :q OR p.tags_json LIKE :q)';$params['q']='%'.$q.'%';}
if($category>0){$sql.=' AND (p.category_id=:category OR p.subcategory_id=:category)';$params['category']=$category;}
if($vendor!==''){$sql.=' AND p.vendor_id=:vendor';$params['vendor']=$vendor;}
$sql.=" ORDER BY p.published_at DESC,p.created_at DESC LIMIT {$limit} OFFSET {$offset}";
$stmt=$db->prepare($sql);$stmt->execute($params);$products=$stmt->fetchAll();
foreach($products as &$row){$snapshot=json_decode((string)$row['published_snapshot_json'],true)?:[];$row=array_replace($row,$snapshot);unset($row['published_snapshot_json']);}
json_response(200,['success'=>true,'products'=>$products,'pagination'=>['limit'=>$limit,'offset'=>$offset]]);
