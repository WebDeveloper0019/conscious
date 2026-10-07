<?php
declare(strict_types=1);
require_once __DIR__.'/../../lib/helpers.php';require_once __DIR__.'/../../lib/vendor.php';
send_cors_headers();if(($_SERVER['REQUEST_METHOD']??'')!=='GET')json_response(405,['success'=>false,'message'=>'Method not allowed.']);
$context=require_vendor(false);json_response(200,['success'=>true,'vendor'=>public_vendor($context['vendor']),'csrf_token'=>current_csrf_token(),'redirect'=>vendor_next_route($context['vendor'])]);
