<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/notifications.php';
require_once __DIR__ . '/../lib/audit.php';
require_once __DIR__ . '/../lib/delivery-otp.php';

send_cors_headers();

$principal = require_principal(['vendor']);
$vendorId  = $principal['id'];
$db        = get_db();
$method    = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// ────────── GET: OTP status for a group ──────────
if ($method === 'GET') {
    $groupId = trim((string)($_GET['group_id'] ?? ''));
    if ($groupId === '') json_response(422, ['success' => false, 'message' => 'group_id required.']);

    $stmt = $db->prepare(
        'SELECT g.id, g.group_number, g.status, g.order_id,
                g.delivery_otp_expires_at, g.delivery_otp_attempts,
                g.delivery_otp_verified_at, g.delivery_otp_generated_at,
                o.order_number, o.user_id
         FROM vendor_order_groups g
         JOIN orders o ON o.id = g.order_id
         WHERE g.id = ? AND g.vendor_id = ? LIMIT 1'
    );
    $stmt->execute([$groupId, $vendorId]);
    $g = $stmt->fetch();
    if (!$g) json_response(404, ['success' => false, 'message' => 'Order group not found.']);

    $otpActive = !empty($g['delivery_otp_generated_at'])
        && empty($g['delivery_otp_verified_at'])
        && !empty($g['delivery_otp_expires_at'])
        && strtotime((string)$g['delivery_otp_expires_at']) > time();

    json_response(200, [
        'success' => true,
        'status'  => [
            'groupId'      => $g['id'],
            'groupNumber'  => $g['group_number'],
            'orderNumber'  => $g['order_number'],
            'groupStatus'  => $g['status'],
            'otpRequired'  => delivery_otp_required(),
            'otpActive'    => $otpActive,
            'expiresAt'    => $g['delivery_otp_expires_at'],
            'attempts'     => (int)$g['delivery_otp_attempts'],
            'verifiedAt'   => $g['delivery_otp_verified_at'],
            'generatedAt'  => $g['delivery_otp_generated_at'],
        ],
    ]);
}

// ────────── POST: generate OTP ──────────
if ($method !== 'POST') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

require_csrf();

$body    = get_json_body();
$groupId = trim((string)($body['group_id'] ?? ''));
if ($groupId === '') json_response(422, ['success' => false, 'message' => 'group_id required.']);

$stmt = $db->prepare(
    'SELECT g.id, g.group_number, g.status, g.order_id,
            g.delivery_otp_verified_at, g.delivery_otp_generated_at,
            o.order_number, o.user_id
     FROM vendor_order_groups g
     JOIN orders o ON o.id = g.order_id
     WHERE g.id = ? AND g.vendor_id = ? LIMIT 1'
);
$stmt->execute([$groupId, $vendorId]);
$g = $stmt->fetch();
if (!$g) json_response(404, ['success' => false, 'message' => 'Order group not found.']);

if (!empty($g['delivery_otp_verified_at'])) {
    json_response(409, ['success' => false, 'message' => 'Delivery OTP already verified.']);
}

if (!in_array(strtolower((string)$g['status']), ['dispatched','partially_dispatched','in_transit','ready_to_dispatch'], true)) {
    json_response(409, ['success' => false, 'message' => 'Generate OTP only after the group is dispatched.']);
}

$cd = delivery_otp_can_regenerate($groupId);
if (!$cd['ok']) {
    json_response(429, [
        'success'      => false,
        'message'      => 'Please wait before regenerating.',
        'retryAfter'   => $cd['wait_seconds'],
    ]);
}

$db->beginTransaction();
try {
    $otp = delivery_otp_generate($groupId);

    create_notification(
        'customer',
        (string)$g['user_id'],
        'delivery_otp',
        'Delivery OTP: ' . $otp,
        'Share this OTP with the delivery agent to receive order ' . $g['order_number'] . '. Do NOT share with anyone else.',
        '/customer/orders.html',
        ['group_id' => $groupId, 'expires_in_hours' => (int)delivery_otp_config('delivery_otp_ttl_hours', 24)]
    );

    audit_event('vendor.delivery_otp_generated', 'order_group', $groupId, [
        'order_number' => $g['order_number'],
    ]);

    $db->commit();

    // ⚠️ Debug: return OTP in response ONLY in local env
    $response = [
        'success'    => true,
        'message'    => 'OTP sent to customer.',
        'expiresInHours' => (int)delivery_otp_config('delivery_otp_ttl_hours', 24),
    ];

    if (APP_ENV === 'local') {
        $response['debug_otp'] = $otp;
        $response['_note'] = 'Only shown in local env. Never returned in production.';
    }

    json_response(201, $response);

} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    error_log('Delivery OTP generate failed: ' . $e->getMessage());
    json_response(500, ['success' => false, 'message' => 'Unable to generate OTP.']);
}