<?php
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
send_cors_headers();
$payload=require_auth(); $userId=auth_user_id($payload); $db=get_db();

if($_SERVER['REQUEST_METHOD']!=='GET') json_response(405,['success'=>false,'message'=>'Method not allowed.']);

$stmt=$db->prepare('SELECT COUNT(*) FROM orders WHERE user_id=:user'); $stmt->execute(['user'=>$userId]); $orderCount=(int)$stmt->fetchColumn();
$stmt=$db->prepare("SELECT COUNT(*) FROM orders WHERE user_id=:user AND status='delivered'"); $stmt->execute(['user'=>$userId]); $delivered=(int)$stmt->fetchColumn();
$stmt=$db->prepare('SELECT COUNT(*) FROM wishlist_items WHERE user_id=:user'); $stmt->execute(['user'=>$userId]); $wishlist=(int)$stmt->fetchColumn();
$stmt=$db->prepare('SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE user_id=:user'); $stmt->execute(['user'=>$userId]); $spent=(float)$stmt->fetchColumn();
$stmt=$db->prepare('SELECT COUNT(*) FROM user_addresses WHERE user_id=:user'); $stmt->execute(['user'=>$userId]); $addresses=(int)$stmt->fetchColumn();

json_response(200,['success'=>true,'stats'=>['orders'=>$orderCount,'deliveredOrders'=>$delivered,'wishlistItems'=>$wishlist,'totalSpent'=>$spent,'addresses'=>$addresses]]);
