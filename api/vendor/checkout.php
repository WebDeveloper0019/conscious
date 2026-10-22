<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
send_cors_headers();

// For local testing without vendor login, we use a mock vendor ID if not logged in.
$principal = current_session();
$vendorId = $principal['principal_id'] ?? '11111111-1111-1111-1111-111111111111'; // Replace with real vendor ID logic

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

$body = get_json_body();
$packageId = trim($body['package_id'] ?? '');
$brief = trim($body['brief'] ?? '');

if (mb_strlen($brief) < 20) {
    json_response(422, ['success' => false, 'message' => 'Brief must be at least 20 characters.']);
}

$db = get_db();

// 1. Verify package
$stmt = $db->prepare('SELECT * FROM influencer_packages WHERE id = :id AND status = "active"');
$stmt->execute(['id' => $packageId]);
$package = $stmt->fetch();

if (!$package) {
    json_response(404, ['success' => false, 'message' => 'Package not available.']);
}

$amount = (float)$package['price_inr'];
$amountInPaise = $amount * 100;

// 2. Create internal order
$orderId = uuid_string();
$db->prepare('INSERT INTO package_orders (id, vendor_id, influencer_id, package_id, brief, amount_inr, status, created_at, updated_at) 
              VALUES (:id, :vid, :inf, :pkg, :brief, :amt, "pending_payment", UTC_TIMESTAMP(), UTC_TIMESTAMP())')
   ->execute([
       'id' => $orderId, 'vid' => $vendorId, 'inf' => $package['influencer_id'],
       'pkg' => $packageId, 'brief' => $brief, 'amt' => $amount
   ]);

// 3. Create Razorpay Order (Mocked for local testing - real code would call Razorpay API)
// In production, you would use cURL to hit https://api.razorpay.com/v1/orders
$razorpayOrderId = 'order_' . bin2hex(random_bytes(8)); 

// Save the Razorpay Order ID to prevent duplicate callbacks
$db->prepare('UPDATE package_orders SET payment_gateway = "razorpay", payment_id = :rzp WHERE id = :id')
   ->execute(['rzp' => $razorpayOrderId, 'id' => $orderId]);

json_response(200, [
    'success' => true,
    'order' => [
        'id' => $orderId,
        'amount_paise' => $amountInPaise,
        'razorpay_order_id' => $razorpayOrderId,
        'package_title' => $package['title']
    ]
]);