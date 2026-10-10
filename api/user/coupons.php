
<?php

require_once __DIR__ . '/../lib/helpers.php';

send_cors_headers();

$principal = require_principal(['customer']);
$db = get_db();
$userId = (string) $principal['id'];

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method !== 'GET') {
    header('Allow: GET');
    json_response(405, [
        'success' => false,
        'message' => 'Method not allowed.'
    ]);
}

try {
    $sql = "
        SELECT
            c.id,
            c.vendor_id,
            c.code,
            c.discount_type,
            c.discount_value,
            c.min_order_amount,
            c.max_discount_amount,
            c.usage_limit,
            c.per_customer_limit,
            c.starts_at,
            c.ends_at,
            c.status,

            (
                SELECT COUNT(*)
                FROM coupon_redemptions cr
                WHERE cr.coupon_id = c.id
            ) AS total_redemptions,

            (
                SELECT COUNT(*)
                FROM coupon_redemptions cr
                WHERE cr.coupon_id = c.id
                  AND cr.user_id = :customer_id
            ) AS customer_redemptions

        FROM coupons c

        WHERE c.status = 'active'
          AND c.starts_at <= UTC_TIMESTAMP()
          AND c.ends_at > UTC_TIMESTAMP()

          AND (
              c.usage_limit IS NULL
              OR (
                  SELECT COUNT(*)
                  FROM coupon_redemptions cr
                  WHERE cr.coupon_id = c.id
              ) < c.usage_limit
          )

          AND (
              SELECT COUNT(*)
              FROM coupon_redemptions cr
              WHERE cr.coupon_id = c.id
                AND cr.user_id = :customer_limit_user
          ) < c.per_customer_limit

        ORDER BY c.ends_at ASC
    ";

    $stmt = $db->prepare($sql);

    $stmt->execute([
        'customer_id' => $userId,
        'customer_limit_user' => $userId
    ]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $coupons = [];
    $totalSavings = 0.0;
    $expiringSoon = 0;

    foreach ($rows as $row) {
        $discountType = (string) $row['discount_type'];
        $discountValue = (float) $row['discount_value'];
        $maxDiscount = $row['max_discount_amount'] !== null
            ? (float) $row['max_discount_amount']
            : null;

        $couponSavings = $discountType === 'percentage'
            ? $maxDiscount
            : $discountValue;

        if ($couponSavings !== null) {
            $totalSavings += $couponSavings;
        }

        $endsAt = strtotime((string) $row['ends_at']);

        if (
            $endsAt !== false
            && $endsAt > time()
            && $endsAt <= time() + (7 * 86400)
        ) {
            $expiringSoon++;
        }

        $coupons[] = [
            'id' => $row['id'],
            'vendorId' => $row['vendor_id'],
            'code' => $row['code'],
            'discountType' => $discountType,
            'discountValue' => $discountValue,
            'minOrderAmount' => (float) $row['min_order_amount'],
            'maxDiscountAmount' => $maxDiscount,
            'usageLimit' => $row['usage_limit'] !== null
                ? (int) $row['usage_limit']
                : null,
            'perCustomerLimit' => (int) $row['per_customer_limit'],
            'startsAt' => $row['starts_at'],
            'endsAt' => $row['ends_at'],
            'remainingUses' => $row['usage_limit'] !== null
                ? max(
                    0,
                    (int) $row['usage_limit']
                    - (int) $row['total_redemptions']
                )
                : null,
            'remainingCustomerUses' => max(
                0,
                (int) $row['per_customer_limit']
                - (int) $row['customer_redemptions']
            )
        ];
    }

    json_response(200, [
        'success' => true,
        'coupons' => $coupons,
        'stats' => [
            'activeCoupons' => count($coupons),
            'totalSavingsAvailable' => round($totalSavings, 2),
            'expiringIn7Days' => $expiringSoon
        ]
    ]);

} catch (Throwable $e) {
    error_log('Customer coupons API error: ' . $e->getMessage());

    json_response(500, [
        'success' => false,
        'message' => 'Unable to load coupons right now.'
    ]);
}
