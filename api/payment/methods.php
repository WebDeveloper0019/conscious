<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/razorpay.php';
send_cors_headers();
$principal = require_principal(['customer']); $userId = $principal['id'];
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

$demo = APP_ENV === 'local' && !razorpay_is_configured();
json_response(200, [
    'success' => true,
    'online_available' => $demo || razorpay_is_configured(),
    'gateway' => $demo ? 'local_demo' : (razorpay_is_configured() ? 'razorpay' : 'unavailable'),
    'cash_on_delivery_available' => false,
]);
