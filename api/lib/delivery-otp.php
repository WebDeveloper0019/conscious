<?php
declare(strict_types=1);
require_once __DIR__ . '/helpers.php';

function delivery_otp_config(string $key, $default = null)
{
    static $cache = [];
    if (array_key_exists($key, $cache)) return $cache[$key];
    $stmt = get_db()->prepare('SELECT value_json FROM platform_settings WHERE setting_key = ?');
    $stmt->execute([$key]);
    $raw = $stmt->fetchColumn();
    if ($raw === false) return $cache[$key] = $default;
    $decoded = json_decode((string)$raw, true);
    return $cache[$key] = ($decoded['value'] ?? $default);
}

function delivery_otp_required(): bool
{
    return (bool)delivery_otp_config('delivery_otp_required', true);
}

/**
 * Generate a fresh 6-digit OTP for a vendor group.
 * Returns the plaintext OTP (caller must NOT log on prod).
 */
function delivery_otp_generate(string $groupId): string
{
    $db = get_db();
    $otp  = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $hash = hash_hmac('sha256', $otp, APP_KEY);
    $ttl  = (int)delivery_otp_config('delivery_otp_ttl_hours', 24);

    $db->prepare(
        'UPDATE vendor_order_groups
         SET delivery_otp_hash = ?,
             delivery_otp_expires_at = DATE_ADD(UTC_TIMESTAMP(), INTERVAL ? HOUR),
             delivery_otp_attempts = 0,
             delivery_otp_verified_at = NULL,
             delivery_otp_generated_at = UTC_TIMESTAMP(),
             updated_at = UTC_TIMESTAMP()
         WHERE id = ?'
    )->execute([$hash, $ttl, $groupId]);

    return $otp;
}

/**
 * Verify an OTP. Returns ['ok' => bool, 'reason' => ?string, 'attempts_left' => ?int]
 */
function delivery_otp_verify(string $groupId, string $plainOtp): array
{
    $db = get_db();
    $stmt = $db->prepare(
        'SELECT delivery_otp_hash, delivery_otp_expires_at, delivery_otp_attempts,
                delivery_otp_verified_at
         FROM vendor_order_groups WHERE id = ? FOR UPDATE'
    );
    $stmt->execute([$groupId]);
    $row = $stmt->fetch();

    if (!$row) return ['ok' => false, 'reason' => 'not_found'];

    if (!empty($row['delivery_otp_verified_at'])) {
        return ['ok' => true, 'already' => true];
    }
    if (empty($row['delivery_otp_hash'])) {
        return ['ok' => false, 'reason' => 'no_otp'];
    }
    if ($row['delivery_otp_expires_at'] && strtotime((string)$row['delivery_otp_expires_at']) < time()) {
        return ['ok' => false, 'reason' => 'expired'];
    }

    $maxAttempts = (int)delivery_otp_config('delivery_otp_max_attempts', 5);
    if ((int)$row['delivery_otp_attempts'] >= $maxAttempts) {
        return ['ok' => false, 'reason' => 'attempts_exceeded'];
    }

    $expected = hash_hmac('sha256', $plainOtp, APP_KEY);
    if (!hash_equals((string)$row['delivery_otp_hash'], $expected)) {
        $db->prepare('UPDATE vendor_order_groups SET delivery_otp_attempts = delivery_otp_attempts + 1 WHERE id = ?')
           ->execute([$groupId]);
        $used = (int)$row['delivery_otp_attempts'] + 1;
        return [
            'ok'            => false,
            'reason'        => 'invalid',
            'attempts_left' => max(0, $maxAttempts - $used),
        ];
    }

    $db->prepare(
        'UPDATE vendor_order_groups
         SET delivery_otp_verified_at = UTC_TIMESTAMP(),
             delivery_otp_hash = NULL,
             updated_at = UTC_TIMESTAMP()
         WHERE id = ?'
    )->execute([$groupId]);

    return ['ok' => true];
}

/**
 * Cooldown check for regenerate.
 */
function delivery_otp_can_regenerate(string $groupId): array
{
    $db = get_db();
    $stmt = $db->prepare('SELECT delivery_otp_generated_at FROM vendor_order_groups WHERE id = ?');
    $stmt->execute([$groupId]);
    $last = $stmt->fetchColumn();
    if (!$last) return ['ok' => true];

    $cooldownMin = (int)delivery_otp_config('delivery_otp_regenerate_cooldown_min', 5);
    $elapsed = time() - strtotime((string)$last);
    if ($elapsed < $cooldownMin * 60) {
        return ['ok' => false, 'wait_seconds' => $cooldownMin * 60 - $elapsed];
    }
    return ['ok' => true];
}