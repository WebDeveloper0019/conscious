<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
require_once __DIR__.'/../lib/admin.php';
send_cors_headers();
$context=require_admin('content.manage');
$db=get_db();
$method=$_SERVER['REQUEST_METHOD']??'GET';
if ($method==='GET') {
    $rows=$db->query('SELECT id,eyebrow,title,description,button_label,button_url,image_url,image_alt,sort_order,is_active,created_at,updated_at FROM home_hero_banners ORDER BY sort_order ASC,created_at ASC,id ASC')->fetchAll();
    json_response(200,['success'=>true,'banners'=>$rows]);
}
if (!in_array($method,['POST','PUT','DELETE'],true)) json_response(405,['success'=>false,'message'=>'Method not allowed.']);
require_csrf();
$body=get_json_body();
$id=trim((string)($body['id']??''));
if ($method==='DELETE') {
    if (!preg_match('/^[a-f0-9-]{36}$/i',$id)) json_response(422,['success'=>false,'message'=>'Select a valid banner to remove.']);
    $stmt=$db->prepare('DELETE FROM home_hero_banners WHERE id=:id');$stmt->execute(['id'=>$id]);
    if ($stmt->rowCount()===0) json_response(404,['success'=>false,'message'=>'Banner not found.']);
    audit_event('admin.home_hero_deleted','home_hero_banner',$id,[],['type'=>'admin','id'=>$context['admin']['id']]);
    json_response(200,['success'=>true,'message'=>'Banner removed.']);
}
if ($method==='PUT' && !preg_match('/^[a-f0-9-]{36}$/i',$id)) json_response(422,['success'=>false,'message'=>'Select a valid banner to edit.']);
$fields=[];
foreach (['eyebrow'=>90,'title'=>150,'description'=>360,'button_label'=>45,'button_url'=>500,'image_url'=>500,'image_alt'=>180] as $field=>$limit) {
    $value=trim((string)($body[$field]??''));
    if ($value===''||mb_strlen($value)>$limit) json_response(422,['success'=>false,'message'=>ucwords(str_replace('_',' ',$field)).' is required and must be under '.$limit.' characters.']);
    $fields[$field]=$value;
}
$url=$fields['button_url'];
if (!preg_match('~^/conscious(?:/[A-Za-z0-9/_?=&%#.+-]*)?$~',$url) && !preg_match('~^https://[A-Za-z0-9.-]+(?::443)?(?:/[A-Za-z0-9/_?=&%#.+~-]*)?$~',$url)) json_response(422,['success'=>false,'message'=>'Button link must be a Conscious India path or a secure HTTPS URL.']);
$image=$fields['image_url'];
$allowedSeed=['home-sustainable-hero.png','eco-scene-1.png','eco-scene-2.png','eco-scene-3.png','eco-scene-4.png'];
$isSeed=in_array($image,array_map(fn($name)=>'/conscious/assets/images/'.$name,$allowedSeed),true);
$isUpload=preg_match('~^/conscious/assets/images/hero-banners/[a-f0-9]{40}\.(?:jpg|png|webp)$~',$image)===1;
if (!$isSeed && !$isUpload) json_response(422,['success'=>false,'message'=>'Upload a banner image or select a provided image.']);
if ($isUpload && !is_file(__DIR__.'/../../'.substr($image,strlen('/conscious/')))) json_response(422,['success'=>false,'message'=>'The banner image file is missing.']);
$order=filter_var($body['sort_order']??100,FILTER_VALIDATE_INT,['options'=>['min_range'=>0,'max_range'=>999]]);
if ($order===false) json_response(422,['success'=>false,'message'=>'Display order must be from 0 to 999.']);
$fields['sort_order']=$order;
$fields['is_active']=!empty($body['is_active'])?1:0;
if ($method==='POST') {
    $id=uuid_string();$fields['id']=$id;$fields['admin']=$context['admin']['id'];
    $db->prepare('INSERT INTO home_hero_banners (id,eyebrow,title,description,button_label,button_url,image_url,image_alt,sort_order,is_active,created_by) VALUES (:id,:eyebrow,:title,:description,:button_label,:button_url,:image_url,:image_alt,:sort_order,:is_active,:admin)')->execute($fields);
    audit_event('admin.home_hero_created','home_hero_banner',$id,[],['type'=>'admin','id'=>$context['admin']['id']]);
    json_response(201,['success'=>true,'message'=>'Banner added.','id'=>$id]);
}
$fields['id']=$id;
$stmt=$db->prepare('UPDATE home_hero_banners SET eyebrow=:eyebrow,title=:title,description=:description,button_label=:button_label,button_url=:button_url,image_url=:image_url,image_alt=:image_alt,sort_order=:sort_order,is_active=:is_active WHERE id=:id');$stmt->execute($fields);
$exists=$db->prepare('SELECT 1 FROM home_hero_banners WHERE id=:id');$exists->execute(['id'=>$id]);
if (!$exists->fetchColumn()) json_response(404,['success'=>false,'message'=>'Banner not found.']);
audit_event('admin.home_hero_updated','home_hero_banner',$id,[],['type'=>'admin','id'=>$context['admin']['id']]);
json_response(200,['success'=>true,'message'=>'Banner updated.']);
