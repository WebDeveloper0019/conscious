<?php
declare(strict_types=1);
require_once __DIR__.'/../../lib/helpers.php';require_once __DIR__.'/../../lib/admin.php';send_cors_headers();if(($_SERVER['REQUEST_METHOD']??'')!=='GET')json_response(405,['success'=>false,'message'=>'Method not allowed.']);$context=require_admin();json_response(200,['success'=>true,'admin'=>public_admin($context['admin']),'permissions'=>principal_permissions($context['principal']),'csrf_token'=>current_csrf_token()]);
