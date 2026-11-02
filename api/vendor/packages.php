<?php
declare(strict_types=1);

// Include the init file (or the individual files)
require_once __DIR__.'/../lib/helpers.php';
require_once __DIR__.'/../lib/db.php';
require_once __DIR__.'/../lib/vendor.php'; 

send_cors_headers();
$context = require_vendor(true);

// Since you haven't built the packages feature yet, return an empty list.
json_response(200, [
    'success' => true,
    'data' => [] // Empty array so the frontend shows "0 packages" instead of crashing
]);