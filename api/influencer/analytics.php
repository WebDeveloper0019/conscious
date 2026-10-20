<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
send_cors_headers();
if(($_SERVER['REQUEST_METHOD']??'')!=='GET')json_response(405,['success'=>false,'message'=>'Method not allowed.']);
$principal=require_principal(['influencer']);$id=$principal['id'];$db=get_db();
$days=(int)($_GET['days']??30);if(!in_array($days,[7,30,90,365],true))$days=30;
$since=gmdate('Y-m-d H:i:s',time()-$days*86400);
$clicks=$db->prepare('SELECT DATE(c.clicked_at) day,COUNT(*) clicks FROM referral_clicks c JOIN influencer_referral_links l ON l.id=c.referral_link_id WHERE l.influencer_id=:owner AND c.clicked_at>=:since GROUP BY DATE(c.clicked_at) ORDER BY day');$clicks->execute(['owner'=>$id,'since'=>$since]);
$conversions=$db->prepare('SELECT DATE(created_at) day,status,COUNT(*) conversions,COALESCE(SUM(order_amount),0) sales,COALESCE(SUM(commission_amount),0) commission FROM influencer_conversions WHERE influencer_id=:owner AND created_at>=:since GROUP BY DATE(created_at),status ORDER BY day');$conversions->execute(['owner'=>$id,'since'=>$since]);
$ledger=$db->prepare('SELECT id,entry_type,amount,status,reference_type,created_at FROM influencer_ledger_entries WHERE influencer_id=:owner ORDER BY created_at DESC LIMIT 100');$ledger->execute(['owner'=>$id]);
json_response(200,['success'=>true,'days'=>$days,'clicks'=>$clicks->fetchAll(),'conversions'=>$conversions->fetchAll(),'ledger'=>$ledger->fetchAll()]);
