<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
require_once __DIR__.'/../lib/india.php';
send_cors_headers();
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') json_response(405,['success'=>false,'message'=>'Method not allowed.']);
json_response(200,['success'=>true,'states'=>india_states_and_uts()]);
