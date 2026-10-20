<?php
declare(strict_types=1);
require_once __DIR__ . '/helpers.php';

function notification_prefs_categories(): array
{
    return [
        'order_updates' => [
            'label'       => 'Order Updates',
            'description' => 'Shipping, delivery, cancellation, refunds',
            'icon'        => 'fa-box',
        ],
        'promotions' => [
            'label'       => 'Offers & Promotions',
            'description' => 'Flash sales, coupons, discounts',
            'icon'        => 'fa-tag',
        ],
        'wishlist_alerts' => [
            'label'       => 'Wishlist Alerts',
            'description' => 'Price drops, back-in-stock',
            'icon'        => 'fa-heart',
        ],
        'newsletter' => [
            'label'       => 'Newsletter',
            'description' => 'Weekly stories, artisan features',
            'icon'        => 'fa-newspaper',
        ],
        'security' => [
            'label'       => 'Security Alerts',
            'description' => 'Login alerts, password changes',
            'icon'        => 'fa-shield',
        ],
    ];
}

function notification_prefs_channels(): array
{
    return [
        'email'  => ['label' => 'Email',  'icon' => 'fa-envelope'],
        'sms'    => ['label' => 'SMS',    'icon' => 'fa-comment-sms'],
        'push'   => ['label' => 'Push',   'icon' => 'fa-mobile'],
        'in_app' => ['label' => 'In-App', 'icon' => 'fa-bell'],
    ];
}

function notification_prefs_defaults(): array
{
    return [
        'order_updates'   => ['email' => true,  'sms' => true,  'push' => true,  'in_app' => true],
        'promotions'      => ['email' => true,  'sms' => false, 'push' => false, 'in_app' => true],
        'wishlist_alerts' => ['email' => true,  'sms' => false, 'push' => true,  'in_app' => true],
        'newsletter'      => ['email' => false, 'sms' => false, 'push' => false, 'in_app' => false],
        'security'        => ['email' => true,  'sms' => true,  'push' => true,  'in_app' => true],
    ];
}

/**
 * Map internal notification type → preference category.
 */
function notification_category_for_type(string $type): string
{
    $map = [
        // Order updates
        'order_update'           => 'order_updates',
        'order_cancelled'        => 'order_updates',
        'order_placed'           => 'order_updates',
        'order_shipped'          => 'order_updates',
        'order_delivered'        => 'order_updates',
        'refund_update'          => 'order_updates',
        'refund_failed_permanent'=> 'order_updates',
        'delivery_otp'           => 'order_updates',
        'return_update'          => 'order_updates',
        'return_requested'       => 'order_updates',
        'reward_earned'          => 'order_updates',
        'reward_refunded'        => 'order_updates',
        'product_answer'         => 'order_updates',

        // Promotions
        'promo'                  => 'promotions',
        'offer'                  => 'promotions',
        'coupon'                 => 'promotions',
        'reward_expiring'        => 'promotions',
        'reward_expired'         => 'promotions',

        // Wishlist
        'back_in_stock'          => 'wishlist_alerts',
        'price_drop'             => 'wishlist_alerts',

        // Newsletter
        'newsletter'             => 'newsletter',
        'stories'                => 'newsletter',

        // Security
        'login_alert'            => 'security',
        'password_changed'       => 'security',
        'security_alert'         => 'security',
        'session_revoked'        => 'security',
    ];
    return $map[$type] ?? 'order_updates';
}

/**
 * Get the full preference map for a user (merged with defaults).
 */
function user_notification_prefs(string $userId): array
{
    $defaults = notification_prefs_defaults();
    $db = get_db();
    $stmt = $db->prepare('SELECT notification_settings_json FROM user_preferences WHERE user_id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $raw = $stmt->fetchColumn();
    if ($raw === false || $raw === null) return $defaults;

    $parsed = json_decode((string)$raw, true);
    if (!is_array($parsed)) return $defaults;

    // Merge with defaults so missing keys don't break
    $out = $defaults;
    foreach ($defaults as $cat => $channels) {
        foreach ($channels as $ch => $def) {
            if (isset($parsed[$cat][$ch])) {
                $out[$cat][$ch] = (bool)$parsed[$cat][$ch];
            }
        }
    }
    return $out;
}

/**
 * Check if a user wants a given notification on a given channel.
 *
 *   user_wants_notification($userId, 'order_updates', 'email')
 *   user_wants_notification($userId, 'promotions', 'push')
 *
 * Or with notification type:
 *   user_wants_notification_for_type($userId, 'delivery_otp', 'sms')
 */
function user_wants_notification(string $userId, string $category, string $channel): bool
{
    $prefs = user_notification_prefs($userId);
    return !empty($prefs[$category][$channel]);
}

function user_wants_notification_for_type(string $userId, string $notificationType, string $channel): bool
{
    $category = notification_category_for_type($notificationType);
    return user_wants_notification($userId, $category, $channel);
}