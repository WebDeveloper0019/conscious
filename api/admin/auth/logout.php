<?php
declare(strict_types=1);
require_once __DIR__.'/../../lib/helpers.php';send_cors_headers();if(($_SERVER['REQUEST_METHOD']??'')!=='POST')json_response(405,['success'=>false,'message'=>'Method not allowed.']);$principal=require_principal(['admin']);audit_event('admin.logout','admin',$principal['id']);revoke_current_session('logout');json_response(200,['success'=>true,'message'=>'Signed out successfully.']);
