<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
send_cors_headers();

// Note: For this to work, your vendor session/auth must be set up. 
// If you don't have vendor auth yet, we will temporarily mock the vendor ID for testing.
$principal = require_principal(['vendor', 'customer', 'admin']); 
$vendorId = $principal['id']; // Assuming vendor session exists
$db = get_db();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'POST') {
    $body = get_json_body();
    $packageId = trim($body['package_id'] ?? '');
    $brief = trim($body['brief'] ?? '');

    if (mb_strlen($brief) < 20) {
        json_response(422, ['success' => false, 'message' => 'Please provide a detailed brief (at least 20 characters).']);
    }

    // 1. Fetch package and verify it is active
    $stmt = $db->prepare('SELECT * FROM influencer_packages WHERE id = :id AND status = "active"');
    $stmt->execute(['id' => $packageId]);
    $package = $stmt->fetch();

    if (!$package) {
        json_response(404, ['success' => false, 'message' => 'Package is not available.']);
    }

    // 2. Create the pending order
    $orderId = uuid_string();
    $amount = (float)$package['price_inr'];

    $db->prepare('INSERT INTO package_orders 
        (id, vendor_id, influencer_id, package_id, brief, amount_inr, status, created_at, updated_at) 
        VALUES (:id, :vid, :inf, :pkg, :brief, :amt, "pending_payment", UTC_TIMESTAMP(), UTC_TIMESTAMP())')
       ->execute([
           'id' => $orderId,
           'vid' => $vendorId,
           'inf' => $package['influencer_id'],
           'pkg' => $packageId,
           'brief' => $brief,
           'amt' => $amount
       ]);

    // 3. Return the order details (Frontend will use this to open Razorpay)
    json_response(201, [
        'success' => true,
        'order' => [
            'id' => $orderId,
            'amount_inr' => $amount,
            'package_title' => $package['title'],
            'status' => 'pending_payment'
        ],
        'message' => 'Order created. Proceed to payment.'
    ]);
}

json_response(405, ['success' => false, 'message' => 'Method not allowed.']);