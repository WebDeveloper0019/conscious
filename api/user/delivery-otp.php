<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';

send_cors_headers();

$payload = require_auth();
$userId  = auth_user_id($payload);
$db      = get_db();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

// List active OTPs for user's orders
$stmt = $db->prepare(
    'SELECT g.id AS group_id, g.group_number, g.status AS group_status,
            g.delivery_otp_expires_at, g.delivery_otp_verified_at,
            g.delivery_otp_generated_at,
            o.order_number, o.id AS order_id,
            v.display_name AS vendor_name
     FROM vendor_order_groups g
     JOIN orders  o ON o.id = g.order_id
     JOIN vendors v ON v.id = g.vendor_id
     WHERE o.user_id = ?
       AND g.delivery_otp_generated_at IS NOT NULL
       AND g.delivery_otp_verified_at IS NULL
       AND g.delivery_otp_expires_at > UTC_TIMESTAMP()
     ORDER BY g.delivery_otp_generated_at DESC
     LIMIT 20'
);
$stmt->execute([$userId]);

$rows = [];
foreach ($stmt->fetchAll() as $r) {
    // We never store plaintext — customer sees the OTP from their notification.
    // This endpoint only shows status so they know an OTP is pending.
    $rows[] = [
        'groupId'      => $r['group_id'],
        'groupNumber'  => $r['group_number'],
        'orderNumber'  => $r['order_number'],
        'orderId'      => (int)$r['order_id'],
        'vendorName'   => $r['vendor_name'],
        'groupStatus'  => $r['group_status'],
        'expiresAt'    => $r['delivery_otp_expires_at'],
        'generatedAt'  => $r['delivery_otp_generated_at'],
    ];
}

json_response(200, [
    'success'  => true,
    'pending'  => $rows,
    'hint'     => 'OTP was sent to your notifications. Check notifications page if you lost it.',
]);