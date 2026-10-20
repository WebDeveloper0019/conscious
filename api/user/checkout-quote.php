<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/commerce.php';
require_once __DIR__ . '/../lib/coupon.php';
require_once __DIR__ . '/../lib/rewards.php';

send_cors_headers();
$auth = require_auth();
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') json_response(405, ['success' => false, 'message' => 'Method not allowed.']);

$body = get_json_body();
$userId = auth_user_id($auth);
$addressId = trim((string)($body['address_id'] ?? ''));
$couponCode = strtoupper(trim((string)($body['coupon_code'] ?? '')));
$requestedPoints = max(0, (int)($body['reward_points_redeem'] ?? 0));
$db = get_db();

$addressStmt = $db->prepare('SELECT * FROM user_addresses WHERE id=:id AND user_id=:user');
$addressStmt->execute(['id' => $addressId, 'user' => $userId]);
$address = $addressStmt->fetch();
if (!$address) json_response(404, ['success' => false, 'message' => 'Delivery address not found.']);

$db->beginTransaction();
try {
    $owner=$db->prepare('SELECT id FROM users WHERE id=:id FOR UPDATE');$owner->execute(['id'=>$userId]);$owner->fetch();
    expire_customer_quotes($userId);
    $cart=cart_details($userId);
    if(!$cart['items'])json_response(422,['success'=>false,'message'=>'Your bag is empty. Add a product before checkout.']);

    $groups = [];
    foreach ($cart['items'] as $item) {
        if (!$item['can_purchase'] || (float)$item['unit_price']<0) json_response(409, ['success' => false, 'message' => $item['name'] . ' is no longer available. Remove it from your bag to continue.']);
        if ((int)$item['available_quantity'] < (int)$item['quantity']) json_response(409, ['success' => false, 'message' => 'Not enough stock for ' . $item['name'] . '.']);
        $vendorId = (string)$item['vendor_id'];
        if (!isset($groups[$vendorId])) $groups[$vendorId] = ['value' => 0.0, 'weight' => 0, 'vendor_name' => $item['vendor_name']];
        $groups[$vendorId]['value'] += (float)$item['line_subtotal'];
        $groups[$vendorId]['weight'] += (int)$item['package_weight_grams'] * (int)$item['quantity'];
    }

    $shipping = 0.0;
    $pricingGroups = [];
    foreach ($groups as $vendorId => $group) {
        $shippingQuote = shipping_for_vendor($vendorId, $address, $group['value'], $group['weight']);
        if (!$shippingQuote['serviceable']) json_response(422, ['success' => false, 'message' => $group['vendor_name'] . ': ' . $shippingQuote['reason'], 'code' => 'ADDRESS_NOT_SERVICEABLE']);
        $codQuery = $db->prepare('SELECT cod_enabled FROM vendor_shipping_settings WHERE vendor_id=:vendor');
        $codQuery->execute(['vendor' => $vendorId]);
        $codEnabled = (int)$codQuery->fetchColumn() === 1;
        $shipping += (float)$shippingQuote['amount'];
        $pricingGroups[$vendorId] = ['vendor_name'=>$group['vendor_name'],'subtotal' => round($group['value'], 2), 'shipping' => $shippingQuote['amount'], 'cod_enabled' => $codEnabled, 'rule' => $shippingQuote['rule']];
    }

    $couponDiscount = 0.0;
    $couponId = null;
    if ($couponCode !== '') {
        $coupon = find_customer_coupon($db, $couponCode, $userId, array_keys($groups), (float)$cart['subtotal'], true);
        $base = $coupon['vendor_id'] !== null ? $groups[$coupon['vendor_id']]['value'] : (float)$cart['subtotal'];
        $couponDiscount = $coupon['discount_type'] === 'percent' ? $base * ((float)$coupon['discount_value'] / 100) : (float)$coupon['discount_value'];
        if ($coupon['max_discount_amount'] !== null) $couponDiscount = min($couponDiscount, (float)$coupon['max_discount_amount']);
        $couponDiscount = round(min($couponDiscount, $base), 2);
        $couponId = $coupon['id'];
    }

    $rewardPoints = 0;
    $rewardDiscount = 0.0;
    if ($requestedPoints > 0) {
        $minimum = max(1, (int)reward_config('min_redeem_points', 100));
        $balance = reward_balance($userId, true);
        $pointValue = max(0.01, reward_config('point_value_rupees', 1.0));
        $cap = (int)floor(((float)$cart['subtotal'] * reward_config('redeem_max_percent', 20.0) / 100) / $pointValue);
        if ($requestedPoints < $minimum || $requestedPoints > (int)$balance['balance_points'] || $requestedPoints > $cap) {
            json_response(422, ['success' => false, 'message' => 'Reward balance or redemption limit is not sufficient.']);
        }
        $rewardPoints = $requestedPoints;
        $rewardDiscount = round($rewardPoints * $pointValue, 2);
    }

    $tax = 0.0;
    foreach ($cart['items'] as $item) $tax += round((float)$item['line_subtotal'] * ((float)$item['gst_percent'] / 100), 2);
    $totalDiscount = round($couponDiscount + $rewardDiscount, 2);
    $total = round((float)$cart['subtotal'] + $shipping + $tax - $totalDiscount, 2);
    if ($total < 0) throw new RuntimeException('Checkout total cannot be negative.');

    $quoteId = uuid_string();
    $pricing = [
        'groups' => $pricingGroups,
        'items' => array_map(fn($item) => [
            'variant_id' => $item['variant_id'], 'quantity' => (int)$item['quantity'],
            'unit_price' => $item['unit_price'], 'tax_percent' => (float)$item['gst_percent'],
            'line_subtotal' => $item['line_subtotal'],
        ], $cart['items']),
        'coupon_discount' => $couponDiscount,
        'reward_points_redeem' => $rewardPoints,
        'reward_discount' => $rewardDiscount,
    ];
    $db->prepare(
        'INSERT INTO checkout_quotes
         (id,user_id,cart_id,address_id,coupon_id,pricing_json,subtotal,shipping_amount,discount_amount,tax_amount,total_amount,currency,status,expires_at,created_at)
         VALUES (:id,:user,:cart,:address,:coupon,:pricing,:subtotal,:shipping,:discount,:tax,:total,"INR","open",DATE_ADD(UTC_TIMESTAMP(),INTERVAL 15 MINUTE),UTC_TIMESTAMP())'
    )->execute([
        'id' => $quoteId, 'user' => $userId, 'cart' => $cart['id'], 'address' => $addressId,
        'coupon' => $couponId, 'pricing' => json_encode($pricing, JSON_UNESCAPED_SLASHES),
        'subtotal' => $cart['subtotal'], 'shipping' => $shipping, 'discount' => $totalDiscount,
        'tax' => round($tax, 2), 'total' => $total,
    ]);

    foreach ($cart['items'] as $item) {
        $lock = $db->prepare('SELECT stock_quantity,reserved_quantity FROM inventory_levels WHERE variant_id=:variant FOR UPDATE');
        $lock->execute(['variant' => $item['variant_id']]);
        $level = $lock->fetch();
        if (!$level || (int)$level['stock_quantity'] - (int)$level['reserved_quantity'] < (int)$item['quantity']) throw new RuntimeException('Stock changed while creating the quote.');
        $db->prepare('UPDATE inventory_levels SET reserved_quantity=reserved_quantity+:qty,updated_at=UTC_TIMESTAMP() WHERE variant_id=:variant')->execute(['qty' => $item['quantity'], 'variant' => $item['variant_id']]);
        $db->prepare('INSERT INTO inventory_reservations (id,quote_id,variant_id,quantity,status,expires_at,created_at) VALUES (:id,:quote,:variant,:qty,"reserved",DATE_ADD(UTC_TIMESTAMP(),INTERVAL 15 MINUTE),UTC_TIMESTAMP())')->execute(['id' => uuid_string(), 'quote' => $quoteId, 'variant' => $item['variant_id'], 'qty' => $item['quantity']]);
    }

    $db->commit();
    json_response(201, ['success' => true, 'quote' => [
        'id' => $quoteId, 'subtotal' => $cart['subtotal'], 'shipping_amount' => round($shipping, 2),
        'coupon_discount' => $couponDiscount, 'reward_discount' => $rewardDiscount,
        'reward_points_redeem' => $rewardPoints, 'discount_amount' => $totalDiscount,
        'tax_amount' => round($tax, 2), 'total_amount' => $total, 'currency' => 'INR',
        'expires_in' => 900, 'vendors' => $pricingGroups,
    ]]);
} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    throw $e;
}
