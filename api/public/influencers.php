<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
send_cors_headers();
if(($_SERVER['REQUEST_METHOD']??'GET')!=='GET')json_response(405,['success'=>false,'message'=>'Method not allowed.']);
$db=get_db();
$visibility=APP_ENV==='local'?' AND verification_status IN ("approved","demo_fixture")':' AND verification_status="approved"';
$vendorApproval=APP_ENV==='local'?'v.verification_status IN ("approved","demo_fixture")':'v.verification_status="approved"';
$handle=strtolower(trim((string)($_GET['handle']??'')));
if($handle!==''&&!preg_match('/^[a-z0-9._-]{3,100}$/',$handle))json_response(422,['success'=>false,'message'=>'Invalid creator handle.']);
if($handle!==''){
    $stmt=$db->prepare('SELECT id,full_name,handle,bio,platforms_json,audience_json,verification_status,created_at,profile_image_id,cover_image_id,portfolio_json FROM influencers WHERE handle=:handle AND status="active" AND email_verified=1'.$visibility.' LIMIT 1');    $stmt->execute(['handle'=>$handle]);
    $creator=$stmt->fetch();
    if(!$creator)json_response(404,['success'=>false,'message'=>'Creator not found.']);
    $public=public_influencer($creator);
        // Fetch Image URLs
    $public['profile_image'] = null;
    $public['cover_image'] = null;
    $public['portfolio'] = [];

    if (!empty($creator['profile_image_id'])) {
        $img = $db->prepare('SELECT CONCAT("../files.php?id=",id) FROM file_assets WHERE id=:id AND visibility="public"');
        $img->execute(['id' => $creator['profile_image_id']]);
        $public['profile_image'] = $img->fetchColumn() ?: null;
    }

    if (!empty($creator['cover_image_id'])) {
        $img = $db->prepare('SELECT CONCAT("../files.php?id=",id) FROM file_assets WHERE id=:id AND visibility="public"');
        $img->execute(['id' => $creator['cover_image_id']]);
        $public['cover_image'] = $img->fetchColumn() ?: null;
    }

    $portfolioIds = json_decode((string)($creator['portfolio_json'] ?? '[]'), true) ?: [];
    if (!empty($portfolioIds)) {
        $inQuery = str_repeat('?,', count($portfolioIds) - 1) . '?';
        $img = $db->prepare("SELECT CONCAT('../files.php?id=',id) FROM file_assets WHERE id IN ($inQuery) AND visibility='public'");
        $img->execute($portfolioIds);
        $public['portfolio'] = $img->fetchAll(PDO::FETCH_COLUMN);
    }
    $products=$db->prepare('SELECT p.id,p.name,p.slug,p.selling_price,p.published_snapshot_json,(SELECT CONCAT("../files.php?id=",f.id) FROM product_images pi JOIN file_assets f ON f.id=pi.file_asset_id WHERE pi.product_id=p.id AND f.visibility="public" AND f.scan_status="clean" AND f.deleted_at IS NULL ORDER BY pi.is_primary DESC,pi.sort_order LIMIT 1) image_url FROM influencer_curated_products cp JOIN products p ON p.id=cp.product_id JOIN vendors v ON v.id=p.vendor_id WHERE cp.influencer_id=:owner AND p.published_snapshot_json IS NOT NULL AND p.status NOT IN ("suspended","archived") AND v.status="active" AND '.$vendorApproval.' ORDER BY cp.sort_order,cp.created_at LIMIT 24');
    $products->execute(['owner'=>$creator['id']]);$public['products']=[];
    foreach($products->fetchAll() as $product){$snapshot=json_decode((string)$product['published_snapshot_json'],true)?:[];$public['products'][]=['id'=>$product['id'],'name'=>(string)($snapshot['name']??$product['name']),'price'=>(float)($snapshot['selling_price']??$product['selling_price']),'image_url'=>$product['image_url']];}
    $campaign=$db->prepare('SELECT l.code,c.disclosure_text FROM influencer_referral_links l JOIN influencer_campaigns c ON c.id=l.campaign_id WHERE l.influencer_id=:owner AND l.status="active" AND c.status="active" AND c.starts_at<=UTC_TIMESTAMP() AND c.ends_at>=UTC_TIMESTAMP() ORDER BY c.ends_at LIMIT 1');
    $campaign->execute(['owner'=>$creator['id']]);$public['referral']=$campaign->fetch()?:null;
    json_response(200,['success'=>true,'creator'=>$public]);
}
$stmt=$db->query('SELECT id,full_name,handle,bio,platforms_json,audience_json,verification_status,created_at,profile_image_id FROM influencers WHERE status="active" AND email_verified=1'.$visibility.' ORDER BY created_at DESC LIMIT 100');
$creators=[];

foreach($stmt->fetchAll() as $creator){
    $pub = public_influencer($creator);
    
    // Fetch the profile image URL for the directory card
    if (!empty($creator['profile_image_id'])) {
        $img = $db->prepare('SELECT CONCAT("../files.php?id=",id) FROM file_assets WHERE id=:id AND visibility="public"');
        $img->execute(['id' => $creator['profile_image_id']]);
        $pub['profile_image'] = $img->fetchColumn() ?: null;
    } else {
        $pub['profile_image'] = null;
    }
    
    $creators[] = $pub;
}

json_response(200,['success'=>true,'creators'=>$creators]);

function public_influencer(array $row): array {
    $platforms=json_decode((string)($row['platforms_json']??'[]'),true);
    $audience=json_decode((string)($row['audience_json']??'{}'),true);
    if(!is_array($platforms))$platforms=[];
    if(!is_array($audience))$audience=[];
    $safePlatforms=[];
    foreach($platforms as $platform){
        if(!is_array($platform))continue;
        $url=trim((string)($platform['url']??''));
        if(!filter_var($url,FILTER_VALIDATE_URL)||!in_array(strtolower((string)parse_url($url,PHP_URL_SCHEME)),['https','http'],true))continue;
        $safePlatforms[]=['name'=>mb_substr(trim((string)($platform['name']??'Social')),0,50),'url'=>$url,'followers'=>max(0,(int)($platform['followers']??0))];
    }
    return ['full_name'=>$row['full_name'],'handle'=>$row['handle'],'bio'=>$row['bio']?:'','region'=>mb_substr(trim((string)($audience['region']??'')),0,100),'platforms'=>$safePlatforms,'followers'=>array_sum(array_column($safePlatforms,'followers')),'joined_at'=>$row['created_at'],'is_demo'=>APP_ENV==='local'&&$row['verification_status']==='demo_fixture'];
}
