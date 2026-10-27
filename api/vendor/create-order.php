<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
send_cors_headers();

$principal = require_principal(['vendor']);
$vendorId = $principal['id']; 

$body = get_json_body();
$packageId = trim($body['package_id'] ?? '');
$brief = trim($body['brief'] ?? '');

if (mb_strlen($brief) < 20) json_response(422, ['success' => false, 'message' => 'Brief must be 20+ characters.']);

$db = get_db();
$db->beginTransaction();

try {
    $stmt = $db->prepare('SELECT * FROM influencer_packages WHERE id = :id AND status = "active" FOR UPDATE');
    $stmt->execute(['id' => $packageId]);
    $package = $stmt->fetch();

    if (!$package) throw new Exception('Package is not active or does not exist.');

    $capStmt = $db->prepare('SELECT COUNT(*) FROM service_orders WHERE package_id = :pid AND status IN ("paid", "in_progress", "scheduled", "delivered")');
    $capStmt->execute(['pid' => $packageId]);
    $currentOrders = (int)$capStmt->fetchColumn();

    if ($currentOrders >= (int)$package['capacity']) {
        throw new Exception('This creator has reached their maximum capacity for this package.');
    }

    $baseAmount = (float)$package['price_inr'];
    $gstAmount = $baseAmount * 0.18;
    $totalAmount = $baseAmount + $gstAmount;
    $totalPaise = (int)round($totalAmount * 100);

    $orderId = uuid_string();
    $orderNumber = 'ORD-' . strtoupper(bin2hex(random_bytes(4)));
    
    $db->prepare('INSERT INTO service_orders (id, order_number, vendor_id, influencer_id, package_id, brief, base_amount, gst_amount, total_amount, status) 
                  VALUES (:id, :num, :vid, :inf, :pkg, :brief, :base, :gst, :total, "pending_payment")')
       ->execute([
           'id' => $orderId, 'num' => $orderNumber, 'vid' => $vendorId, 
           'inf' => $package['influencer_id'], 'pkg' => $packageId, 'brief' => $brief,
           'base' => $baseAmount, 'gst' => $gstAmount, 'total' => $totalAmount
       ]);

    $db->prepare('INSERT INTO service_order_snapshots (order_id, package_title, deliverables_json, platforms_json, turnaround_days, usage_rights, vendor_requirements, cancellation_terms) 
                  VALUES (:oid, :title, :deliv, :plat, :turn, :usage, :vendor, :cancel)')
       ->execute([
           'oid' => $orderId, 'title' => $package['title'], 'deliv' => $package['deliverables_json'], 
           'plat' => $package['platforms_json'], 'turn' => $package['turnaround_days'], 
           'usage' => $package['usage_rights'], 'vendor' => $package['vendor_requirements'], 
           'cancel' => $package['cancellation_terms']
       ]);

    $rzpKey = getenv('RAZORPAY_KEY_ID') ?: '';
    $rzpSecret = getenv('RAZORPAY_KEY_SECRET') ?: '';
    
    if (empty($rzpKey) || empty($rzpSecret)) {
        $razorpayOrderId = 'order_mock_' . bin2hex(random_bytes(8)); 
        $isMock = true;
    } else {
        $ch = curl_init('https://api.razorpay.com/v1/orders');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => $rzpKey . ':' . $rzpSecret,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'amount' => $totalPaise,
                'currency' => 'INR',
                'receipt' => $orderNumber,
                'notes' => ['internal_order_id' => $orderId]
            ]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json']
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $rzpData = json_decode($response, true);
            $razorpayOrderId = $rzpData['id'];
            $isMock = false;
        } else {
            throw new Exception('Razorpay order creation failed. Check API keys.');
        }
    }

    $db->prepare('INSERT INTO service_payments (id, order_id, razorpay_order_id, amount_inr, status) 
                  VALUES (:id, :oid, :rzp, :amt, "created")')
       ->execute(['id' => uuid_string(), 'oid' => $orderId, 'rzp' => $razorpayOrderId, 'amt' => $totalAmount]);

    $db->commit();


    $idempotencyKey = trim($body['idempotency_key'] ?? '');
if (empty($idempotencyKey)) {
    json_response(422, ['success' => false, 'message' => 'Idempotency key required.']);
}

// Check if this key already exists before doing anything else
$checkStmt = $db->prepare('SELECT id FROM service_payments WHERE idempotency_key = :key');
$checkStmt->execute(['key' => $idempotencyKey]);
if ($existing = $checkStmt->fetch()) {
    // Return the existing order instead of creating a new one
    json_response(200, ['success' => true, 'message' => 'Order already exists.']);
}

    json_response(200, [
        'success' => true,
        'is_mock_payment' => $isMock,
        'order' => [
            'id' => $orderId,
            'razorpay_order_id' => $razorpayOrderId,
            'amount_paise' => $totalPaise,
            'razorpay_key' => $rzpKey ?: 'mock_key'
        ]
    ]);

} catch (Throwable $e) {
    $db->rollBack();
    json_response(400, ['success' => false, 'message' => 'An error occurred while processing your order.']);
}