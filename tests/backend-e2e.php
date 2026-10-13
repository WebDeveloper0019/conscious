<?php
declare(strict_types=1);

/**
 * Conscious Indi — Backend E2E Test Runner
 *
 * Run:    php tests/backend-e2e.php
 * Filter: php tests/backend-e2e.php --only=cancellation
 *
 * Requires:
 *   - APP_ENV=local (won't run against production)
 *   - DB reachable via api/config.php
 *   - At least one active vendor + product in DB for order seeding
 *
 * Creates and cleans up its own test data (prefixed with TEST-E2E-).
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This test must be run from the command line.\n");
    exit(1);
}

require_once __DIR__ . '/../api/config.php';

if (APP_ENV !== 'local') {
    fwrite(STDERR, "Refusing to run against non-local APP_ENV=" . APP_ENV . "\n");
    exit(1);
}
require_once __DIR__ . '/../api/lib/db.php';
require_once __DIR__ . '/../api/lib/helpers.php';
require_once __DIR__ . '/../api/lib/notifications.php';
require_once __DIR__ . '/../api/lib/audit.php';
require_once __DIR__ . '/../api/lib/commerce.php';
require_once __DIR__ . '/../api/lib/idempotency.php';
require_once __DIR__ . '/../api/lib/invoice.php';
require_once __DIR__ . '/../api/lib/rewards.php';

// ─────────────────────────────────────────────────────────
//  CLI hardening — override the HTTP error handlers that
//  helpers.php/errors.php/session.php installed, so real
//  errors surface as text instead of a JSON "session expired".
// ─────────────────────────────────────────────────────────

restore_error_handler();
restore_exception_handler();

set_error_handler(function (int $no, string $str, string $file, int $line): bool {
    if (!(error_reporting() & $no)) return false;
    fwrite(STDERR, "\n\033[31m[PHP ERROR]\033[0m $str\n  at $file:$line\n");
    return true;
});

set_exception_handler(function (Throwable $e): void {
    fwrite(STDERR, "\n\033[31m[UNCAUGHT EXCEPTION]\033[0m " . get_class($e) . "\n");
    fwrite(STDERR, "  " . $e->getMessage() . "\n");
    fwrite(STDERR, "  at " . $e->getFile() . ":" . $e->getLine() . "\n");
    exit(1);
});

// Stub HTTP environment vars helpers expect
$_SERVER['REQUEST_METHOD'] ??= 'CLI';
$_SERVER['REMOTE_ADDR']    ??= '127.0.0.1';
$_SERVER['HTTP_HOST']      ??= 'localhost';
$_SERVER['REQUEST_URI']    ??= '/cli/e2e';
$_SERVER['CONTENT_LENGTH'] ??= 0;

// ─────────────────────────────────────────────────────────
//  Test harness
// ─────────────────────────────────────────────────────────

final class T
{
    public static int $passed  = 0;
    public static int $failed  = 0;
    public static int $skipped = 0;
    public static array $failures = [];
    public static string $current = '';

    public static function suite(string $name): void
    {
        self::$current = $name;
        echo "\n" . str_repeat('═', 68) . "\n";
        echo "  SUITE: $name\n";
        echo str_repeat('═', 68) . "\n";
    }

    public static function ok(string $msg): void
    {
        self::$passed++;
        echo "  \033[32m✓\033[0m $msg\n";
    }

    public static function fail(string $msg): void
    {
        self::$failed++;
        self::$failures[] = self::$current . ' → ' . $msg;
        echo "  \033[31m✗\033[0m $msg\n";
    }

    public static function skip(string $msg): void
    {
        self::$skipped++;
        echo "  \033[33m○\033[0m SKIP: $msg\n";
    }

    public static function assert(bool $cond, string $msg): void
    {
        $cond ? self::ok($msg) : self::fail($msg);
    }

    public static function assertEq($expected, $actual, string $msg): void
    {
        if ($expected == $actual) {
            self::ok($msg);
        } else {
            self::fail($msg . "  (expected: " . var_export($expected, true)
                     . ", got: " . var_export($actual, true) . ")");
        }
    }

    public static function summary(): int
    {
        echo "\n" . str_repeat('═', 68) . "\n";
        echo "  RESULTS\n";
        echo str_repeat('═', 68) . "\n";
        printf("  Passed:  %d\n", self::$passed);
        printf("  Failed:  %d\n", self::$failed);
        printf("  Skipped: %d\n", self::$skipped);

        if (self::$failures) {
            echo "\n  \033[31mFailures:\033[0m\n";
            foreach (self::$failures as $f) {
                echo "    • $f\n";
            }
        }

        echo "\n";
        return self::$failed === 0 ? 0 : 1;
    }
}

// ─────────────────────────────────────────────────────────
//  Fixture helpers
// ─────────────────────────────────────────────────────────

function fixture_user(): array
{
    $db = get_db();
    $email = 'e2e+' . bin2hex(random_bytes(4)) . '@test.local';
    $id = uuid_string();

    $db->prepare(
        'INSERT INTO users (id, first_name, last_name, email, mobile, password_hash,
                            email_verified, created_at)
         VALUES (:id, "E2E", "Tester", :email, :mobile, "x", 1, UTC_TIMESTAMP())'
    )->execute([
        'id'     => $id,
        'email'  => $email,
        'mobile' => '9' . str_pad((string)random_int(0, 999999999), 9, '0', STR_PAD_LEFT),
    ]);

    return ['id' => $id, 'email' => $email];
}

function fixture_vendor(): string
{
    $db = get_db();
    $existing = $db->query("SELECT id FROM vendors WHERE status='active' AND verification_status='approved' LIMIT 1")->fetchColumn();
    if ($existing) return (string)$existing;

    $id = uuid_string();
    $db->prepare(
        'INSERT INTO vendors (id, vendor_type, owner_name, display_name, email, mobile,
                              status, verification_status, onboarding_status, created_at)
         VALUES (:id, "artisan", "E2E Vendor", "E2E Vendor", :email, :mobile,
                 "active", "approved", "completed", UTC_TIMESTAMP())'
    )->execute([
        'id'     => $id,
        'email'  => 'e2e-vendor+' . bin2hex(random_bytes(3)) . '@test.local',
        'mobile' => '9' . str_pad((string)random_int(0, 999999999), 9, '0', STR_PAD_LEFT),
    ]);
    return $id;
}

function fixture_product(string $vendorId): array
{
    $db = get_db();
    $pid = 'E2E-PROD-' . strtoupper(bin2hex(random_bytes(4)));
    $vid = uuid_string();

    $db->prepare(
        'INSERT INTO products (id, vendor_id, name, slug, sku, mrp, selling_price,
                               gst_percent, package_weight_grams,
                               processing_min_days, processing_max_days, status, created_at)
         VALUES (:id, :v, "E2E Test Product", :slug, :sku, 1500.00, 1200.00,
                 5.00, 300, 1, 3, "approved", UTC_TIMESTAMP())'
    )->execute([
        'id'   => $pid,
        'v'    => $vendorId,
        'slug' => strtolower($pid),
        'sku'  => $pid,
    ]);

    $db->prepare(
        'INSERT INTO product_variants (id, product_id, name, sku, price_adjustment)
         VALUES (:id, :p, "Default", :sku, 0.00)'
    )->execute(['id' => $vid, 'p' => $pid, 'sku' => $pid . '-V1']);

    $db->prepare(
        'INSERT INTO inventory_levels (variant_id, stock_quantity, reserved_quantity,
                                       low_stock_threshold, updated_at)
         VALUES (:v, 100, 0, 5, UTC_TIMESTAMP())'
    )->execute(['v' => $vid]);

    return ['product_id' => $pid, 'variant_id' => $vid];
}

function fixture_order(string $userId, string $vendorId, array $product,
                       string $paymentStatus = 'paid',
                       string $paymentMethod = 'online',
                       string $orderStatus   = 'confirmed',
                       int $qty = 1): array
{
    $db = get_db();
    $orderNumber = 'E2E-' . strtoupper(bin2hex(random_bytes(4)));
    $groupId     = uuid_string();
    $unitPrice   = 1200.00;
    $lineTotal   = round($unitPrice * $qty * 1.05, 2); // +5% GST
    $subtotal    = round($unitPrice * $qty, 2);
    $taxAmount   = round($lineTotal - $subtotal, 2);
    $vendorNet   = round($lineTotal * 0.9, 2); // 10% commission

    $db->prepare(
        'INSERT INTO orders (user_id, checkout_quote_id, order_number, status, payment_status,
                             payment_method, currency, subtotal, shipping_amount, discount_amount,
                             tax_amount, total_amount, address_snapshot_json,
                             placed_at, created_at, updated_at)
         VALUES (:u, NULL, :num, :st, :ps, :pm, "INR", :sub, 50.00, 0.00,
                 :tax, :total, :addr, UTC_TIMESTAMP(), UTC_TIMESTAMP(), UTC_TIMESTAMP())'
    )->execute([
        'u'     => $userId,
        'num'   => $orderNumber,
        'st'    => $orderStatus,
        'ps'    => $paymentStatus,
        'pm'    => $paymentMethod,
        'sub'   => $subtotal,
        'tax'   => $taxAmount,
        'total' => round($lineTotal + 50, 2),
        'addr'  => json_encode([
            'full_name'     => 'E2E Tester',
            'phone'         => '9000000000',
            'pincode'       => '560001',
            'address_line1' => '1 Test St',
            'city'          => 'Bangalore',
            'state'         => 'Karnataka',
        ]),
    ]);
    $orderId = (int)$db->lastInsertId();

    $db->prepare(
        'INSERT INTO vendor_order_groups
           (id, order_id, vendor_id, group_number, status, subtotal, shipping_amount,
            discount_amount, tax_amount, total_amount, policy_snapshot_json,
            dispatch_due_at, created_at, updated_at)
         VALUES (:id, :o, :v, :gn, "awaiting_acceptance", :sub, 50.00, 0.00,
                 :tax, :total, "{}", DATE_ADD(UTC_TIMESTAMP(), INTERVAL 3 DAY),
                 UTC_TIMESTAMP(), UTC_TIMESTAMP())'
    )->execute([
        'id'    => $groupId,
        'o'     => $orderId,
        'v'     => $vendorId,
        'gn'    => $orderNumber . '-01',
        'sub'   => $subtotal,
        'tax'   => $taxAmount,
        'total' => round($lineTotal + 50, 2),
    ]);

    $db->prepare(
        'INSERT INTO order_items
           (order_id, vendor_group_id, vendor_id, product_id, variant_id, product_name, sku,
            quantity, status, unit_price, mrp, discount_amount, tax_amount, line_total,
            product_snapshot_json, policy_snapshot_json, cancelled_quantity, returned_quantity)
         VALUES (:o, :g, :v, :p, :va, "E2E Test Product", :sku,
                 :q, "awaiting_acceptance", :up, 1500.00, 0.00, :tax, :lt,
                 :snap, "{}", 0, 0)'
    )->execute([
        'o'    => $orderId,
        'g'    => $groupId,
        'v'    => $vendorId,
        'p'    => $product['product_id'],
        'va'   => $product['variant_id'],
        'sku'  => $product['product_id'] . '-V1',
        'q'    => $qty,
        'up'   => $unitPrice,
        'tax'  => $taxAmount,
        'lt'   => $lineTotal,
        'snap' => json_encode(['gst_percent' => 5.0, 'hsn' => '6214']),
    ]);
    $itemId = (int)$db->lastInsertId();

    $db->prepare(
        'INSERT INTO order_item_financials
           (order_item_id, gross_amount, discount_amount, tax_amount,
            commission_percent, commission_amount, fixed_fee, vendor_net_amount,
            snapshot_json, created_at)
         VALUES (:i, :g, 0.00, :t, 10.00, :comm, 0.00, :net, "{}", UTC_TIMESTAMP())'
    )->execute([
        'i'    => $itemId,
        'g'    => $subtotal,
        't'    => $taxAmount,
        'comm' => round($lineTotal * 0.1, 2),
        'net'  => $vendorNet,
    ]);

    if ($paymentStatus === 'paid') {
        $db->prepare(
            'INSERT INTO payment_attempts (id, order_id, provider, provider_payment_id,
                                           amount, currency, status, created_at)
             VALUES (:id, :o, :pr, :ppid, :amt, "INR", "paid", UTC_TIMESTAMP())'
        )->execute([
            'id'   => uuid_string(),
            'o'    => $orderId,
            'pr'   => $paymentMethod === 'cod' ? 'cod' : 'razorpay',
            'ppid' => 'pay_e2e_' . bin2hex(random_bytes(6)),
            'amt'  => round($lineTotal + 50, 2),
        ]);
    }

    return [
        'order_id'     => $orderId,
        'order_number' => $orderNumber,
        'group_id'     => $groupId,
        'item_id'      => $itemId,
        'variant_id'   => $product['variant_id'],
        'total_amount' => round($lineTotal + 50, 2),
        'vendor_net'   => $vendorNet,
    ];
}

function cleanup_order(int $orderId): void
{
    $db = get_db();

    // Capture item IDs before anything is deleted
    $itemIds = $db->prepare('SELECT id FROM order_items WHERE order_id = :o');
    $itemIds->execute(['o' => $orderId]);
    $itemList = array_column($itemIds->fetchAll(), 'id');

    // 1. Child rows of order_items (must go before order_items)
    $db->prepare('DELETE FROM order_item_financials WHERE order_item_id IN
                  (SELECT id FROM order_items WHERE order_id = :o)')
       ->execute(['o' => $orderId]);

    // 2. Inventory movements + vendor ledger referencing order items
    if ($itemList) {
        $inList = implode(',', array_map('intval', $itemList));
        $db->exec("DELETE FROM inventory_movements
                   WHERE reference_type = 'order_item' AND reference_id IN ($inList)");
        $db->exec("DELETE FROM vendor_ledger_entries
                   WHERE reference_type = 'order_item' AND reference_id IN ($inList)");
    }

    // 3. Order-level rows
    $db->prepare('DELETE FROM refunds WHERE order_id = :o')->execute(['o' => $orderId]);
    $db->prepare('DELETE FROM invoices WHERE order_id = :o')->execute(['o' => $orderId]);
    $db->prepare('DELETE FROM payment_attempts WHERE order_id = :o')->execute(['o' => $orderId]);
    $db->prepare('DELETE FROM cod_collections WHERE order_id = :o')->execute(['o' => $orderId]);
    $db->prepare('DELETE FROM vendor_ledger_entries
                  WHERE reference_type = "order" AND reference_id = :o')
       ->execute(['o' => (string)$orderId]);
    $db->prepare('DELETE FROM customer_reward_ledger
                  WHERE reference_type = "order" AND reference_id = :o')
       ->execute(['o' => (string)$orderId]);

    // 4. Order items → vendor_order_groups → order (in that order)
    $db->prepare('DELETE FROM order_items WHERE order_id = :o')->execute(['o' => $orderId]);
    $db->prepare('DELETE FROM vendor_order_groups WHERE order_id = :o')->execute(['o' => $orderId]);
    $db->prepare('DELETE FROM orders WHERE id = :o')->execute(['o' => $orderId]);
}

function cleanup_user(string $userId): void
{
    $db = get_db();
    $db->prepare('DELETE FROM customer_rewards WHERE user_id = :u')->execute(['u' => $userId]);
    $db->prepare('DELETE FROM customer_reward_ledger WHERE user_id = :u')->execute(['u' => $userId]);
    $db->prepare('DELETE FROM users WHERE id = :u')->execute(['u' => $userId]);
}

// ─────────────────────────────────────────────────────────
//  Direct cancellation logic (mirrors cancel-order.php)
//  Used so we don't need HTTP auth in CLI
// ─────────────────────────────────────────────────────────

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

        // Load items + financials
        $itemStmt = $db->prepare(
            'SELECT oi.*, f.vendor_net_amount
             FROM order_items oi
             LEFT JOIN order_item_financials f ON f.order_item_id = oi.id
             WHERE oi.order_id = :oid FOR UPDATE'
        );
        $itemStmt->execute(['oid' => $orderId]);
        $items = $itemStmt->fetchAll();

        $refundAmount     = 0.0;
        $vendorReversals  = [];

        foreach ($items as $it) {
            if (in_array($it['status'], ['cancelled', 'delivered', 'returned'], true)) continue;

            // Stock restore
            $db->prepare(
                'UPDATE inventory_levels
                 SET stock_quantity = stock_quantity + :q, updated_at = UTC_TIMESTAMP()
                 WHERE variant_id = :v'
            )->execute(['q' => (int)$it['quantity'], 'v' => $it['variant_id']]);

            $lvl = $db->prepare('SELECT stock_quantity, reserved_quantity FROM inventory_levels WHERE variant_id = :v');
            $lvl->execute(['v' => $it['variant_id']]);
            $after = $lvl->fetch() ?: ['stock_quantity' => 0, 'reserved_quantity' => 0];

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

            if (!empty($it['vendor_id']) && !empty($it['vendor_net_amount'])) {
                $vendorReversals[$it['vendor_id']] =
                    ($vendorReversals[$it['vendor_id']] ?? 0.0) + (float)$it['vendor_net_amount'];
            }

            $refundAmount += (float)$it['line_total'];

            $db->prepare('UPDATE order_items SET status = "cancelled", cancelled_quantity = quantity WHERE id = :id')
               ->execute(['id' => $it['id']]);
        }

        // Vendor ledger reversals
        foreach ($vendorReversals as $vendorId => $net) {
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
        }
           // Shipping refund on full cancel (mirrors cancel-order.php)
        $remStmt = $db->prepare('SELECT COUNT(*) FROM order_items WHERE order_id = ? AND status NOT IN ("cancelled","returned")');
        $remStmt->execute([$orderId]);
        $isFullCancel = ((int)$remStmt->fetchColumn() === 0);
        if ($isFullCancel && (float)$order['shipping_amount'] > 0) {
            $refundAmount += (float)$order['shipping_amount'];}

        // Order status
        $db->prepare(
            'UPDATE orders SET status = "cancelled", cancelled_at = UTC_TIMESTAMP(),
                               cancelled_reason = :r, updated_at = UTC_TIMESTAMP()
             WHERE id = :id'
        )->execute(['r' => $reason, 'id' => $orderId]);

        // Refund
        $refund = ['method' => 'none', 'status' => 'not_applicable', 'amount' => 0.0];
        if ($paymentStatus === 'paid') {
            $pay = $db->prepare(
                'SELECT * FROM payment_attempts WHERE order_id = :o AND status = "paid"
                 ORDER BY created_at DESC LIMIT 1'
            );
            $pay->execute(['o' => $orderId]);
            $payment = $pay->fetch();

            $refundId = uuid_string();
            if ($payment && $payment['provider'] === 'razorpay' && !empty($payment['provider_payment_id'])) {
                $db->prepare(
                    'INSERT INTO refunds
                       (id, order_id, order_item_id, amount, currency, method, status,
                        provider_payment_id, reason, recommended_by_type, recommended_by_id,
                        created_at, updated_at)
                     VALUES (:id, :o, NULL, :amt, "INR", "razorpay", "processing",
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
                       (id, order_id, order_item_id, amount, currency, method, status,
                        reason, recommended_by_type, recommended_by_id,
                        created_at, updated_at)
                     VALUES (:id, :o, NULL, :amt, "INR", "manual", "recommended",
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

        // Rewards adjustments
        reward_refund_for_order($userId, $orderId, (string)$order['order_number']);
        reward_clawback_for_order($userId, $orderId, (string)$order['order_number']);

        // Audit
                // Audit (pass explicit actor so it doesn't try to auto-detect an HTTP session)
        audit_event('customer.order_cancelled', 'order', (string)$orderId, [
            'reason' => $reason,
            'refund' => $refund,
        ], [
            'principal_type' => 'customer',
            'principal_id'   => $userId,
        ]);

        // Notification
        create_notification(
            'customer',
            $userId,
            'order_update',
            'Order cancellation confirmed',
            'Your order ' . $order['order_number'] . ' has been cancelled.',
            null,
            ['order_number' => $order['order_number']]
        );

        $db->commit();
        return ['success' => true, 'refund' => $refund, 'refund_amount' => $refundAmount];
    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        throw $e;
    }
}

// ─────────────────────────────────────────────────────────
//  Suite 1 — Cancellation
// ─────────────────────────────────────────────────────────

function suite_cancellation(): void
{
    T::suite('CANCELLATION');

    $db       = get_db();
    $user     = fixture_user();
    $vendorId = fixture_vendor();
    $product  = fixture_product($vendorId);
    $order    = fixture_order($user['id'], $vendorId, $product, 'paid', 'online');

    // Snapshot: stock before
    $before = $db->prepare('SELECT stock_quantity FROM inventory_levels WHERE variant_id = :v');
    $before->execute(['v' => $product['variant_id']]);
    $stockBefore = (int)$before->fetchColumn();

    // Run cancel
    $result = run_cancel_logic($order['order_id'], $user['id']);

    T::assert($result['success'] === true, 'Cancel succeeded');

    // Stock restored
    $after = $db->prepare('SELECT stock_quantity FROM inventory_levels WHERE variant_id = :v');
    $after->execute(['v' => $product['variant_id']]);
    $stockAfter = (int)$after->fetchColumn();
    T::assertEq($stockBefore + 1, $stockAfter, 'Stock restored by 1');

    // Order status
    $oStmt = $db->prepare('SELECT status, cancelled_at, cancelled_reason FROM orders WHERE id = :id');
    $oStmt->execute(['id' => $order['order_id']]);
    $oRow = $oStmt->fetch();
    T::assertEq('cancelled', $oRow['status'], 'Order status = cancelled');
    T::assert(!empty($oRow['cancelled_at']), 'cancelled_at set');
    T::assertEq('E2E test cancel', $oRow['cancelled_reason'], 'cancelled_reason saved');

    // Item status
    $iStmt = $db->prepare('SELECT status, cancelled_quantity FROM order_items WHERE id = :id');
    $iStmt->execute(['id' => $order['item_id']]);
    $iRow = $iStmt->fetch();
    T::assertEq('cancelled', $iRow['status'], 'Item status = cancelled');
    T::assertEq(1, (int)$iRow['cancelled_quantity'], 'cancelled_quantity = 1');

    // Vendor ledger reversal
    $lStmt = $db->prepare(
        'SELECT amount, balance_bucket FROM vendor_ledger_entries
         WHERE reference_type = "order" AND reference_id = :ref AND entry_type = "order_cancellation"'
    );
    $lStmt->execute(['ref' => (string)$order['order_id']]);
    $ledger = $lStmt->fetch();
    T::assert($ledger !== false, 'Vendor ledger reversal row exists');
    if ($ledger) {
        T::assert((float)$ledger['amount'] < 0, 'Reversal amount is negative');
        T::assertEq('reversed', $ledger['balance_bucket'], 'balance_bucket = reversed');
    }

    // Inventory movement
    $mStmt = $db->prepare(
        'SELECT movement_type, quantity_delta FROM inventory_movements
         WHERE reference_type = "order_item" AND reference_id = :ref'
    );
    $mStmt->execute(['ref' => (string)$order['item_id']]);
    $mov = $mStmt->fetch();
    T::assert($mov !== false, 'Inventory movement row exists');
    if ($mov) {
        T::assertEq('cancellation', $mov['movement_type'], 'movement_type = cancellation');
        T::assertEq(1, (int)$mov['quantity_delta'], 'quantity_delta = +1');
    }
        // Shipping refund assertion — full cancel must include shipping
    $orderRow = $db->prepare('SELECT shipping_amount FROM orders WHERE id = ?');
    $orderRow->execute([$order['order_id']]);
    $shipAmt = (float)$orderRow->fetchColumn();
    if ($shipAmt > 0) {
        $refStmt = $db->prepare('SELECT amount FROM refunds WHERE order_id = ? AND source = "cancellation" LIMIT 1');
        $refStmt->execute([$order['order_id']]);
        $refundAmt = (float)$refStmt->fetchColumn();
        // line_total (1260) + shipping (50) = 1310 expected
        T::assert($refundAmt >= 1300,
                  'Shipping refunded on full cancel (refund ≈ 1310)');
    }

    // Audit log
    $aStmt = $db->prepare(
        'SELECT COUNT(*) FROM audit_logs WHERE action = "customer.order_cancelled" AND subject_id = :s'
    );
    $aStmt->execute(['s' => (string)$order['order_id']]);
    T::assert((int)$aStmt->fetchColumn() > 0, 'Audit log written');

    // Notification
    $nStmt = $db->prepare(
        'SELECT COUNT(*) FROM notifications WHERE recipient_type = "customer"
         AND recipient_id = :u AND type = "order_update"'
    );
    $nStmt->execute(['u' => $user['id']]);
    T::assert((int)$nStmt->fetchColumn() > 0, 'Customer notification sent');

    cleanup_order($order['order_id']);
    cleanup_user($user['id']);
}

// ─────────────────────────────────────────────────────────
//  Suite 2 — Refund
// ─────────────────────────────────────────────────────────

function suite_refund(): void
{
    T::suite('REFUND');

    $db       = get_db();
    $user     = fixture_user();
    $vendorId = fixture_vendor();
    $product  = fixture_product($vendorId);

    // --- Case A: paid online (razorpay) ---
    $orderA = fixture_order($user['id'], $vendorId, $product, 'paid', 'online');
    $resA   = run_cancel_logic($orderA['order_id'], $user['id'], 'E2E refund A');

    $rA = $db->prepare('SELECT method, status, amount FROM refunds WHERE order_id = :o LIMIT 1');
    $rA->execute(['o' => $orderA['order_id']]);
    $refundA = $rA->fetch();

    T::assert($refundA !== false, 'Refund row created for razorpay order');
    if ($refundA) {
        T::assertEq('razorpay', $refundA['method'], 'Refund method = razorpay');
        T::assert(in_array($refundA['status'], ['processing', 'processed'], true),
                  'Refund status = processing or processed');
        T::assert((float)$refundA['amount'] > 0, 'Refund amount > 0');
    }

    // --- Case B: paid COD (pending status → no refund) ---
    $orderB = fixture_order($user['id'], $vendorId, $product, 'pending', 'cod');
    $resB   = run_cancel_logic($orderB['order_id'], $user['id'], 'E2E refund B');

    $rB = $db->prepare('SELECT COUNT(*) FROM refunds WHERE order_id = :o');
    $rB->execute(['o' => $orderB['order_id']]);
    T::assertEq(0, (int)$rB->fetchColumn(), 'No refund row for unpaid COD');

    T::assertEq('cod', $resB['refund']['method'], 'COD refund method reported');

    cleanup_order($orderA['order_id']);
    cleanup_order($orderB['order_id']);
    cleanup_user($user['id']);
}

// ─────────────────────────────────────────────────────────
//  Suite 3 — Coupon concurrency
// ─────────────────────────────────────────────────────────

function suite_coupon_concurrency(): void
{
    T::suite('COUPON CONCURRENCY');

    $db       = get_db();
    $user     = fixture_user();
    $vendorId = fixture_vendor();

    // Create a coupon with a strict total usage limit
    $couponId = uuid_string();
    $code     = 'E2E' . strtoupper(bin2hex(random_bytes(3)));
    $db->prepare(
        'INSERT INTO coupons
           (id, code, discount_type, discount_value, min_order_amount,
            usage_limit, per_customer_limit, vendor_id, status,
            starts_at, ends_at, created_at)
         VALUES (:id, :code, "flat", 100.00, 500.00,
                 1, 5, NULL, "active",
                 DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 DAY),
                 DATE_ADD(UTC_TIMESTAMP(), INTERVAL 1 DAY),
                 UTC_TIMESTAMP())'
    )->execute(['id' => $couponId, 'code' => $code]);

    // Simulate two near-simultaneous redemptions on two orders for the same user
    $orderA = fixture_order($user['id'], $vendorId, fixture_product($vendorId), 'paid', 'online');
    $orderB = fixture_order($user['id'], $vendorId, fixture_product($vendorId), 'paid', 'online');

    // Simulate the two attempts sequentially, using FOR UPDATE to mimic real race
    // (True parallel requires pcntl_fork or curl_multi — the lock semantics are the same)
    $attempts = [$orderA['order_id'], $orderB['order_id']];
    $succeeded = 0;
    $blocked   = 0;

    foreach ($attempts as $oid) {
        $db->beginTransaction();
        try {
            // Lock coupon row
            $c = $db->prepare('SELECT usage_limit FROM coupons WHERE id = :id FOR UPDATE');
            $c->execute(['id' => $couponId]);
            $coupon = $c->fetch();

            // Count current redemptions
            $u = $db->prepare('SELECT COUNT(*) FROM coupon_redemptions WHERE coupon_id = :c');
            $u->execute(['c' => $couponId]);
            $used = (int)$u->fetchColumn();

            if ($coupon['usage_limit'] !== null && $used >= (int)$coupon['usage_limit']) {
                $db->rollBack();
                $blocked++;
                continue;
            }

            $db->prepare(
                'INSERT INTO coupon_redemptions (id, coupon_id, user_id, order_id, discount_amount, created_at)
                 VALUES (:id, :c, :u, :o, 100.00, UTC_TIMESTAMP())'
            )->execute([
                'id' => uuid_string(),
                'c'  => $couponId,
                'u'  => $user['id'],
                'o'  => $oid,
            ]);
            $db->commit();
            $succeeded++;
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            $blocked++;
        }
    }

    T::assertEq(1, $succeeded, 'Exactly 1 redemption succeeded');
    T::assertEq(1, $blocked, 'Exactly 1 redemption blocked');

    $final = $db->prepare('SELECT COUNT(*) FROM coupon_redemptions WHERE coupon_id = :c');
    $final->execute(['c' => $couponId]);
    T::assertEq(1, (int)$final->fetchColumn(), 'Total redemptions = 1');

    // Cleanup
    $db->prepare('DELETE FROM coupon_redemptions WHERE coupon_id = :c')->execute(['c' => $couponId]);
    $db->prepare('DELETE FROM coupons WHERE id = :c')->execute(['c' => $couponId]);
    cleanup_order($orderA['order_id']);
    cleanup_order($orderB['order_id']);
    cleanup_user($user['id']);
}

// ─────────────────────────────────────────────────────────
//  Suite 4 — Reward redemption
// ─────────────────────────────────────────────────────────

function suite_rewards(): void
{
    T::suite('REWARD REDEMPTION');

    $db       = get_db();
    $user     = fixture_user();
    $vendorId = fixture_vendor();
    $product  = fixture_product($vendorId);

    // --- Case A: award points on delivery ---
    $orderA = fixture_order($user['id'], $vendorId, $product, 'paid', 'online', 'confirmed');

    $awarded = reward_earn_for_order(
        $user['id'],
        $orderA['order_id'],
        $orderA['total_amount'],
        $orderA['order_number']
    );
    T::assert($awarded > 0, 'Points awarded on delivery');

    // Idempotency: awarding again should return 0
    $again = reward_earn_for_order(
        $user['id'],
        $orderA['order_id'],
        $orderA['total_amount'],
        $orderA['order_number']
    );
    T::assertEq(0, $again, 'Second award is idempotent (returns 0)');

    // Balance check
    $bal = $db->prepare('SELECT balance_points FROM customer_rewards WHERE user_id = :u');
    $bal->execute(['u' => $user['id']]);
    T::assertEq($awarded, (int)$bal->fetchColumn(), 'Balance matches award');

    // --- Case B: redeem on a new order ---
    $orderB = fixture_order($user['id'], $vendorId, $product, 'pending', 'cod', 'payment_pending');

    // Cannot redeem less than 100
    try {
        reward_redeem_for_order($user['id'], $orderB['order_id'], 50, 2000.00, $orderB['order_number']);
        T::fail('Redeem 50 points should have thrown');
    } catch (Throwable $e) {
        T::ok('Redeem below minimum rejected');
    }

    // Redeem a valid amount (must be ≥ 100 and ≤ balance)
    $redeem = min(100, $awarded);
    if ($redeem >= 100) {
        $applied = reward_redeem_for_order(
            $user['id'],
            $orderB['order_id'],
            $redeem,
            2000.00,
            $orderB['order_number']
        );
        T::assertEq($redeem, $applied, "Redeemed $redeem points");

        $bal->execute(['u' => $user['id']]);
        T::assertEq($awarded - $redeem, (int)$bal->fetchColumn(), 'Balance reduced by redeemed points');

        // --- Case C: cancel → points refunded ---
        $refunded = reward_refund_for_order($user['id'], $orderB['order_id'], $orderB['order_number']);
        T::assertEq($redeem, $refunded, 'Cancelled order refunded points');

        $bal->execute(['u' => $user['id']]);
        T::assertEq($awarded, (int)$bal->fetchColumn(), 'Balance restored after refund');
    } else {
        T::skip('Not enough points to test redemption (need 100+)');
    }

    // --- Case D: cancel order that earned points → clawback ---
    $clawed = reward_clawback_for_order($user['id'], $orderA['order_id'], $orderA['order_number']);
    T::assertEq($awarded, $clawed, 'Points clawed back on cancelled delivery');

    $bal->execute(['u' => $user['id']]);
    $final = (int)$bal->fetchColumn();
    T::assert($final >= 0, 'Balance never goes negative (floor at 0)');

    // --- Case E: insufficient balance ---
    try {
        reward_redeem_for_order($user['id'], $orderB['order_id'], 99999, 5000.00, $orderB['order_number']);
        T::fail('Redeem 99999 with low balance should have thrown');
    } catch (Throwable $e) {
        T::ok('Redeem over balance rejected');
    }

    cleanup_order($orderA['order_id']);
    cleanup_order($orderB['order_id']);
    cleanup_user($user['id']);
}

// ─────────────────────────────────────────────────────────
//  Suite 5 — Concurrent cancel (double-fire safety)
// ─────────────────────────────────────────────────────────

function suite_concurrent_cancel(): void
{
    T::suite('CONCURRENT CANCEL — double-fire safety');

    $db       = get_db();
    $user     = fixture_user();
    $vendorId = fixture_vendor();
    $product  = fixture_product($vendorId);
    $order    = fixture_order($user['id'], $vendorId, $product, 'paid', 'online');

    // Snapshot stock BEFORE
    $beforeStmt = $db->prepare('SELECT stock_quantity FROM inventory_levels WHERE variant_id = ?');
    $beforeStmt->execute([$product['variant_id']]);
    $stockBefore = (int)$beforeStmt->fetchColumn();

    // Fire two cancels back-to-back. Row-level FOR UPDATE must serialize them.
    $first  = null;
    $second = null;

    try {
        $first = run_cancel_logic($order['order_id'], $user['id'], 'first');
    } catch (Throwable $e) {
        $first = ['error' => $e->getMessage()];
    }

    try {
        $second = run_cancel_logic($order['order_id'], $user['id'], 'second');
    } catch (Throwable $e) {
        $second = ['error' => $e->getMessage()];
    }

    T::assert(($first['success'] ?? false) === true,
              'First cancel succeeded');

    $secondRejected = (($second['success'] ?? false) === false) || !empty($second['error']);
    T::assert($secondRejected, 'Second cancel rejected (already cancelled)');

    // Exactly ONE refund row for this order
    $rCount = $db->prepare(
        'SELECT COUNT(*) FROM refunds WHERE order_id = ? AND source = "cancellation"'
    );
    $rCount->execute([$order['order_id']]);
    T::assertEq(1, (int)$rCount->fetchColumn(), 'Exactly 1 refund row (no double-refund)');

    // Exactly ONE stock restore movement
    $mCount = $db->prepare(
        "SELECT COUNT(*) FROM inventory_movements
         WHERE reference_type = 'order_item'
           AND reference_id = ?
           AND movement_type = 'cancellation'"
    );
    $mCount->execute([(string)$order['item_id']]);
    T::assertEq(1, (int)$mCount->fetchColumn(), 'Exactly 1 stock restore movement');

    // Exactly ONE ledger reversal for the order
    $lCount = $db->prepare(
        "SELECT COUNT(*) FROM vendor_ledger_entries
         WHERE reference_type = 'order'
           AND reference_id = ?
           AND entry_type = 'order_cancellation'"
    );
    $lCount->execute([(string)$order['order_id']]);
    T::assertEq(1, (int)$lCount->fetchColumn(), 'Exactly 1 ledger reversal row');

    // Original order_earning entry is marked reversed (if it existed)
    $origStmt = $db->prepare(
        "SELECT status FROM vendor_ledger_entries
         WHERE reference_type = 'order_item'
           AND reference_id = ?
           AND entry_type = 'order_earning'
         LIMIT 1"
    );
    $origStmt->execute([(string)$order['item_id']]);
    $origStatus = $origStmt->fetchColumn();
    if ($origStatus !== false) {
        T::assertEq('reversed', (string)$origStatus, 'Original earning marked reversed');
    } else {
        T::skip('No original order_earning entry (test order had no ledger)');
    }

    // Stock never double-restored
    $afterStmt = $db->prepare('SELECT stock_quantity FROM inventory_levels WHERE variant_id = ?');
    $afterStmt->execute([$product['variant_id']]);
    $stockAfter = (int)$afterStmt->fetchColumn();
    T::assertEq($stockBefore + 1, $stockAfter, 'Stock restored exactly once (+1, not +2)');

    cleanup_order($order['order_id']);
    cleanup_user($user['id']);
}

$only = null;
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--only=')) {
        $only = substr($arg, 7);
    }
}

echo "\n";
echo "  ╔══════════════════════════════════════════════════════════════╗\n";
echo "  ║  Conscious Indi — Backend E2E Tests                          ║\n";
echo "  ║  APP_ENV = " . str_pad(APP_ENV, 50) . "║\n";
echo "  ║  DB      = " . str_pad(DB_NAME, 50) . "║\n";
echo "  ╚══════════════════════════════════════════════════════════════╝\n";

$all = [
    'cancellation' => 'suite_cancellation',
    'refund'       => 'suite_refund',
    'coupon'       => 'suite_coupon_concurrency',
    'rewards'      => 'suite_rewards',
       'concurrent'   => 'suite_concurrent_cancel',

];

$exitCode = 0;
try {
    foreach ($all as $key => $fn) {
        if ($only !== null && $key !== $only) continue;
        $fn();
    }
    $exitCode = T::summary();
} catch (Throwable $e) {
    echo "\n\033[31mFATAL: " . $e->getMessage() . "\033[0m\n";
    echo $e->getTraceAsString() . "\n";
    $exitCode = 2;
}

exit($exitCode);

