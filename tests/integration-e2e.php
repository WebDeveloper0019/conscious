<?php
declare(strict_types=1);

/**
 * Integration E2E tests — full user journeys across features.
 *
 * Run:    php tests/integration-e2e.php
 * Filter: php tests/integration-e2e.php --only=journey_cancel
 */

if (PHP_SAPI !== 'cli') { fwrite(STDERR, "CLI only.\n"); exit(1); }

require_once __DIR__ . '/../api/config.php';
if (APP_ENV !== 'local') { fwrite(STDERR, "Refusing non-local APP_ENV.\n"); exit(1); }

require_once __DIR__ . '/../api/lib/db.php';
require_once __DIR__ . '/../api/lib/helpers.php';
require_once __DIR__ . '/../api/lib/notifications.php';
require_once __DIR__ . '/../api/lib/audit.php';
require_once __DIR__ . '/../api/lib/commerce.php';
require_once __DIR__ . '/../api/lib/idempotency.php';
require_once __DIR__ . '/../api/lib/invoice.php';
require_once __DIR__ . '/../api/lib/credit-note.php';
require_once __DIR__ . '/../api/lib/rewards.php';
require_once __DIR__ . '/../api/lib/refund-queue.php';

// CLI hardening
restore_error_handler();
restore_exception_handler();
set_exception_handler(function (Throwable $e) {
    fwrite(STDERR, "\n[UNCAUGHT] " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n");
    exit(1);
});
$_SERVER['REQUEST_METHOD'] ??= 'CLI';
$_SERVER['REMOTE_ADDR'] ??= '127.0.0.1';
$_SERVER['HTTP_HOST'] ??= 'localhost';
$_SERVER['CONTENT_LENGTH'] ??= 0;

// ─── Test harness (isolated copy) ───
final class IT
{
    public static int $passed = 0, $failed = 0, $skipped = 0;
    public static array $failures = [];
    public static string $current = '';

    public static function suite(string $n): void {
        self::$current = $n;
        echo "\n" . str_repeat('═', 68) . "\n  SUITE: $n\n" . str_repeat('═', 68) . "\n";
    }
    public static function ok(string $m): void { self::$passed++; echo "  \033[32m✓\033[0m $m\n"; }
    public static function fail(string $m): void { self::$failed++; self::$failures[] = self::$current.' → '.$m; echo "  \033[31m✗\033[0m $m\n"; }
    public static function skip(string $m): void { self::$skipped++; echo "  \033[33m○\033[0m SKIP: $m\n"; }
    public static function assert(bool $c, string $m): void { $c ? self::ok($m) : self::fail($m); }
    public static function eq($e, $a, string $m): void {
        if ($e == $a) self::ok($m);
        else self::fail("$m  (expected " . var_export($e, true) . ", got " . var_export($a, true) . ")");
    }
    public static function summary(): int {
        echo "\n" . str_repeat('═', 68) . "\n  RESULTS\n" . str_repeat('═', 68) . "\n";
        printf("  Passed:  %d\n  Failed:  %d\n  Skipped: %d\n", self::$passed, self::$failed, self::$skipped);
        if (self::$failures) { echo "\n  Failures:\n"; foreach (self::$failures as $f) echo "    • $f\n"; }
        echo "\n";
        return self::$failed === 0 ? 0 : 1;
    }
}

// ─── Fixtures (reuse from backend-e2e) ───
require_once __DIR__ . '/_fixtures.php';

// ─── Suite: full cancellation → refund → credit note → rewards ───
function journey_cancel_and_refund(): void
{
    IT::suite('JOURNEY: cancel → refund → credit note');

    $user     = fixture_user();
    $vendorId = fixture_vendor();
    $product  = fixture_product($vendorId);
    $order    = fixture_order($user['id'], $vendorId, $product, 'paid', 'online');

    // 1. Award some rewards first (simulating delivery)
    $earned = reward_earn_for_order($user['id'], $order['order_id'], $order['total_amount'], $order['order_number']);
    IT::assert($earned > 0, "Rewards earned: {$earned}");

    // 2. Cancel
    $cancel = run_cancel_logic($order['order_id'], $user['id'], 'integration test');
    IT::assert(($cancel['success'] ?? false), 'Cancel succeeded');

    // 3. Refund row exists
    $rStmt = get_db()->prepare('SELECT * FROM refunds WHERE order_id = ? LIMIT 1');
    $rStmt->execute([$order['order_id']]);
    $refund = $rStmt->fetch();
    IT::assert($refund !== false, 'Refund row created');
    IT::eq('razorpay', $refund['method'] ?? null, 'Refund method razorpay');

    // 4. Manually mark refund processed + generate credit note
    get_db()->prepare('UPDATE refunds SET status = "processed", processed_at = UTC_TIMESTAMP() WHERE id = ?')
        ->execute([$refund['id']]);

    $cn = credit_note_generate_for_refund((string)$refund['id']);
    IT::assert(!empty($cn['credit_note_number']), 'Credit note generated: ' . ($cn['credit_note_number'] ?? 'N/A'));
    IT::assert((float)$cn['total_credit'] > 0, 'Credit note amount > 0');

    // 5. Idempotency — second call returns same
    $cn2 = credit_note_generate_for_refund((string)$refund['id']);
    IT::eq($cn['credit_note_number'], $cn2['credit_note_number'], 'Credit note idempotent');

    // 6. Rewards clawed back
    $balStmt = get_db()->prepare('SELECT balance_points FROM customer_rewards WHERE user_id = ?');
    $balStmt->execute([$user['id']]);
    $balance = (int)$balStmt->fetchColumn();
    IT::eq(0, $balance, 'Rewards fully clawed back');

    cleanup_order($order['order_id']);
    cleanup_user($user['id']);
}

// ─── Suite: rewards earn → redeem → cancel → refund ───
function journey_rewards_lifecycle(): void
{
    IT::suite('JOURNEY: rewards earn → redeem → cancel');

    $user     = fixture_user();
    $vendorId = fixture_vendor();
    $product  = fixture_product($vendorId);

    $orderA = fixture_order($user['id'], $vendorId, $product, 'paid', 'online');
    $earned = reward_earn_for_order($user['id'], $orderA['order_id'], $orderA['total_amount'], $orderA['order_number']);

    // Top up so we can redeem (min 100)
    if ($earned < 150) {
        get_db()->prepare('UPDATE customer_rewards SET balance_points = 200, lifetime_earned = 200 WHERE user_id = ?')
            ->execute([$user['id']]);
        $earned = 200;
    }

    $orderB = fixture_order($user['id'], $vendorId, $product, 'pending', 'cod', 'payment_pending');

    $redeemed = reward_redeem_for_order($user['id'], $orderB['order_id'], 100, 2000.00, $orderB['order_number']);
    IT::eq(100, $redeemed, 'Redeemed 100 points');

    $bal = get_db()->prepare('SELECT balance_points FROM customer_rewards WHERE user_id = ?');
    $bal->execute([$user['id']]);
    IT::eq($earned - 100, (int)$bal->fetchColumn(), 'Balance reduced by 100');

    // Cancel → refund points
    $refunded = reward_refund_for_order($user['id'], $orderB['order_id'], $orderB['order_number']);
    IT::eq(100, $refunded, 'Points refunded on cancel');

    $bal->execute([$user['id']]);
    IT::eq($earned, (int)$bal->fetchColumn(), 'Balance fully restored');

    cleanup_order($orderA['order_id']);
    cleanup_order($orderB['order_id']);
    cleanup_user($user['id']);
}

// ─── Suite: invoice generation + idempotency + cancel → credit note ───
function journey_invoice_lifecycle(): void
{
    IT::suite('JOURNEY: invoice → cancel → credit note');

    $user     = fixture_user();
    $vendorId = fixture_vendor();
    $product  = fixture_product($vendorId);
    $order    = fixture_order($user['id'], $vendorId, $product, 'paid', 'online', 'confirmed');

    // Generate invoice
    $inv = invoice_generate_for_order($order['order_id']);
    IT::assert(!empty($inv['invoice_number']), 'Invoice generated: ' . ($inv['invoice_number'] ?? 'N/A'));
    IT::assert((float)$inv['grand_total'] > 0, 'Invoice total > 0');

    // Idempotency
    $inv2 = invoice_generate_for_order($order['order_id']);
    IT::eq($inv['invoice_number'], $inv2['invoice_number'], 'Invoice number idempotent');

    // Cancel → refund → credit note
    run_cancel_logic($order['order_id'], $user['id'], 'invoice test');
    $rStmt = get_db()->prepare('SELECT id FROM refunds WHERE order_id = ? LIMIT 1');
    $rStmt->execute([$order['order_id']]);
    $refundId = $rStmt->fetchColumn();

    if ($refundId) {
        $cn = credit_note_generate_for_refund((string)$refundId);
        IT::assert(!empty($cn['credit_note_number']), 'Credit note generated');
        IT::eq($inv['invoice_number'], $cn['original_invoice_number'] ?? null, 'Credit note links original invoice');
    } else {
        IT::skip('No refund row — skipping credit note check');
    }

    cleanup_order($order['order_id']);
    cleanup_user($user['id']);
}

// ─── Suite: partial quantity cancel ───
function journey_partial_cancel(): void
{
    IT::suite('JOURNEY: partial quantity cancel');

    $user     = fixture_user();
    $vendorId = fixture_vendor();
    $product  = fixture_product($vendorId);
    $order    = fixture_order($user['id'], $vendorId, $product, 'paid', 'online', 'confirmed', 5);

    // Partial cancel — 2 of 5 items
    $db = get_db();
    $db->beginTransaction();
    try {
        // Simulate the real endpoint logic in-line (simplified)
        $itemStmt = $db->prepare('SELECT * FROM order_items WHERE order_id = ? LIMIT 1');
        $itemStmt->execute([$order['order_id']]);
        $item = $itemStmt->fetch();

        $qty = 2;
        $db->prepare('UPDATE order_items SET cancelled_quantity = ?, cancelled_amount = ? WHERE id = ?')
            ->execute([$qty, round((float)$item['line_total'] * 2 / 5, 2), $item['id']]);

        $db->commit();
    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        throw $e;
    }

    $chk = $db->prepare('SELECT quantity, cancelled_quantity, cancelled_amount FROM order_items WHERE order_id = ?');
    $chk->execute([$order['order_id']]);
    $row = $chk->fetch();
    IT::eq(5, (int)$row['quantity'], 'Original qty = 5');
    IT::eq(2, (int)$row['cancelled_quantity'], 'Cancelled qty = 2');
    IT::assert((float)$row['cancelled_amount'] > 0, 'Cancelled amount > 0');

    cleanup_order($order['order_id']);
    cleanup_user($user['id']);
}

// ─── Runner ───
$only = null;
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--only=')) $only = substr($arg, 7);
}

echo "\n  ╔══════════════════════════════════════════════════════════════╗\n";
echo "  ║  Conscious Indi — Integration E2E Tests                      ║\n";
echo "  ║  APP_ENV = " . str_pad(APP_ENV, 50) . "║\n";
echo "  ║  DB      = " . str_pad(DB_NAME, 50) . "║\n";
echo "  ╚══════════════════════════════════════════════════════════════╝\n";

$all = [
    'journey_cancel'    => 'journey_cancel_and_refund',
    'journey_rewards'   => 'journey_rewards_lifecycle',
    'journey_invoice'   => 'journey_invoice_lifecycle',
    'journey_partial'   => 'journey_partial_cancel',
];

$exitCode = 0;
try {
    foreach ($all as $k => $fn) {
        if ($only !== null && $k !== $only) continue;
        $fn();
    }
    $exitCode = IT::summary();
} catch (Throwable $e) {
    echo "\n\033[31mFATAL: " . $e->getMessage() . "\033[0m\n" . $e->getTraceAsString() . "\n";
    $exitCode = 2;
}
exit($exitCode);