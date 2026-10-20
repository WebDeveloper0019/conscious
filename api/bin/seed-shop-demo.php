<?php
declare(strict_types=1);

if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require_once __DIR__.'/../lib/helpers.php';
if(APP_ENV!=='local'){fwrite(STDERR,"Shop demo seeding is local-only.\n");exit(1);}

// These catalogue fixtures use existing project photographs, not HTML sample cards.
$items=[
    ['blue-pottery-vase','Blue Pottery Vase','blue-pottery-vase.jpg',6,'Jaipur','Glazed ceramic',1299,1799,700,'Pottery & Ceramics','pottery-ceramics'],
    ['dhokra-musician','Dhokra Musician Sculpture','dhokra-musician.jpg',8,'West Bengal','Brass',2799,3599,850,'Traditional Art','traditional-art'],
    ['kantha-cushion','Hand-Stitched Kantha Cushion','kantha-cushion.jpg',7,'West Bengal','Cotton',1599,2199,450,'Handloom & Textiles','handloom-textiles'],
    ['kundan-necklace','Kundan Necklace Set','kundan-necklace.jpg',9,'Rajasthan','Mixed metal and glass',3499,4999,320,'Handmade Jewelry','handmade-jewelry'],
    ['madhubani-painting','Madhubani Folk Painting','madhubani-painting.jpg',6,'Bihar','Paper and natural pigment',4500,4500,450,'Paintings','paintings'],
    ['pashmina-shawl','Embroidered Pashmina Shawl','pashmina-shawl.jpg',7,'Kashmir','Wool',8999,12000,500,'Handloom & Textiles','handloom-textiles'],
    ['sandalwood-box','Carved Sandalwood Box','sandalwood-box.jpg',10,'Uttar Pradesh','Wood',1799,2499,650,'Wood Crafts','wood-crafts'],
    ['warli-wall-art','Warli Wall Art','warli-wall-art.jpg',8,'Maharashtra','Canvas and paint',2499,3299,600,'Traditional Art','traditional-art'],
];
$sourceRoot=dirname(__DIR__,2).'/frontend/modules/customer/assets/images/products';
$storageRoot=rtrim(PRIVATE_STORAGE_PATH,'/\\');
$targetFolder=$storageRoot.'/demo/catalog';
foreach($items as $item){if(!is_file($sourceRoot.'/'.$item[2])){fwrite(STDERR,"Missing photo: {$item[2]}\n");exit(1);}}
if(!is_dir($targetFolder)&&!mkdir($targetFolder,0775,true)&&!is_dir($targetFolder)){fwrite(STDERR,"Cannot create demo image storage.\n");exit(1);}
$db=get_db();
$vendorQuery=$db->prepare('SELECT id,verification_status FROM vendors WHERE email=:email AND status="active" LIMIT 1');
$productQuery=$db->prepare('SELECT id FROM products WHERE slug=:slug LIMIT 1');
$categoryQuery=$db->prepare('SELECT id FROM categories WHERE slug=:slug LIMIT 1');
$insertCategory=$db->prepare('INSERT INTO categories (name,slug,status,created_at) VALUES (:name,:slug,"active",UTC_TIMESTAMP())');
$insertFile=$db->prepare('INSERT INTO file_assets (id,owner_type,owner_id,purpose,storage_key,original_name,mime_type,extension,size_bytes,sha256,visibility,scan_status,created_at) VALUES (:id,"vendor",:vendor,"product_image",:storage,:name,"image/jpeg","jpg",:bytes,:sha,"public","clean",UTC_TIMESTAMP())');
$insertProduct=$db->prepare('INSERT INTO products (id,vendor_id,category_id,name,slug,sku,short_description,description,mrp,selling_price,gst_percent,material,origin_state,tags_json,package_weight_grams,status,review_version,published_version,published_snapshot_json,published_at,created_at) VALUES (:id,:vendor,:category,:name,:slug,:sku,:short,:description,:mrp,:price,0,:material,:origin,:tags,:weight,"approved",1,1,:snapshot,UTC_TIMESTAMP(),UTC_TIMESTAMP())');
$insertImage=$db->prepare('INSERT INTO product_images (id,product_id,file_asset_id,alt_text,sort_order,is_primary,created_at) VALUES (:id,:product,:asset,:alt,0,1,UTC_TIMESTAMP())');
$insertVariant=$db->prepare('INSERT INTO product_variants (id,product_id,sku,name,price_adjustment,weight_grams,is_active,created_at) VALUES (:id,:product,:sku,"Standard",0,:weight,1,UTC_TIMESTAMP())');
$insertStock=$db->prepare('INSERT INTO inventory_levels (variant_id,stock_quantity,reserved_quantity,low_stock_threshold,updated_at) VALUES (:id,15,0,3,UTC_TIMESTAMP())');
$insertVersion=$db->prepare('INSERT INTO product_versions (id,product_id,version_number,snapshot_json,review_status,submitted_at,reviewed_at,created_at) VALUES (:id,:product,1,:snapshot,"approved",UTC_TIMESTAMP(),UTC_TIMESTAMP(),UTC_TIMESTAMP())');
$insertShipping=$db->prepare('INSERT IGNORE INTO vendor_shipping_settings (vendor_id,coverage_model,processing_min_days,processing_max_days,courier_partners,base_charge,free_above,cod_enabled,created_at) VALUES (:vendor,"all_india",1,5,"Manual vendor dispatch",79,999,1,UTC_TIMESTAMP())');
$created=0;$skipped=0;
foreach($items as $item){
    [$key,$name,$filename,$vendorNumber,$origin,$material,$price,$mrp,$weight,$categoryName,$categorySlug]=$item;
    $email=sprintf('vendor.demo%02d@conscious.test',$vendorNumber);
    $vendorQuery->execute(['email'=>$email]);$vendor=$vendorQuery->fetch();
    if(!$vendor||$vendor['verification_status']!=='demo_fixture'){throw new RuntimeException("Expected local demo vendor not found: {$email}. Run seed-marketplace-showcase.php first.");}
    $categoryQuery->execute(['slug'=>$categorySlug]);$categoryId=$categoryQuery->fetchColumn();
    if(!$categoryId){$insertCategory->execute(['name'=>$categoryName,'slug'=>$categorySlug]);$categoryId=(int)$db->lastInsertId();}
    $slug='local-demo-'.$key;$productQuery->execute(['slug'=>$slug]);$existingProduct=$productQuery->fetch();
    if($existingProduct){$db->prepare('UPDATE products SET category_id=:category WHERE id=:id AND vendor_id=:vendor')->execute(['category'=>$categoryId,'id'=>$existingProduct['id'],'vendor'=>$vendor['id']]);$skipped++;continue;}
    $source=$sourceRoot.'/'.$filename;$hash=hash_file('sha256',$source);$storage='demo/catalog/'.$hash.'.jpg';$target=$storageRoot.'/'.$storage;
    if(!is_file($target)&&!copy($source,$target))throw new RuntimeException("Could not store {$filename}.");
    $productId=uuid_string();$assetId=uuid_string();$variantId=uuid_string();$sku='LOCAL-DEMO-'.strtoupper(str_replace('-','',$key));
    $description=$name.' — local demo catalogue item with a real project photograph. Vendor manually fulfils delivery across India.';
    $snapshot=json_encode(['name'=>$name,'slug'=>$slug,'short_description'=>'Local demo product · '.$origin,'description'=>$description,'mrp'=>$mrp,'selling_price'=>$price,'material'=>$material,'origin_state'=>$origin,'package_weight_grams'=>$weight],JSON_THROW_ON_ERROR);
    $db->beginTransaction();
    try{
        $insertFile->execute(['id'=>$assetId,'vendor'=>$vendor['id'],'storage'=>$storage,'name'=>$filename,'bytes'=>filesize($source),'sha'=>$hash]);
        $insertProduct->execute(['id'=>$productId,'vendor'=>$vendor['id'],'category'=>$categoryId,'name'=>$name,'slug'=>$slug,'sku'=>$sku,'short'=>'Local demo product · '.$origin,'description'=>$description,'mrp'=>$mrp,'price'=>$price,'material'=>$material,'origin'=>$origin,'tags'=>json_encode(['local-demo'],JSON_THROW_ON_ERROR),'weight'=>$weight,'snapshot'=>$snapshot]);
        $insertImage->execute(['id'=>uuid_string(),'product'=>$productId,'asset'=>$assetId,'alt'=>$name]);
        $insertVariant->execute(['id'=>$variantId,'product'=>$productId,'sku'=>$sku.'-STD','weight'=>$weight]);
        $insertStock->execute(['id'=>$variantId]);
        $insertVersion->execute(['id'=>uuid_string(),'product'=>$productId,'snapshot'=>$snapshot]);
        $insertShipping->execute(['vendor'=>$vendor['id']]);
        $db->commit();$created++;
    }catch(Throwable $error){if($db->inTransaction())$db->rollBack();throw $error;}
}
fwrite(STDOUT,"Local database shop fixtures: {$created} created, {$skipped} already present. No production listings or payments changed.\n");
