<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/ai-client.php';
send_cors_headers();
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') json_response(405,['success'=>false,'message'=>'Method not allowed.']);
$principal=require_principal(['vendor']);
if (rate_limit('ai-writing|' . $principal['id'], 30, 600)) json_response(429,['success'=>false,'message'=>'Too many requests. Please try again shortly.']);
$body=get_json_body(); $text=trim((string)($body['text']??'')); $action=trim((string)($body['action']??''));
$instructions=[
 'improve'=>'Improve this copy while preserving every factual claim.',
 'shorten'=>'Shorten this copy to under 100 words without losing key facts.',
 'translate_hindi'=>'Translate this copy into natural Hindi.',
 'translate_english'=>'Translate this copy into natural English.',
 'suggest_tags'=>'Return 5 to 8 concise, comma-separated tags based only on this copy.',
 'generate_description'=>'Write a warm 120 to 180 word product description using only these supplied facts.'
];
if ($text==='' || mb_strlen($text)>3000 || !isset($instructions[$action])) json_response(422,['success'=>false,'message'=>'Provide valid text (maximum 3000 characters) and an allowed action.']);
$system='You are a careful copy editor for Conscious India, an Indian artisan and conscious-living marketplace. Never invent origin, certification, impact, material or product facts. Return only the requested copy.';
$result=ai_complete($system, $instructions[$action] . "\n\nSource text:\n" . $text);
if ($result===null) json_response(503,['success'=>false,'message'=>'AI writing help is unavailable right now.']);
json_response(200,['success'=>true,'result'=>$result,'action'=>$action]);
