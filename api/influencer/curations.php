<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
send_cors_headers();
$principal=require_principal(['influencer']);$id=$principal['id'];$db=get_db();$method=$_SERVER['REQUEST_METHOD']??'GET';
$vendorApproval=APP_ENV==='local'?'v.verification_status IN ("approved","demo_fixture")':'v.verification_status="approved"';
if($method==='GET'){
    $stmt=$db->prepare('SELECT c.product_id,c.sort_order,p.name,p.selling_price,p.slug,p.published_snapshot_json,(SELECT CONCAT("../files.php?id=",f.id) FROM product_images pi JOIN file_assets f ON f.id=pi.file_asset_id WHERE pi.product_id=p.id AND f.visibility="public" AND f.scan_status="clean" AND f.deleted_at IS NULL ORDER BY pi.is_primary DESC,pi.sort_order LIMIT 1) image_url FROM influencer_curated_products c JOIN products p ON p.id=c.product_id JOIN vendors v ON v.id=p.vendor_id WHERE c.influencer_id=:owner AND p.published_snapshot_json IS NOT NULL AND p.status NOT IN ("suspended","archived") AND v.status="active" AND '.$vendorApproval.' ORDER BY c.sort_order,c.created_at LIMIT 24');
    $stmt->execute(['owner'=>$id]);$products=[];foreach($stmt->fetchAll() as $product){$snapshot=json_decode((string)$product['published_snapshot_json'],true)?:[];unset($product['published_snapshot_json']);$product['name']=$snapshot['name']??$product['name'];$product['selling_price']=$snapshot['selling_price']??$product['selling_price'];$products[]=$product;}json_response(200,['success'=>true,'products'=>$products]);
}
if($method==='POST'){
    $body=get_json_body();$product=trim((string)($body['product_id']??''));
    $db->beginTransaction();
    try{
        $owner=$db->prepare('SELECT status FROM influencers WHERE id=:id FOR UPDATE');$owner->execute(['id'=>$id]);$status=$owner->fetchColumn();
        if(!in_array($status,['application_draft','changes_requested','pending_review','active'],true)){$db->rollBack();json_response(409,['success'=>false,'message'=>'Curations are unavailable in this account state.']);}
        $count=$db->prepare('SELECT COUNT(*) FROM influencer_curated_products WHERE influencer_id=:owner');$count->execute(['owner'=>$id]);$featuredCount=(int)$count->fetchColumn();
        if($featuredCount>=24){$db->rollBack();json_response(422,['success'=>false,'message'=>'A creator can feature up to 24 products.']);}
        $check=$db->prepare('SELECT p.id FROM products p JOIN vendors v ON v.id=p.vendor_id WHERE p.id=:id AND p.published_snapshot_json IS NOT NULL AND p.status NOT IN ("suspended","archived") AND v.status="active" AND '.$vendorApproval);$check->execute(['id'=>$product]);
        if(!$check->fetch()){$db->rollBack();json_response(422,['success'=>false,'message'=>'Choose a currently published product from an approved maker.']);}
        try{$db->prepare('INSERT INTO influencer_curated_products (influencer_id,product_id,sort_order,created_at) VALUES (:owner,:product,:position,UTC_TIMESTAMP())')->execute(['owner'=>$id,'product'=>$product,'position'=>$featuredCount+1]);}
        catch(PDOException $error){if((string)$error->getCode()==='23000'){$db->rollBack();json_response(409,['success'=>false,'message'=>'This product is already featured.']);}throw $error;}
        $db->commit();json_response(201,['success'=>true,'message'=>'Product added to your curated collection.']);
    }catch(Throwable $error){if($db->inTransaction())$db->rollBack();throw $error;}
}
if($method==='DELETE'){
    $body=get_json_body();$product=trim((string)($body['product_id']??''));
    $stmt=$db->prepare('DELETE FROM influencer_curated_products WHERE influencer_id=:owner AND product_id=:product');$stmt->execute(['owner'=>$id,'product'=>$product]);
    if($stmt->rowCount()===0)json_response(404,['success'=>false,'message'=>'Featured product not found.']);
    json_response(200,['success'=>true,'message'=>'Product removed from your collection.']);
}
json_response(405,['success'=>false,'message'=>'Method not allowed.']);
