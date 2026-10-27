<?php
// tests/simulate-webhook.php
// Run this in your terminal to simulate a successful Razorpay payment!

$orderId = $argv[1] ?? ''; // Pass the razorpay_order_id as an argument
if (!$orderId) {
    die("Usage: php simulate-webhook.php order_mock_xxxxx\n");
}

$webhookSecret = 'YOUR_WEBHOOK_SECRET_FROM_ENV'; // Put your local webhook secret here
$payload = json_encode([
    'event' => 'payment.captured',
    'payload' => [
        'payment' => [
            'entity' => [
                'id' => 'pay_mock_123',
                'order_id' => $orderId,
                'amount' => 118000, // Match your totalPaise
                'currency' => 'INR'
            ]
        ]
    ]
]);

$signature = hash_hmac('sha256', $payload, $webhookSecret);

// Send to your local webhook endpoint
$ch = curl_init('http://localhost/conscious/api/webhooks/razorpay.php');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'X-Razorpay-Signature: ' . $signature,
        'X-Razorpay-Event-Id: evt_mock_' . rand(1000, 9999)
    ]
]);
$response = curl_exec($ch);
echo "Response: $response\n";