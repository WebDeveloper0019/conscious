<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
send_cors_headers();
$context = require_vendor(false);
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
$paymentId = trim((string)($_GET['payment_id'] ?? ''));
if (!preg_match('/^[a-f0-9-]{36}$/i', $paymentId)) json_response(422, ['success' => false, 'message' => 'Valid service payment id is required.']);
$stmt = get_db()->prepare(
    'SELECT p.id AS payment_id,p.status AS payment_status,p.provider_order_id,
            o.id AS service_order_id,o.order_number,o.status AS order_status,o.reservation_expires_at
     FROM influencer_service_payments p
     JOIN influencer_service_orders o ON o.id=p.order_id
     WHERE p.id=:id AND o.vendor_id=:vendor LIMIT 1'
);
$stmt->execute(['id' => $paymentId, 'vendor' => $context['vendor']['id']]);
$row = $stmt->fetch();
if (!$row) json_response(404, ['success' => false, 'message' => 'Service payment not found.']);
$row['reservation_expired'] = strtotime((string)$row['reservation_expires_at']) <= time();
json_response(200, ['success' => true] + $row);
