<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/ai-client.php';
require_once __DIR__ . '/../lib/ai-grounding.php';
send_cors_headers();
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') json_response(405, ['success'=>false,'message'=>'Method not allowed.']);
if (rate_limit('ai-chat|' . request_ip(), 20, 3600)) json_response(429, ['success'=>false,'message'=>'Too many messages. Please try again later.']);
$body=get_json_body(); $message=trim((string)($body['message']??''));
if ($message==='' || mb_strlen($message)>500) json_response(422,['success'=>false,'message'=>'Enter a message of up to 500 characters.']);
$intent=puji_intent($message);
if ($intent==='product') {
    $answer=puji_products(get_db(),$message);
    json_response(200,['success'=>true]+$answer);
}
if ($intent==='event') {
    $answer=puji_events(get_db(),$message);
    json_response(200,['success'=>true]+$answer);
}
if ($intent==='site') {
    json_response(200,['success'=>true]+puji_site($message));
}
if ($intent==='order') {
    $session=current_auth_session(false);
    if (!$session || $session['principal_type']!=='customer') {
        json_response(200,['success'=>true,'reply'=>'Please sign in to see your own order status.','results'=>[
            ['type'=>'link','title'=>'Customer sign in','url'=>puji_base_path().'/portals/modules/customer/auth/login.php']
        ]]);
    }
    $auth=require_auth();
    $answer=puji_orders(get_db(),auth_user_id($auth),$message);
    json_response(200,['success'=>true]+$answer);
}
$system='You are the Conscious India shopping assistant. Be warm, concise and answer in the customer language. Help with artisan-made and conscious products, but never invent prices, stock, delivery promises, policies or sustainability claims. Never request passwords, payment data or sensitive personal information. If facts are unavailable, direct the customer to support.';
$reply=ai_complete($system, $message, 256);
if ($reply===null) json_response(503,['success'=>false,'message'=>'The assistant is unavailable right now. Please use Contact Us for help.']);
json_response(200,['success'=>true,'reply'=>$reply,'results'=>[]]);
