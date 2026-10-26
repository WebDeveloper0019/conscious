<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
send_cors_headers();
if(($_SERVER['REQUEST_METHOD']??'GET')!=='GET')json_response(405,['success'=>false,'message'=>'Method not allowed.']);
$code=strtoupper(trim((string)($_GET['code']??'')));
$product=trim((string)($_GET['product_id']??''));
if(!preg_match('/^[A-Z0-9_-]{6,32}$/',$code)||!preg_match('/^[a-f0-9-]{36}$/i',$product))json_response(422,['success'=>false,'message'=>'Invalid creator product link.']);
$db=get_db();
$stmt=$db->prepare('SELECT l.id link_id,l.influencer_id,c.attribution_days FROM influencer_referral_links l JOIN influencer_campaigns c ON c.id=l.campaign_id JOIN influencers i ON i.id=l.influencer_id JOIN influencer_curated_products cp ON cp.influencer_id=i.id AND cp.product_id=:product JOIN products p ON p.id=cp.product_id JOIN vendors v ON v.id=p.vendor_id WHERE l.code=:code AND l.status="active" AND c.status="active" AND c.starts_at<=UTC_TIMESTAMP() AND c.ends_at>=UTC_TIMESTAMP() AND i.status="active" AND p.published_snapshot_json IS NOT NULL AND p.status NOT IN ("suspended","archived") AND v.status="active" AND v.verification_status="approved" LIMIT 1');
$stmt->execute(['code'=>$code,'product'=>$product]);$link=$stmt->fetch();
if(!$link)json_response(404,['success'=>false,'message'=>'Creator product link is unavailable.']);
$visitor=(string)($_COOKIE['ci_referral_visitor']??'');
if(!preg_match('/^[a-f0-9]{64}$/',$visitor)){
    $visitor=bin2hex(random_bytes(32));
    setcookie('ci_referral_visitor',$visitor,['expires'=>time()+86400*(int)$link['attribution_days'],'path'=>'/','secure'=>secure_cookie(),'httponly'=>true,'samesite'=>'Lax']);
}
$clickId=uuid_string();$ipHash=request_fingerprint(request_ip());$fraud=[];
if(rate_limit('referral-click|'.$ipHash.'|'.$link['link_id'],30,3600))$fraud[]='high_click_velocity';
$db->prepare('INSERT INTO referral_clicks (id,referral_link_id,visitor_hash,ip_hash,user_agent_hash,fraud_flags_json,clicked_at) VALUES (:id,:link,:visitor,:ip,:agent,:fraud,UTC_TIMESTAMP())')->execute(['id'=>$clickId,'link'=>$link['link_id'],'visitor'=>hash_hmac('sha256',$visitor,APP_KEY),'ip'=>$ipHash,'agent'=>request_fingerprint((string)($_SERVER['HTTP_USER_AGENT']??'')),'fraud'=>$fraud?json_encode($fraud):null]);
setcookie('ci_referral_click',$clickId,['expires'=>time()+86400*(int)$link['attribution_days'],'path'=>'/','secure'=>secure_cookie(),'httponly'=>true,'samesite'=>'Lax']);
$script=(string)($_SERVER['SCRIPT_NAME']??'');$prefix=preg_replace('~/api/public/creator-product-referral\.php$~','',$script);
$destination=($prefix!==$script?$prefix:'').'/portals/modules/customer/product.php?id='.rawurlencode($product);
header('Location: '.$destination,true,302);exit;
