<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/helpers.php'; send_cors_headers();
if(($_SERVER['REQUEST_METHOD']??'')!=='POST') json_response(405,['success'=>false,'message'=>'Method not allowed.']);
require_csrf(); clear_auth_cookie(); setcookie(CSRF_COOKIE,'',['expires'=>time()-3600,'path'=>'/','secure'=>secure_cookie(),'httponly'=>false,'samesite'=>'Lax']);
json_response(200,['success'=>true,'message'=>'Signed out successfully.']);
