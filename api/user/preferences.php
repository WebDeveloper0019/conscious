<?php
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
send_cors_headers();
$payload=require_auth(); $userId=auth_user_id($payload); $db=get_db();

if($_SERVER['REQUEST_METHOD']==='GET'){
  $stmt=$db->prepare('SELECT * FROM user_preferences WHERE user_id=:user LIMIT 1'); $stmt->execute(['user'=>$userId]);
  $p=$stmt->fetch();
  if(!$p){$p=['order_updates'=>1,'offers_promotions'=>1,'wishlist_alerts'=>1,'newsletter'=>0];}
  json_response(200,['success'=>true,'preferences'=>[
    'orderUpdates'=>(bool)$p['order_updates'],'offersPromotions'=>(bool)$p['offers_promotions'],
    'wishlistAlerts'=>(bool)$p['wishlist_alerts'],'newsletter'=>(bool)$p['newsletter']
  ]]);
}
if($_SERVER['REQUEST_METHOD']!=='PUT' && $_SERVER['REQUEST_METHOD']!=='PATCH') json_response(405,['success'=>false,'message'=>'Method not allowed.']);
$b=get_json_body();
$values=[
 'order_updates'=>!empty($b['orderUpdates'])?1:0,
 'offers_promotions'=>!empty($b['offersPromotions'])?1:0,
 'wishlist_alerts'=>!empty($b['wishlistAlerts'])?1:0,
 'newsletter'=>!empty($b['newsletter'])?1:0
];
$stmt=$db->prepare('INSERT INTO user_preferences (user_id,order_updates,offers_promotions,wishlist_alerts,newsletter,updated_at)
 VALUES (:user,:a,:b,:c,:d,:updated)
 ON DUPLICATE KEY UPDATE order_updates=VALUES(order_updates),offers_promotions=VALUES(offers_promotions),
 wishlist_alerts=VALUES(wishlist_alerts),newsletter=VALUES(newsletter),updated_at=VALUES(updated_at)');
$stmt->execute([
 'user'=>$userId,'a'=>$values['order_updates'],'b'=>$values['offers_promotions'],
 'c'=>$values['wishlist_alerts'],'d'=>$values['newsletter'],'updated'=>date('Y-m-d H:i:s')
]);
json_response(200,['success'=>true,'message'=>'Preferences saved successfully.','preferences'=>[
 'orderUpdates'=>(bool)$values['order_updates'],'offersPromotions'=>(bool)$values['offers_promotions'],
 'wishlistAlerts'=>(bool)$values['wishlist_alerts'],'newsletter'=>(bool)$values['newsletter']
]]);
