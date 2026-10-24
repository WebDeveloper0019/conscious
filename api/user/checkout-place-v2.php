<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/rewards.php';
require_once __DIR__ . '/../lib/invoice.php';

require_once __DIR__ . '/../lib/coupon.php';
require_once __DIR__ . '/../lib/razorpay.php';

function checkout_place_v2(): void
{
    send_cors_headers();
    $auth = require_auth();
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
    }

    $body = get_json_body();
    $quoteId = trim((string)($body['quote_id'] ?? ''));
    $paymentMethod = trim((string)($body['payment_method'] ?? ''));
    if ($quoteId === '' || !in_array($paymentMethod, ['cod', 'online'], true)) {
        json_response(422, ['success' => false, 'message' => 'A valid quote and payment method are required.']);
    }
    if ($paymentMethod === 'online' && APP_ENV !== 'local' && !razorpay_is_configured()) {
        json_response(503, ['success' => false, 'message' => 'Online payment is temporarily unavailable.']);
    }

    $userId = auth_user_id($auth);
    $principal = ['type' => 'customer', 'id' => $userId];
    $db = get_db();
    $db->beginTransaction();

    try {
        // Keep the retry key in the same transaction as the order. A failed
        // checkout must not leave behind a permanently "processing" key.
        $idempotency = begin_idempotent_request('checkout.place', $principal, $body);
        $owner=$db->prepare('SELECT id FROM users WHERE id=:id FOR UPDATE');$owner->execute(['id'=>$userId]);$owner->fetch();
        $quoteStmt = $db->prepare(
            'SELECT q.*,a.full_name,a.phone,a.pincode,a.address_line1,a.address_line2,a.city,a.state,a.landmark
             FROM checkout_quotes q JOIN user_addresses a ON a.id=q.address_id
             WHERE q.id=:quote_id AND q.user_id=:quote_user FOR UPDATE'
        );
        $quoteStmt->execute(['quote_id' => $quoteId, 'quote_user' => $userId]);
        $quote = $quoteStmt->fetch();
        if (!$quote || $quote['status'] !== 'open' || strtotime((string)$quote['expires_at']) <= time()) {
            json_response(409, ['success' => false, 'message' => 'Checkout quote expired. Please calculate checkout again.']);
        }

        $pricing = json_decode((string)$quote['pricing_json'], true);
        if (!is_array($pricing)) {
            json_response(409, ['success' => false, 'message' => 'Checkout pricing is no longer valid.']);
        }
        if ($paymentMethod === 'cod') {
            foreach (array_keys((array)($pricing['groups'] ?? [])) as $vendorId) {
                $codQuery = $db->prepare('SELECT cod_enabled FROM vendor_shipping_settings WHERE vendor_id=:vendor');
                $codQuery->execute(['vendor' => $vendorId]);
                if ((int)$codQuery->fetchColumn() !== 1) json_response(422, ['success' => false, 'message' => 'Cash on delivery is not available for every vendor in this order. Choose online payment.']);
            }
        }
        $cart = cart_details($userId);
        if (!$cart['items']) {
            json_response(409, ['success' => false, 'message' => 'Your cart is empty.']);
        }
        $quotedItems=array_column((array)($pricing['items']??[]),null,'variant_id');
        if($cart['id']!==$quote['cart_id']||count($quotedItems)!==count($cart['items']))json_response(409,['success'=>false,'message'=>'Your bag changed. Calculate delivery and total again before placing your order.']);
        foreach($cart['items'] as $item){
            $quoted=$quotedItems[$item['variant_id']]??null;
            if(!$item['can_purchase']||!$quoted||(int)$quoted['quantity']!==(int)$item['quantity']||abs((float)$quoted['unit_price']-(float)$item['unit_price'])>0.001||abs((float)$quoted['tax_percent']-(float)$item['gst_percent'])>0.001){
                json_response(409,['success'=>false,'message'=>'A product, price or quantity changed. Calculate delivery and total again to review the latest amount.']);
            }
        }
        $vendorIds = array_values(array_unique(array_map(fn($item) => (string)$item['vendor_id'], $cart['items'])));
        if ($quote['coupon_id'] !== null) {
            find_customer_coupon_by_id($db, (string)$quote['coupon_id'], $userId, $vendorIds, (float)$quote['subtotal']);
        }

        $reservationStmt = $db->prepare(
            'SELECT * FROM inventory_reservations WHERE quote_id=:reservation_quote AND status="reserved" FOR UPDATE'
        );
        $reservationStmt->execute(['reservation_quote' => $quoteId]);
        $reserved = array_column($reservationStmt->fetchAll(), null, 'variant_id');
        foreach ($cart['items'] as $item) {
            $reservation = $reserved[$item['variant_id']] ?? null;
            if (!$reservation || (int)$reservation['quantity'] !== (int)$item['quantity']) {
                json_response(409, ['success' => false, 'message' => 'Reserved stock no longer matches your cart.']);
            }
        }

        $orderNumber = order_number();
        $addressSnapshot = [
            'full_name' => $quote['full_name'], 'phone' => $quote['phone'], 'pincode' => $quote['pincode'],
            'address_line1' => $quote['address_line1'], 'address_line2' => $quote['address_line2'],
            'city' => $quote['city'], 'state' => $quote['state'], 'landmark' => $quote['landmark'],
        ];
        $paymentStatus = $paymentMethod === 'cod' ? 'cod_pending' : 'pending';
        $orderStatus = $paymentMethod === 'cod' ? 'confirmed' : 'payment_pending';

        $db->prepare(
            'INSERT INTO orders
             (user_id,checkout_quote_id,order_number,status,payment_status,payment_method,currency,subtotal,shipping_amount,discount_amount,tax_amount,total_amount,address_snapshot_json,placed_at,created_at,updated_at)
             VALUES (:order_user,:order_quote,:order_number,:order_status,:order_payment,:payment_method,"INR",:order_subtotal,:order_shipping,:order_discount,:order_tax,:order_total,:order_address,UTC_TIMESTAMP(),UTC_TIMESTAMP(),UTC_TIMESTAMP())'
        )->execute([
            'order_user' => $userId, 'order_quote' => $quoteId, 'order_number' => $orderNumber,
            'order_status' => $orderStatus, 'order_payment' => $paymentStatus,
            'payment_method' => $paymentMethod,
            'order_subtotal' => $quote['subtotal'], 'order_shipping' => $quote['shipping_amount'],
            'order_discount' => $quote['discount_amount'], 'order_tax' => $quote['tax_amount'],
            'order_total' => $quote['total_amount'], 'order_address' => json_encode($addressSnapshot, JSON_UNESCAPED_SLASHES),
        ]);
        $orderId = (int)$db->lastInsertId();


        // Redeem reward points atomically
        // ── Redeem reward points atomically ──
        // Uses the canonical reward_redeem_for_order() helper.
        // Called after order creation, inside the same transaction,
        // so a failed redeem rolls back the whole order.
        $rewardPoints = (int)($pricing['reward_points_redeem'] ?? 0);
        if ($rewardPoints > 0) {
            try {
                $applied = reward_redeem_for_order(
                    $userId,
                    $orderId,
                    $rewardPoints,
                    (float)$quote['subtotal'],
                    (string)$orderNumber
                );
                if ($applied !== $rewardPoints) {
                    throw new RuntimeException('Reward points changed. Please try again.');
                }
            } catch (Throwable $e) {
                throw new RuntimeException('Reward redemption failed: ' . $e->getMessage());
            }
        }

        $groupsByVendor = [];
        foreach ($cart['items'] as $item) {
            $groupsByVendor[(string)$item['vendor_id']][] = $item;
        }
        $itemSubtotal = (float)$quote['subtotal'];
        $groupIndex = 0;

        foreach ($groupsByVendor as $vendorId => $items) {
            $groupIndex++;
            $groupSubtotal = round(array_sum(array_column($items, 'line_subtotal')), 2);
            $ratio = $itemSubtotal > 0 ? $groupSubtotal / $itemSubtotal : 0;
            $groupShipping = (float)($pricing['groups'][$vendorId]['shipping'] ?? 0);
            $groupDiscount = round((float)$quote['discount_amount'] * $ratio, 2);
            $groupTax = 0.0;
            foreach ($items as $item) {
                $groupTax += round((float)$item['line_subtotal'] * ((float)$item['gst_percent'] / 100), 2);
            }
            $groupTotal = round($groupSubtotal + $groupShipping + $groupTax - $groupDiscount, 2);
            $groupId = uuid_string();
            $groupNumber = $orderNumber . '-' . str_pad((string)$groupIndex, 2, '0', STR_PAD_LEFT) . '-' . strtoupper(substr($vendorId, 0, 4));

            $policyStmt = $db->prepare(
                'SELECT return_policy,cancellation_policy,damage_policy,handmade_policy FROM vendor_settings WHERE vendor_id=:policy_vendor'
            );
            $policyStmt->execute(['policy_vendor' => $vendorId]);
            $policy = $policyStmt->fetch() ?: [
                'return_policy' => null, 'cancellation_policy' => null, 'damage_policy' => null, 'handmade_policy' => null,
            ];
            $maxProcessingDays = 0;
            foreach ($items as $item) {
                $maxProcessingDays = max($maxProcessingDays, (int)$item['processing_max_days']);
            }

            $db->prepare(
                'INSERT INTO vendor_order_groups
                 (id,order_id,vendor_id,group_number,status,subtotal,shipping_amount,discount_amount,tax_amount,total_amount,policy_snapshot_json,dispatch_due_at,created_at,updated_at)
                 VALUES (:group_id,:group_order,:group_vendor,:group_number,"awaiting_acceptance",:group_subtotal,:group_shipping,:group_discount,:group_tax,:group_total,:group_policy,DATE_ADD(UTC_TIMESTAMP(),INTERVAL :processing_days DAY),UTC_TIMESTAMP(),UTC_TIMESTAMP())'
            )->execute([
                'group_id' => $groupId, 'group_order' => $orderId, 'group_vendor' => $vendorId, 'group_number' => $groupNumber,
                'group_subtotal' => $groupSubtotal, 'group_shipping' => $groupShipping,
                'group_discount' => $groupDiscount, 'group_tax' => $groupTax, 'group_total' => $groupTotal,
                'group_policy' => json_encode($policy, JSON_UNESCAPED_SLASHES), 'processing_days' => $maxProcessingDays,
            ]);

            foreach ($items as $item) {
                $lineTax = round((float)$item['line_subtotal'] * ((float)$item['gst_percent'] / 100), 2);
                $lineDiscount = round($groupDiscount * ($groupSubtotal > 0 ? (float)$item['line_subtotal'] / $groupSubtotal : 0), 2);
                $lineTotal = round((float)$item['line_subtotal'] + $lineTax - $lineDiscount, 2);
                $productSnapshot = [
                    'name' => $item['name'], 'slug' => $item['slug'], 'sku' => $item['variant_sku'],
                    'variant_name' => $item['variant_name'], 'unit_price' => $item['unit_price'],
                    'gst_percent' => (float)$item['gst_percent'], 'image' => $item['image_url'],
                ];

                $db->prepare(
                    'INSERT INTO order_items
                     (order_id,vendor_group_id,vendor_id,product_id,variant_id,product_name,sku,quantity,status,unit_price,mrp,discount_amount,tax_amount,line_total,image,product_snapshot_json,policy_snapshot_json)
                     VALUES (:item_order,:item_group,:item_vendor,:item_product,:item_variant,:item_name,:item_sku,:item_quantity,"awaiting_acceptance",:item_price,:item_mrp,:item_discount,:item_tax,:item_total,:item_image,:item_snapshot,:item_policy)'
                )->execute([
                    'item_order' => $orderId, 'item_group' => $groupId, 'item_vendor' => $vendorId,
                    'item_product' => $item['product_id'], 'item_variant' => $item['variant_id'],
                    'item_name' => $item['name'], 'item_sku' => $item['variant_sku'], 'item_quantity' => $item['quantity'],
                    'item_price' => $item['unit_price'], 'item_mrp' => $item['mrp'], 'item_discount' => $lineDiscount,
                    'item_tax' => $lineTax, 'item_total' => $lineTotal, 'item_image' => $item['image_url'],
                    'item_snapshot' => json_encode($productSnapshot, JSON_UNESCAPED_SLASHES),
                    'item_policy' => json_encode($policy, JSON_UNESCAPED_SLASHES),
                ]);
                $orderItemId = (int)$db->lastInsertId();

                $commissionStmt = $db->prepare(
                    'SELECT * FROM commission_rules
                     WHERE status="active" AND effective_from<=UTC_TIMESTAMP()
                       AND (effective_until IS NULL OR effective_until>UTC_TIMESTAMP())
                       AND (vendor_id=:commission_vendor OR vendor_id IS NULL)
                       AND (category_id IS NULL OR category_id=(SELECT category_id FROM products WHERE id=:commission_product))
                     ORDER BY (vendor_id IS NOT NULL) DESC,(category_id IS NOT NULL) DESC,effective_from DESC LIMIT 1'
                );
                $commissionStmt->execute(['commission_vendor' => $vendorId, 'commission_product' => $item['product_id']]);
                $commission = $commissionStmt->fetch() ?: [];
                $commissionPercent = (float)($commission['commission_percent'] ?? 10);
                $fixedFee = (float)($commission['fixed_fee'] ?? 0);
                $commissionAmount = round(((float)$item['line_subtotal'] - $lineDiscount) * $commissionPercent / 100 + $fixedFee, 2);
                $vendorNet = round($lineTotal - $commissionAmount, 2);
                $db->prepare(
                    'INSERT INTO order_item_financials
                     (order_item_id,gross_amount,discount_amount,tax_amount,commission_percent,commission_amount,fixed_fee,vendor_net_amount,snapshot_json,created_at)
                     VALUES (:financial_item,:financial_gross,:financial_discount,:financial_tax,:financial_percent,:financial_commission,:financial_fixed,:financial_net,:financial_snapshot,UTC_TIMESTAMP())'
                )->execute([
                    'financial_item' => $orderItemId, 'financial_gross' => $item['line_subtotal'],
                    'financial_discount' => $lineDiscount, 'financial_tax' => $lineTax,
                    'financial_percent' => $commissionPercent, 'financial_commission' => $commissionAmount,
                    'financial_fixed' => $fixedFee, 'financial_net' => $vendorNet,
                    'financial_snapshot' => json_encode(['rule_id' => $commission['id'] ?? null, 'payment_method' => $paymentMethod]),
                ]);

                if ($paymentMethod === 'cod') {
                    $inventory = $db->prepare(
                        'UPDATE inventory_levels
                         SET stock_quantity=stock_quantity-:sale_qty,reserved_quantity=reserved_quantity-:reserve_qty,updated_at=UTC_TIMESTAMP()
                         WHERE variant_id=:inventory_variant AND stock_quantity>=:stock_guard AND reserved_quantity>=:reserved_guard'
                    );
                    $inventory->execute([
                        'sale_qty' => $item['quantity'], 'reserve_qty' => $item['quantity'], 'inventory_variant' => $item['variant_id'],
                        'stock_guard' => $item['quantity'], 'reserved_guard' => $item['quantity'],
                    ]);
                    if (!$inventory->rowCount()) {
                        throw new RuntimeException('Reserved stock cannot be converted.');
                    }
                    $db->prepare(
                        'UPDATE inventory_reservations SET status="converted",released_at=UTC_TIMESTAMP()
                         WHERE quote_id=:converted_quote AND variant_id=:converted_variant'
                    )->execute(['converted_quote' => $quoteId, 'converted_variant' => $item['variant_id']]);

                    $levelStmt = $db->prepare('SELECT stock_quantity,reserved_quantity FROM inventory_levels WHERE variant_id=:level_variant');
                    $levelStmt->execute(['level_variant' => $item['variant_id']]);
                    $level = $levelStmt->fetch() ?: ['stock_quantity' => 0, 'reserved_quantity' => 0];
                    $db->prepare(
                        'INSERT INTO inventory_movements
                         (variant_id,vendor_id,movement_type,quantity_delta,reserved_delta,stock_after,reserved_after,reference_type,reference_id,note,actor_type,actor_id,created_at)
                         VALUES (:movement_variant,:movement_vendor,"sale",:movement_qty,:movement_reserved,:movement_stock,:movement_reserved_after,"order_item",:movement_reference,"Cash-on-delivery order placed","customer",:movement_actor,UTC_TIMESTAMP())'
                    )->execute([
                        'movement_variant' => $item['variant_id'], 'movement_vendor' => $vendorId,
                        'movement_qty' => -(int)$item['quantity'], 'movement_reserved' => -(int)$item['quantity'],
                        'movement_stock' => $level['stock_quantity'], 'movement_reserved_after' => $level['reserved_quantity'],
                        'movement_reference' => (string)$orderItemId, 'movement_actor' => $userId,
                    ]);
                    $db->prepare(
                        'INSERT INTO vendor_ledger_entries
                         (id,vendor_id,entry_type,amount,currency,balance_bucket,reference_type,reference_id,description,available_at,created_at)
                         VALUES (:ledger_id,:ledger_vendor,"order_earning",:ledger_amount,"INR","pending","order_item",:ledger_reference,"Order earning pending delivery",DATE_ADD(UTC_TIMESTAMP(),INTERVAL 7 DAY),UTC_TIMESTAMP())'
                    )->execute([
                        'ledger_id' => uuid_string(), 'ledger_vendor' => $vendorId, 'ledger_amount' => $vendorNet,
                        'ledger_reference' => (string)$orderItemId,
                    ]);
                }
            }

            if ($paymentMethod === 'cod') {
                $db->prepare(
                    'INSERT INTO cod_collections (id,order_id,vendor_group_id,vendor_id,amount,status,created_at,updated_at)
                     VALUES (:cod_id,:cod_order,:cod_group,:cod_vendor,:cod_amount,"pending",UTC_TIMESTAMP(),UTC_TIMESTAMP())'
                )->execute([
                    'cod_id' => uuid_string(), 'cod_order' => $orderId, 'cod_group' => $groupId,
                    'cod_vendor' => $vendorId, 'cod_amount' => $groupTotal,
                ]);
            }
            create_notification('vendor', $vendorId, 'new_order', 'New order received', 'A new order is waiting for your acceptance.', '../orders.php', ['order_id' => $orderId, 'group_id' => $groupId]);
        }

        $db->prepare('UPDATE checkout_quotes SET status="converted" WHERE id=:converted_quote_id')->execute(['converted_quote_id' => $quoteId]);
        $db->prepare('UPDATE carts SET status="converted",updated_at=UTC_TIMESTAMP() WHERE id=:converted_cart_id')->execute(['converted_cart_id' => $cart['id']]);
        if($paymentMethod==='cod'&&$quote['coupon_id']!==null){
            $db->prepare('INSERT INTO coupon_redemptions (id,coupon_id,user_id,order_id,discount_amount,created_at) VALUES (:id,:coupon,:user,:order,:amount,UTC_TIMESTAMP())')->execute([
                'id'=>uuid_string(),'coupon'=>$quote['coupon_id'],'user'=>$userId,'order'=>$orderId,
                'amount'=>(float)($pricing['coupon_discount']??$quote['discount_amount']),
            ]);
        }

        $paymentAttemptId = uuid_string();
        $db->prepare(
            'INSERT INTO payment_attempts (id,order_id,provider,amount,currency,status,created_at)
             VALUES (:payment_id,:payment_order,:payment_provider,:payment_amount,"INR",:payment_status,UTC_TIMESTAMP())'
        )->execute([
            'payment_id' => $paymentAttemptId, 'payment_order' => $orderId, 'payment_provider' => $paymentMethod,
            'payment_amount' => $quote['total_amount'], 'payment_status' => $paymentMethod === 'cod' ? 'cod_pending' : 'created',
        ]);
        // ── Generate invoice for COD orders ──
        if ($paymentMethod === 'cod') {
            try {
                invoice_generate_for_order($orderId);
            } catch (Throwable $e) {
                error_log('COD invoice generation failed for order ' . $orderId . ': ' . $e->getMessage());
            }
        }

        record_status_change('order', (string)$orderId, null, $orderStatus);
        attribute_influencer_order($orderId, $userId, (float)$quote['total_amount']);
        audit_event('customer.order_placed', 'order', (string)$orderId, ['order_number' => $orderNumber, 'payment_method' => $paymentMethod]);

        $response = [
            'success' => true,
            'message' => $paymentMethod === 'cod' ? 'Order placed successfully.' : 'Order created. Continue to the payment provider.',
            'order' => [
                'id' => $orderId, 'order_number' => $orderNumber, 'status' => $orderStatus,
                'payment_status' => $paymentStatus, 'total_amount' => (float)$quote['total_amount'],
            ],
            'payment' => [
                'attempt_id' => $paymentAttemptId, 'provider' => $paymentMethod,
                'status' => $paymentMethod === 'cod' ? 'cod_pending' : 'created',
            ],
        ];
        finish_idempotent_request((int)$idempotency['id'], 201, $response);
        $db->commit();
        json_response(201, $response);
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
}
