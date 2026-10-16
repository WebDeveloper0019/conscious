<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/notifications.php';
require_once __DIR__ . '/../lib/audit.php';
require_once __DIR__ . '/../lib/delivery-otp.php';
require_once __DIR__ . '/../lib/fulfilment-safe.php';

send_cors_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

$principal = require_principal(['vendor']);
$vendorId  = $principal['id'];
require_csrf();

if (rate_limit('verify-otp:' . $vendorId, 30, 300)) {
    json_response(429, ['success' => false, 'message' => 'Too many verification attempts.']);
}

$body    = get_json_body();
$groupId = trim((string)($body['group_id'] ?? ''));
$otp     = trim((string)($body['otp'] ?? ''));

if ($groupId === '' || !preg_match('/^[0-9]{6}$/', $otp)) {
    json_response(422, ['success' => false, 'message' => 'group_id and 6-digit OTP required.']);
}

$db = get_db();

$stmt = $db->prepare(
    'SELECT g.id, g.group_number, g.status, g.order_id,
            o.order_number, o.user_id
     FROM vendor_order_groups g
     JOIN orders o ON o.id = g.order_id
     WHERE g.id = ? AND g.vendor_id = ? LIMIT 1'
);
$stmt->execute([$groupId, $vendorId]);
$g = $stmt->fetch();
if (!$g) json_response(404, ['success' => false, 'message' => 'Order group not found.']);

$db->beginTransaction();
try {
    $result = delivery_otp_verify($groupId, $otp);

    if (!$result['ok']) {
        $db->commit();
        $msg = [
            'invalid'           => 'Incorrect OTP.',
            'expired'           => 'OTP expired. Please regenerate.',
            'attempts_exceeded' => 'Maximum attempts reached. Please regenerate OTP.',
            'no_otp'            => 'No OTP generated for this group.',
            'not_found'         => 'Order group not found.',
        ][$result['reason']] ?? 'OTP verification failed.';

        json_response(409, [
            'success'       => false,
            'message'       => $msg,
            'reason'        => $result['reason'],
            'attemptsLeft'  => $result['attempts_left'] ?? null,
        ]);
    }

    // ── Mark all packages of this group as delivered ──
    $upd = $db->prepare(
        'UPDATE shipment_packages
         SET status = "delivered", updated_at = UTC_TIMESTAMP()
         WHERE vendor_group_id = ? AND status NOT IN ("delivered","returned_to_vendor","return_in_transit")'
    );
    $upd->execute([$groupId]);
    $packagesUpdated = $upd->rowCount();

    audit_event('vendor.delivery_otp_verified', 'order_group', $groupId, [
        'order_number'      => $g['order_number'],
        'packages_updated'  => $packagesUpdated,
    ]);

    $db->commit();

    // ── Fire fulfilment sync (post-commit) — updates group to delivered,
    //    ledger eligible, rewards earn, order completion, influencer
    //    conversions, all side effects ──
    try {
        sync_group_from_packages_safe($groupId);
    } catch (Throwable $e) {
        error_log('Post-OTP sync failed for group ' . $groupId . ': ' . $e->getMessage());
    }

    json_response(200, [
        'success'   => true,
        'message'   => 'Delivery verified. Order marked as delivered.',
        'groupId'   => $groupId,
        'alreadyVerified' => !empty($result['already']),
    ]);

} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    error_log('Delivery OTP verify failed: ' . $e->getMessage());
    json_response(500, ['success' => false, 'message' => 'Unable to verify OTP.']);
}