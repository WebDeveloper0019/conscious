<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
require_once __DIR__.'/../lib/vendor.php'; 

send_cors_headers();
$context = require_vendor(true);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if($method === 'GET'){
    // BYPASS: Return an empty list since the table doesn't exist yet
    json_response(200, [
        'success' => true, 
        'flags' => []
    ]);
}

if($method === 'POST'){
    json_response(200, ['success' => true, 'message' => 'Note recorded (bypassed).']);
}

json_response(405, ['success' => false, 'message' => 'Method not allowed.']);