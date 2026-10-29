<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/ai-client.php';
send_cors_headers();
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') json_response(405, ['success'=>false,'message'=>'Method not allowed.']);
if (rate_limit('ai-chat|' . request_ip(), 20, 3600)) json_response(429, ['success'=>false,'message'=>'Too many messages. Please try again later.']);
$body=get_json_body(); $message=trim((string)($body['message']??''));
if ($message==='' || mb_strlen($message)>500) json_response(422,['success'=>false,'message'=>'Enter a message of up to 500 characters.']);
$system='You are the Conscious India shopping assistant. Be warm, concise and answer in the customer language. Help with artisan-made and conscious products, but never invent prices, stock, delivery promises, policies or sustainability claims. Never request passwords, payment data or sensitive personal information. If facts are unavailable, direct the customer to support.';
$reply=ai_complete($system, $message, 256);
if ($reply===null) json_response(503,['success'=>false,'message'=>'The assistant is unavailable right now. Please use Contact Us for help.']);
json_response(200,['success'=>true,'reply'=>$reply]);
