<?php
declare(strict_types=1);

if (!function_exists('fixture_user')) {
    function fixture_user(): array {
        $db = get_db();
        $email = 'it+' . bin2hex(random_bytes(4)) . '@test.local';
        $id = uuid_string();
        $db->prepare('INSERT INTO users (id, first_name, last_name, email, mobile, password_hash, email_verified, created_at) VALUES (?, "IT", "Tester", ?, ?, "x", 1, UTC_TIMESTAMP())')
           ->execute([$id, $email, '9' . str_pad((string)random_int(0, 999999999), 9, '0', STR_PAD_LEFT)]);
        return ['id' => $id, 'email' => $email];
    }

    function fixture_vendor(): string {
        $db = get_db();
        $existing = $db->query("SELECT id FROM vendors WHERE status='active' AND verification_status='approved' LIMIT 1")->fetchColumn();
        if ($existing) return (string)$existing;

        $id = uuid_string();
        $db->prepare('INSERT INTO vendors (id, vendor_type, owner_name, display_name, email, mobile, status, verification_status, onboarding_status, created_at) VALUES (?, "artisan", "IT Vendor", "IT Vendor", ?, ?, "active", "approved", "completed", UTC_TIMESTAMP())')
           ->execute([$id, 'it-vendor+' . bin2hex(random_bytes(3)) . '@test.local', '9' . str_pad((string)random_int(0, 999999999), 9, '0', STR_PAD_LEFT)]);
        return $id;
    }

    function fixture_product(string $vendorId): array {
        $db = get_db();
        $pid = 'IT-PROD-' . strtoupper(bin2hex(random_bytes(4)));
        $vid = uuid_string();

        $db->prepare('INSERT INTO products (id, vendor_id, name, slug, sku, mrp, selling_price, gst_percent, package_weight_grams, processing_min_days, processing_max_days, status, created_at) VALUES (?, ?, "IT Test Product", ?, ?, 1500.00, 1200.00, 5.00, 300, 1, 3, "approved", UTC_TIMESTAMP())')
           ->execute([$pid, $vendorId, strtolower($pid), $pid]);
        $db->prepare('INSERT INTO product_variants (id, product_id, name, sku, price_adjustment) VALUES (?, ?, "Default", ?, 0.00)')
           ->execute([$vid, $pid, $pid . '-V1']);
        $db->prepare('INSERT INTO inventory_levels (variant_id, stock_quantity, reserved_quantity, low_stock_threshold, updated_at) VALUES (?, 100, 0, 5, UTC_TIMESTAMP())')
           ->execute([$vid]);

        return ['product_id' => $pid, 'variant_id' => $vid];
    }

    function fixture_order(string $userId, string $vendorId, array $product, string $payStatus = 'paid', string $payMethod = 'online', string $orderStatus = 'confirmed', int $qty = 1): array {
        $db = get_db();
        $orderNumber = 'IT-' . strtoupper(bin2hex(random_bytes(4)));
        $groupId = uuid_string();
        $unitPrice = 1200.00;
        $lineTotal = round($unitPrice * $qty * 1.05, 2);
        $subtotal = round($unitPrice * $qty, 2);
        $taxAmount = round($lineTotal - $subtotal, 2);
        $vendorNet = round($lineTotal * 0.9, 2);

        $db->prepare('INSERT INTO orders (user_id, checkout_quote_id, order_number, status, payment_status, payment_method, currency, subtotal, shipping_amount, discount_amount, tax_amount, total_amount, address_snapshot_json, placed_at, created_at, updated_at) VALUES (?, NULL, ?, ?, ?, ?, "INR", ?, 50.00, 0.00, ?, ?, ?, UTC_TIMESTAMP(), UTC_TIMESTAMP(), UTC_TIMESTAMP())')
           ->execute([$userId, $orderNumber, $orderStatus, $payStatus, $payMethod, $subtotal, $taxAmount, round($lineTotal + 50, 2),
               json_encode(['full_name'=>'IT Tester','phone'=>'9000000000','pincode'=>'560001','address_line1'=>'1 Test St','city'=>'Bangalore','state'=>'Karnataka'])]);
        $orderId = (int)$db->lastInsertId();

        $db->prepare('INSERT INTO vendor_order_groups (id, order_id, vendor_id, group_number, status, subtotal, shipping_amount, discount_amount, tax_amount, total_amount, policy_snapshot_json, dispatch_due_at, created_at, updated_at) VALUES (?, ?, ?, ?, "awaiting_acceptance", ?, 50.00, 0.00, ?, ?, "{}", DATE_ADD(UTC_TIMESTAMP(), INTERVAL 3 DAY), UTC_TIMESTAMP(), UTC_TIMESTAMP())')
           ->execute([$groupId, $orderId, $vendorId, $orderNumber . '-01', $subtotal, $taxAmount, round($lineTotal + 50, 2)]);

        $db->prepare('INSERT INTO order_items (order_id, vendor_group_id, vendor_id, product_id, variant_id, product_name, sku, quantity, status, unit_price, mrp, discount_amount, tax_amount, line_total, product_snapshot_json, policy_snapshot_json, cancelled_quantity, returned_quantity) VALUES (?, ?, ?, ?, ?, "IT Test Product", ?, ?, "awaiting_acceptance", ?, 1500.00, 0.00, ?, ?, ?, "{}", 0, 0)')
           ->execute([$orderId, $groupId, $vendorId, $product['product_id'], $product['variant_id'], $product['product_id'] . '-V1', $qty, $unitPrice, $taxAmount, $lineTotal,
               json_encode(['gst_percent' => 5.0, 'hsn' => '6214'])]);
        $itemId = (int)$db->lastInsertId();

        $db->prepare('INSERT INTO order_item_financials (order_item_id, gross_amount, discount_amount, tax_amount, commission_percent, commission_amount, fixed_fee, vendor_net_amount, snapshot_json, created_at) VALUES (?, ?, 0.00, ?, 10.00, ?, 0.00, ?, "{}", UTC_TIMESTAMP())')
           ->execute([$itemId, $subtotal, $taxAmount, round($lineTotal * 0.1, 2), $vendorNet]);

        if ($payStatus === 'paid') {
            $db->prepare('INSERT INTO payment_attempts (id, order_id, provider, provider_payment_id, amount, currency, status, created_at) VALUES (?, ?, ?, ?, ?, "INR", "paid", UTC_TIMESTAMP())')
               ->execute([uuid_string(), $orderId, $payMethod === 'cod' ? 'cod' : 'online', 'pay_it_' . bin2hex(random_bytes(6)), round($lineTotal + 50, 2)]);
        }

        return ['order_id'=>$orderId,'order_number'=>$orderNumber,'group_id'=>$groupId,'item_id'=>$itemId,'variant_id'=>$product['variant_id'],'total_amount'=>round($lineTotal + 50, 2),'vendor_net'=>$vendorNet];
    }

    function cleanup_order(int $orderId): void {
        $db = get_db();
        foreach (['refunds','invoices','credit_notes','payment_attempts','cod_collections','idempotency_keys'] as $t) {
            try { $db->prepare("DELETE FROM $t WHERE order_id = ?")->execute([$orderId]); } catch (Throwable $e) {}
        }
        try { $db->prepare('DELETE FROM customer_reward_ledger WHERE reference_type = "order" AND reference_id = ?')->execute([(string)$orderId]); } catch (Throwable $e) {}
        try { $db->prepare('DELETE FROM vendor_ledger_entries WHERE reference_type = "order" AND reference_id = ?')->execute([(string)$orderId]); } catch (Throwable $e) {}
        try {
            $items = $db->prepare('SELECT id FROM order_items WHERE order_id = ?');
            $items->execute([$orderId]);
            $ids = array_column($items->fetchAll(), 'id');
            if ($ids) {
                $in = implode(',', array_map('intval', $ids));
                $db->exec("DELETE FROM order_item_financials WHERE order_item_id IN ($in)");
                $db->exec("DELETE FROM inventory_movements WHERE reference_type='order_item' AND reference_id IN ($in)");
                $db->exec("DELETE FROM vendor_ledger_entries WHERE reference_type='order_item' AND reference_id IN ($in)");
            }
        } catch (Throwable $e) {}
        try { $db->prepare('DELETE FROM order_items WHERE order_id = ?')->execute([$orderId]); } catch (Throwable $e) {}
        try { $db->prepare('DELETE FROM vendor_order_groups WHERE order_id = ?')->execute([$orderId]); } catch (Throwable $e) {}
        try { $db->prepare('DELETE FROM orders WHERE id = ?')->execute([$orderId]); } catch (Throwable $e) {}
    }

    function cleanup_user(string $userId): void {
        $db = get_db();
        try { $db->prepare('DELETE FROM customer_rewards WHERE user_id = ?')->execute([$userId]); } catch (Throwable $e) {}
        try { $db->prepare('DELETE FROM customer_reward_ledger WHERE user_id = ?')->execute([$userId]); } catch (Throwable $e) {}
        try { $db->prepare('DELETE FROM users WHERE id = ?')->execute([$userId]); } catch (Throwable $e) {}
    }
}

/**
 * Mirrors cancel-order.php's logic. Used by integration tests so we don't
 * need HTTP auth in CLI.
 */
if (!function_exists('run_cancel_logic')) {
    function run_cancel_logic(int $orderId, string $userId, string $reason = 'E2E test cancel'): array
    {
        $db = get_db();
        $db->beginTransaction();

        try {
            $stmt = $db->prepare('SELECT * FROM orders WHERE id = :id AND user_id = :u LIMIT 1 FOR UPDATE');
            $stmt->execute(['id' => $orderId, 'u' => $userId]);
            $order = $stmt->fetch();
            if (!$order) throw new RuntimeException('Order not found.');

            $status        = strtolower((string)$order['status']);
            $paymentStatus = strtolower((string)$order['payment_status']);

            $itemStmt = $db->prepare(
                'SELECT oi.*, f.vendor_net_amount
                 FROM order_items oi
                 LEFT JOIN order_item_financials f ON f.order_item_id = oi.id
                 WHERE oi.order_id = :oid FOR UPDATE'
            );
            $itemStmt->execute(['oid' => $orderId]);
            $items = $itemStmt->fetchAll();

            $refundAmount    = 0.0;
            $vendorReversals = [];

            foreach ($items as $it) {
                if (in_array($it['status'], ['cancelled', 'delivered', 'returned'], true)) continue;

                $db->prepare(
                    'UPDATE inventory_levels
                     SET stock_quantity = stock_quantity + :q, updated_at = UTC_TIMESTAMP()
                     WHERE variant_id = :v'
                )->execute(['q' => (int)$it['quantity'], 'v' => $it['variant_id']]);

                $lvl = $db->prepare('SELECT stock_quantity, reserved_quantity FROM inventory_levels WHERE variant_id = :v');
                $lvl->execute(['v' => $it['variant_id']]);
                $after = $lvl->fetch() ?: ['stock_quantity' => 0, 'reserved_quantity' => 0];

                try {
                    $db->prepare(
                        'INSERT INTO inventory_movements
                           (variant_id, vendor_id, movement_type, quantity_delta, reserved_delta,
                            stock_after, reserved_after, reference_type, reference_id, note,
                            actor_type, actor_id, created_at)
                         VALUES (:v, :vn, "cancellation", :q, 0, :s, :r, "order_item", :ref,
                                 "E2E cancellation", "customer", :actor, UTC_TIMESTAMP())'
                    )->execute([
                        'v'     => $it['variant_id'],
                        'vn'    => $it['vendor_id'],
                        'q'     => (int)$it['quantity'],
                        's'     => $after['stock_quantity'],
                        'r'     => $after['reserved_quantity'],
                        'ref'   => (string)$it['id'],
                        'actor' => $userId,
                    ]);
                } catch (PDOException $e) {
                    if ((string)$e->getCode() !== '23000') throw $e;
                }

                if (!empty($it['vendor_id']) && !empty($it['vendor_net_amount'])) {
                    $vendorReversals[$it['vendor_id']] =
                        ($vendorReversals[$it['vendor_id']] ?? 0.0) + (float)$it['vendor_net_amount'];
                }

                $refundAmount += (float)$it['line_total'];

                $db->prepare('UPDATE order_items SET status = "cancelled", cancelled_quantity = quantity WHERE id = :id')
                   ->execute(['id' => $it['id']]);
            }

            foreach ($vendorReversals as $vendorId => $net) {
                try {
                    $db->prepare(
                        'INSERT INTO vendor_ledger_entries
                           (id, vendor_id, entry_type, amount, currency, balance_bucket,
                            reference_type, reference_id, description, available_at, created_at)
                         VALUES (:id, :v, "order_cancellation", :amt, "INR", "reversed",
                                 "order", :ref, :desc, NULL, UTC_TIMESTAMP())'
                    )->execute([
                        'id'   => uuid_string(),
                        'v'    => $vendorId,
                        'amt'  => -1 * abs($net),
                        'ref'  => (string)$orderId,
                        'desc' => 'E2E cancellation reversal',
                    ]);
                } catch (PDOException $e) {
                    if ((string)$e->getCode() !== '23000') throw $e;
                }
            }

            // Shipping refund on full cancel
            $remStmt = $db->prepare('SELECT COUNT(*) FROM order_items WHERE order_id = ? AND status NOT IN ("cancelled","returned")');
            $remStmt->execute([$orderId]);
            $isFullCancel = ((int)$remStmt->fetchColumn() === 0);
            if ($isFullCancel && (float)$order['shipping_amount'] > 0) {
                $refundAmount += (float)$order['shipping_amount'];
            }

            $db->prepare(
                'UPDATE orders SET status = "cancelled", cancelled_at = UTC_TIMESTAMP(),
                                   cancelled_reason = :r, updated_at = UTC_TIMESTAMP()
                 WHERE id = :id'
            )->execute(['r' => $reason, 'id' => $orderId]);

            $refund = ['method' => 'none', 'status' => 'not_applicable', 'amount' => 0.0];
            if ($paymentStatus === 'paid') {
                $pay = $db->prepare(
                    'SELECT * FROM payment_attempts WHERE order_id = :o AND status = "paid"
                     ORDER BY created_at DESC LIMIT 1'
                );
                $pay->execute(['o' => $orderId]);
                $payment = $pay->fetch();

                $refundId = uuid_string();
                if ($payment && !empty($payment['provider_payment_id'])) {
                    $db->prepare(
                        'INSERT INTO refunds
                           (id, return_request_id, order_id, amount, currency, method, source, status,
                            provider_payment_id, reason, recommended_by_type, recommended_by_id,
                            created_at, updated_at)
                         VALUES (:id, NULL, :o, :amt, "INR", "razorpay", "cancellation", "processing",
                                 :ppid, :reason, "customer", :uid,
                                 UTC_TIMESTAMP(), UTC_TIMESTAMP())'
                    )->execute([
                        'id'     => $refundId,
                        'o'      => $orderId,
                        'amt'    => $refundAmount,
                        'ppid'   => $payment['provider_payment_id'],
                        'reason' => $reason,
                        'uid'    => $userId,
                    ]);
                    $refund = ['method' => 'razorpay', 'status' => 'processing',
                               'amount' => $refundAmount, 'refundId' => $refundId];
                } else {
                    $db->prepare(
                        'INSERT INTO refunds
                           (id, return_request_id, order_id, amount, currency, method, source, status,
                            reason, recommended_by_type, recommended_by_id,
                            created_at, updated_at)
                         VALUES (:id, NULL, :o, :amt, "INR", "manual", "cancellation", "recommended",
                                 :reason, "customer", :uid,
                                 UTC_TIMESTAMP(), UTC_TIMESTAMP())'
                    )->execute([
                        'id'     => $refundId,
                        'o'      => $orderId,
                        'amt'    => $refundAmount,
                        'reason' => $reason,
                        'uid'    => $userId,
                    ]);
                    $refund = ['method' => 'manual', 'status' => 'recommended',
                               'amount' => $refundAmount, 'refundId' => $refundId];
                }
            } elseif ($paymentStatus === 'pending') {
                $refund = ['method' => 'cod', 'status' => 'not_applicable', 'amount' => 0.0];
            }

            // Rewards adjustments (guarded — may not exist in some test envs)
            if (function_exists('reward_refund_for_order')) {
                reward_refund_for_order($userId, $orderId, (string)$order['order_number']);
            }
            if (function_exists('reward_clawback_for_order')) {
                reward_clawback_for_order($userId, $orderId, (string)$order['order_number']);
            }

            if (function_exists('audit_event')) {
                audit_event('customer.order_cancelled', 'order', (string)$orderId, [
                    'reason' => $reason,
                    'refund' => $refund,
                ], [
                    'principal_type' => 'customer',
                    'principal_id'   => $userId,
                ]);
            }

            if (function_exists('create_notification')) {
                create_notification(
                    'customer',
                    $userId,
                    'order_update',
                    'Order cancellation confirmed',
                    'Your order ' . $order['order_number'] . ' has been cancelled.',
                    null,
                    ['order_number' => $order['order_number']]
                );
            }

            $db->commit();
            return ['success' => true, 'refund' => $refund, 'refund_amount' => $refundAmount];

        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }
    }
}
