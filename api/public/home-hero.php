<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
send_cors_headers();
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') json_response(405,['success'=>false,'message'=>'Method not allowed.']);
$rows=get_db()->query('SELECT id,eyebrow,title,description,button_label,button_url,image_url,image_alt FROM home_hero_banners WHERE is_active=1 ORDER BY sort_order ASC,created_at ASC,id ASC LIMIT 20')->fetchAll();
json_response(200,['success'=>true,'banners'=>$rows]);
