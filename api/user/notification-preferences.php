<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/audit.php';
require_once __DIR__ . '/../lib/notification-prefs.php';

send_cors_headers();

$payload = require_auth();
$userId  = auth_user_id($payload);
$db      = get_db();
$method  = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// ────────── GET ──────────
if ($method === 'GET') {
    $prefs = user_notification_prefs($userId);

    json_response(200, [
        'success'     => true,
        'preferences' => $prefs,
        'defaults'    => notification_prefs_defaults(),
        'metadata'    => [
            'categories' => notification_prefs_categories(),
            'channels'   => notification_prefs_channels(),
        ],
    ]);
}

// ────────── PUT ──────────
if ($method !== 'PUT' && $method !== 'PATCH') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

require_csrf();

$body = get_json_body();
if (empty($body) || !is_array($body)) {
    json_response(422, ['success' => false, 'message' => 'Preferences payload required.']);
}

$categories = array_keys(notification_prefs_categories());
$channels   = array_keys(notification_prefs_channels());

$prefs  = user_notification_prefs($userId);   // start from current, only override what's sent
$errors = [];

foreach ($body as $cat => $channelsData) {
    if (!in_array($cat, $categories, true)) {
        $errors[] = "Unknown category: {$cat}";
        continue;
    }
    if (!is_array($channelsData)) {
        $errors[] = "Category '{$cat}' must be an object.";
        continue;
    }
    foreach ($channelsData as $ch => $val) {
        if (!in_array($ch, $channels, true)) {
            $errors[] = "Unknown channel '{$ch}' in category '{$cat}'.";
            continue;
        }
        if (!is_bool($val) && !in_array($val, [0, 1, '0', '1'], true)) {
            $errors[] = "Channel '{$ch}' in '{$cat}' must be boolean.";
            continue;
        }
        $prefs[$cat][$ch] = (bool)$val;
    }
}

if ($errors) {
    json_response(422, ['success' => false, 'message' => 'Validation failed.', 'errors' => $errors]);
}

// Force certain safety-critical combos? (optional)
// For security category, prevent disabling all channels? Skip — user's choice.

try {
    $json = json_encode($prefs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    // Also sync legacy booleans so old code paths keep working
    $legacy = [
        'order_updates'     => (int)($prefs['order_updates']['email'] || $prefs['order_updates']['in_app']),
        'offers_promotions' => (int)($prefs['promotions']['email'] || $prefs['promotions']['in_app']),
        'wishlist_alerts'   => (int)($prefs['wishlist_alerts']['email'] || $prefs['wishlist_alerts']['in_app']),
        'newsletter'        => (int)$prefs['newsletter']['email'],
    ];

    $db->prepare(
        'INSERT INTO user_preferences
           (user_id, order_updates, offers_promotions, wishlist_alerts, newsletter,
            notification_settings_json, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, UTC_TIMESTAMP())
         ON DUPLICATE KEY UPDATE
           order_updates     = VALUES(order_updates),
           offers_promotions = VALUES(offers_promotions),
           wishlist_alerts   = VALUES(wishlist_alerts),
           newsletter        = VALUES(newsletter),
           notification_settings_json = VALUES(notification_settings_json),
           updated_at        = UTC_TIMESTAMP()'
    )->execute([
        $userId,
        $legacy['order_updates'],
        $legacy['offers_promotions'],
        $legacy['wishlist_alerts'],
        $legacy['newsletter'],
        $json,
    ]);

    audit_event('customer.notification_prefs_updated', 'user', $userId, [
        'categories_updated' => array_keys($body),
    ]);

    json_response(200, [
        'success'     => true,
        'message'     => 'Notification preferences saved.',
        'preferences' => $prefs,
    ]);

} catch (Throwable $e) {
    error_log('Notification prefs save failed: ' . $e->getMessage());
    json_response(500, ['success' => false, 'message' => 'Unable to save preferences.']);
}