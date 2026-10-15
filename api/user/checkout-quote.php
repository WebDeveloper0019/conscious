<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
require_once __DIR__.'/../lib/commerce.php';
require_once __DIR__.'/../lib/rewards.php';

send_cors_headers();
$auth = require_auth();
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') json_response(405, ['success' => false, 'message' => 'Method not allowed.']);

$body = get_json_body();
$userId = auth_user_id($auth);
$addressId = trim((string)($body['address_id'] ?? ''));
$couponCode = strtoupper(trim((string)($body['coupon_code'] ?? '')));
$pointsToRedeem = max(0, (int)($body['reward_points_redeem'] ?? 0));
$db = get_db();

$addressStmt = $db->prepare('SELECT * FROM user_addresses WHERE id=:id AND user_id=:user');
$addressStmt->execute(['id' => $addressId, 'user' => $userId]);
$address = $addressStmt->fetch();
if (!$address) json_response(404, ['success' => false, 'message' => 'Delivery address not found.']);

$cart = cart_details($userId);
if (!$cart['items']) json_response(422, ['success' => false, 'message' => 'Your cart is empty.']);

$db->beginTransaction();
try {
    $old = $db->prepare('SELECT id FROM checkout_quotes WHERE user_id=:user AND status="open" FOR UPDATE');
    $old->execute(['user' => $userId]);
    foreach ($old->fetchAll() as $quote) {
        release_quote_reservations($quote['id']);
    }
    $db->prepare('UPDATE checkout_quotes SET status="expired" WHERE user_id=:user AND status="open"')->execute(['user' => $userId]);

    $groups = [];
    foreach ($cart['items'] as $item) {
        if (!in_array($item['status'], ['approved', 'active'], true)) json_response(409, ['success' => false, 'message' => $item['name'] . ' is no longer available.']);
        if ((int)$item['available_quantity'] < (int)$item['quantity']) json_response(409, ['success' => false, 'message' => 'Not enough stock for ' . $item['name'] . '.']);
        $vendor = $item['vendor_id'];
        if (!isset($groups[$vendor])) $groups[$vendor] = ['value' => 0.0, 'weight' => 0, 'items' => [], 'vendor_name' => $item['vendor_name']];
        $groups[$vendor]['value'] += $item['line_subtotal'];
        $groups[$vendor]['weight'] += (int)$item['package_weight_grams'] * (int)$item['quantity'];
        $groups[$vendor]['items'][] = $item;
    }

    $shipping = 0.0;
    $pricingGroups = [];
    foreach ($groups as $vendorId => $group) {
        $q = shipping_for_vendor($vendorId, $address, $group['value'], $group['weight']);
        if (!$q['serviceable']) json_response(422, ['success' => false, 'message' => $group['vendor_name'] . ': ' . $q['reason'], 'code' => 'ADDRESS_NOT_SERVICEABLE']);
        $shipping += $q['amount'];
        $pricingGroups[$vendorId] = ['subtotal' => round($group['value'], 2), 'shipping' => $q['amount'], 'rule' => $q['rule']];
    }

    // ── Coupon ──
    $discount = 0.0;
    $couponId = null;
    if ($couponCode !== '') {
        $vendorIds = array_keys($groups);
        $coupon = find_customer_coupon($db, $couponCode, $userId, $vendorIds, (float)$cart['subtotal'], true);
        $base = $coupon['vendor_id'] !== null ? $groups[$coupon['vendor_id']]['value'] : $cart['subtotal'];
        $discount = $coupon['discount_type'] === 'percent' ? $base * ((float)$coupon['discount_value'] / 100) : (float)$coupon['discount_value'];
        if ($coupon['max_discount_amount'] !== null) $discount = min($discount, (float)$coupon['max_discount_amount']);
        $discount = round(min($discount, $base), 2);
        $couponId = $coupon['id'];
    }

    // ── Reward redemption ──
    $rewardDiscount = 0.0;
    $rewardPointsApplied = 0;
    if ($pointsToRedeem > 0) {
        $minPoints = (int)reward_config('min_redeem_points', 100);
        if ($pointsToRedeem < $minPoints) json_response(422, ['success' => false, 'message' => "Minimum {$minPoints} points required to redeem."]);

        $acct = reward_balance($userId, false);
        if ((int)$acct['balance_points'] < $pointsToRedeem) json_response(422, ['success' => false, 'message' => 'You do not have enough reward points.']);

        $pointValue = reward_config('point_value_rupees', 1.0);
        $rewardDiscount = round($pointsToRedeem * $pointValue, 2);

        // Cap by redeem_max_percent of cart subtotal
        $maxPercent = reward_config('redeem_max_percent', 20.0);
        $maxDiscount = round((float)$cart['subtotal'] * ($maxPercent / 100), 2);
        if ($rewardDiscount > $maxDiscount) {
            // Scale points down
            $rewardDiscount = $maxDiscount;
            $rewardPointsApplied = (int)floor($maxDiscount / $pointValue);
            if ($rewardPointsApplied < $minPoints) json_response(422, ['success' => false, 'message' => 'Order is too small to use reward points.']);
        } else {
            $rewardPointsApplied = $pointsToRedeem;
        }
    }
// ── Reward points preview ──
$rewardDiscount      = 0.0;
$rewardPointsApplied = 0;
$pointsRequested     = max(0, (int)($body['reward_points_redeem'] ?? 0));
if ($pointsRequested > 0) {
    $bal = $db->prepare('SELECT balance_points FROM customer_rewards WHERE user_id=:u');
    $bal->execute(['u' => $userId]);
    $balance = (int)($bal->fetchColumn() ?: 0);

    $maxDiscount = round((float)$cart['subtotal'] * 0.20, 2);
    $maxPoints   = (int)floor($maxDiscount);
    $points      = min($pointsRequested, $balance, $maxPoints);

    if ($points >= 100) {
        $rewardPointsApplied = $points;
        $rewardDiscount      = round($points * 1.0, 2);
    }
}

$tax=0.0;foreach($cart['items'] as $item)$tax+=round($item['line_subtotal']*((float)$item['gst_percent']/100),2);
    $tax = 0.0;
    foreach ($cart['items'] as $item) $tax += round($item['line_subtotal'] * ((float)$item['gst_percent'] / 100), 2);
    $totalDiscount = round($discount + $rewardDiscount, 2);
    $total = round($cart['subtotal'] + $shipping + $tax - $totalDiscount, 2);

    $quoteId = uuid_string();
    $pricing = [
        'groups' => $pricingGroups,
        'items'  => array_map(fn($i) => [
            'variant_id'    => $i['variant_id'],
            'quantity'      => (int)$i['quantity'],
            'unit_price'    => $i['unit_price'],
            'tax_percent'   => (float)$i['gst_percent'],
            'line_subtotal' => $i['line_subtotal'],
        ], $cart['items']),
        'reward_points_redeem' => $rewardPointsApplied,
        'reward_discount'      => $rewardDiscount,
    ];
    $pricing['reward_points_redeem'] = $rewardPointsApplied;
$pricing['reward_discount'] = $rewardDiscount;

    $db->prepare(
        'INSERT INTO checkout_quotes
           (id, user_id, cart_id, address_id, coupon_id, pricing_json, subtotal, shipping_amount,
            discount_amount, tax_amount, total_amount, currency, status, expires_at, created_at)
         VALUES (:id, :user, :cart, :address, :coupon, :pricing, :subtotal, :shipping,
                 :discount, :tax, :total, "INR", "open", DATE_ADD(UTC_TIMESTAMP(), INTERVAL 15 MINUTE), UTC_TIMESTAMP())'
    )->execute([
        'id' => $quoteId, 'user' => $userId, 'cart' => $cart['id'], 'address' => $addressId,
        'coupon' => $couponId, 'pricing' => json_encode($pricing, JSON_UNESCAPED_SLASHES),
        'subtotal' => $cart['subtotal'], 'shipping' => $shipping,
        'discount' => $totalDiscount, 'tax' => round($tax, 2), 'total' => $total,
    ]);

    foreach ($cart['items'] as $item) {
        $lock = $db->prepare('SELECT stock_quantity, reserved_quantity FROM inventory_levels WHERE variant_id=:variant FOR UPDATE');
        $lock->execute(['variant' => $item['variant_id']]);
        $level = $lock->fetch();
        if (!$level || (int)$level['stock_quantity'] - (int)$level['reserved_quantity'] < (int)$item['quantity'])
            throw new RuntimeException('Stock changed while creating the quote.');
        $db->prepare('UPDATE inventory_levels SET reserved_quantity=reserved_quantity+:qty, updated_at=UTC_TIMESTAMP() WHERE variant_id=:variant')
           ->execute(['qty' => $item['quantity'], 'variant' => $item['variant_id']]);
        $db->prepare('INSERT INTO inventory_reservations (id, quote_id, variant_id, quantity, status, expires_at, created_at) VALUES (:id, :quote, :variant, :qty, "reserved", DATE_ADD(UTC_TIMESTAMP(), INTERVAL 15 MINUTE), UTC_TIMESTAMP())')
           ->execute(['id' => uuid_string(), 'quote' => $quoteId, 'variant' => $item['variant_id'], 'qty' => $item['quantity']]);
    }

    $db->commit();
    json_response(201, [
        'success' => true,
        'quote' => [
            'id' => $quoteId,
            'subtotal' => $cart['subtotal'],
            'shipping_amount' => round($shipping, 2),
            'coupon_discount' => $discount,
            'reward_discount' => $rewardDiscount,
            'reward_points_redeem' => $rewardPointsApplied,
            'discount_amount' => $totalDiscount,
            'tax_amount' => round($tax, 2),
            'total_amount' => $total,
            'currency' => 'INR',
            'expires_in' => 900,
            'vendors' => $pricingGroups,
        ],
    ]);
} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    throw $e;
}