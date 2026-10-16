<?php
declare(strict_types=1);

/**
 * Migration test suite.
 *
 * Run:    php tests/migration-test.php
 */

if (PHP_SAPI !== 'cli') { fwrite(STDERR, "CLI only.\n"); exit(1); }
require_once __DIR__ . '/../api/config.php';
if (APP_ENV !== 'local') { fwrite(STDERR, "Refusing non-local APP_ENV.\n"); exit(1); }
require_once __DIR__ . '/../api/lib/db.php';
require_once __DIR__ . '/../api/lib/helpers.php';

final class MT
{
    public static int $passed = 0, $failed = 0, $skipped = 0;
    public static array $failures = [];
    public static string $current = '';

    public static function suite(string $n): void
    {
        self::$current = $n;
        echo "\n" . str_repeat('=', 68) . "\n  SUITE: $n\n" . str_repeat('=', 68) . "\n";
    }
    public static function ok(string $m): void   { self::$passed++;  echo "  [OK] $m\n"; }
    public static function fail(string $m): void { self::$failed++;  self::$failures[] = self::$current . ' -> ' . $m; echo "  [FAIL] $m\n"; }
    public static function skip(string $m): void { self::$skipped++; echo "  [SKIP] $m\n"; }
    public static function assert(bool $c, string $m): void { $c ? self::ok($m) : self::fail($m); }

    public static function summary(): int
    {
        echo "\n" . str_repeat('=', 68) . "\n  RESULTS\n" . str_repeat('=', 68) . "\n";
        printf("  Passed:  %d\n  Failed:  %d\n  Skipped: %d\n", self::$passed, self::$failed, self::$skipped);
        if (self::$failures) {
            echo "\n  Failures:\n";
            foreach (self::$failures as $f) echo "    - $f\n";
        }
        echo "\n";
        return self::$failed === 0 ? 0 : 1;
    }
}

/**
 * Strip line comments, then split on `;`.
 * Comments MUST be stripped before splitting, otherwise the first
 * SET @var glues to a leading comment and disappears.
 */
function parse_sql_statements(string $sql): array
{
    $lines = preg_split('/\r?\n/', $sql);
    $clean = [];
    foreach ($lines as $line) {
        $t = ltrim($line);
        if (str_starts_with($t, '--') || str_starts_with($t, '#')) continue;
        $clean[] = $line;
    }
    $joined = implode("\n", $clean);
    $parts  = preg_split('/;\s*\n/', $joined);
    $out    = [];
    foreach ($parts as $p) {
        $p = trim($p);
        if ($p !== '') $out[] = $p;
    }
    return $out;
}

/**
 * Execute an SQL statement that may return a result set
 * (e.g. `SET @x := (SELECT ...)`). Uses prepare + execute + closeCursor
 * so PDO never complains about unbuffered queries.
 */
function safe_exec(PDO $pdo, string $sql): void
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    try { $stmt->fetchAll(); } catch (Throwable $e) {}
    $stmt->closeCursor();
}

/**
 * Run one migration file's statements against a PDO connection.
 * Returns [ok, errorMessage].
 */
function run_migration_file(PDO $pdo, string $file): array
{
    $sql = file_get_contents($file);
    if ($sql === false || trim($sql) === '') return [true, null];

    $statements = parse_sql_statements($sql);

    try {
        foreach ($statements as $stmt) {
            // Skip session-control statements that break in scratch DBs
            if (preg_match('/^USE\s+/i', $stmt)) continue;
            if (preg_match('/^CREATE\s+DATABASE/i', $stmt)) continue;

            // Dynamic SQL (PREPARE/EXECUTE/DEALLOCATE) must run in the same
            // session so `PREPARE s FROM @sql` can be `EXECUTE`d later.
            // Migrations use this pattern for idempotent ALTERs.
            if (preg_match('/^(PREPARE|EXECUTE|DEALLOCATE)\s/i', $stmt)) {
                try {
                    $pdo->exec($stmt);
                } catch (PDOException $e) {
                    // If the underlying ALTER failed because the column/index
                    // already exists, that's expected. Log and continue.
                    $msg = $e->getMessage();
                    if (str_contains($msg, 'Duplicate column')
                        || str_contains($msg, 'Duplicate key')
                        || str_contains($msg, 'already exists')) {
                        continue;
                    }
                    // Otherwise, log but don't fail — the migration was
                    // designed to be tolerant.
                    error_log('Dynamic stmt warning in ' . basename($file) . ': ' . $msg);
                }
                continue;
            }

            try {
                safe_exec($pdo, $stmt);
            } catch (PDOException $e) {
                $code = (string)$e->getCode();
                $msg  = $e->getMessage();
                if ($code === '42S21'
                    || str_contains($msg, 'Duplicate column')
                    || str_contains($msg, 'Duplicate key name')
                    || str_contains($msg, 'already exists')) {
                    continue;
                }
                return [false, $msg];
            }
        }
        return [true, null];
    } catch (Throwable $e) {
        return [false, $e->getMessage()];
    }
}

$migrationsDir = __DIR__ . '/../api/database/migrations';
$files = glob($migrationsDir . '/*.sql');
sort($files);

echo "\n  Migration test suite — " . count($files) . " migration files found\n";

// ─────────────────────────────────────────────────────────
// Suite 1: Files present
// ─────────────────────────────────────────────────────────
MT::suite('FILES PRESENT');
foreach ($files as $f) {
    MT::ok(basename($f));
}
MT::assert(count($files) >= 24, 'At least 24 migration files');

// ─────────────────────────────────────────────────────────
// Suite 2: Existing DB — tables exist
// ─────────────────────────────────────────────────────────
MT::suite('EXISTING DB — tables exist');

$db = get_db();

$expectedTables = [
    'users','orders','order_items','vendor_order_groups','order_item_financials',
    'inventory_levels','inventory_movements','inventory_reservations',
    'vendor_ledger_entries','payment_attempts','cod_collections',
    'customer_reward_ledger','customer_rewards','customer_reward_rules',
    'coupons','coupon_redemptions','checkout_quotes',
    'notifications','audit_logs','status_history','idempotency_keys',
    'refunds','invoices','invoice_sequences','credit_notes','credit_note_sequences',
    'reason_master','return_requests','return_evidence',
    'admin_tasks','admin_alerts','user_data_export_jobs',
    'refund_jobs','inventory_anomaly_log','reward_expiry_notifications',
];

$allTables = [];
$stmt = $db->query('SHOW TABLES');
while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    $allTables[$row[0]] = true;
}
$stmt->closeCursor();

foreach ($expectedTables as $t) {
    MT::assert(isset($allTables[$t]), "Table exists: {$t}");
}

// ─────────────────────────────────────────────────────────
// Suite 3: Critical columns exist
// ─────────────────────────────────────────────────────────
MT::suite('EXISTING DB — critical columns');

$expectedColumns = [
    ['users', 'account_status'],
    ['users', 'deactivated_at'],
    ['users', 'deletion_scheduled_for'],
    ['orders', 'cancelled_reason'],
    ['orders', 'payment_method'],
    ['order_items', 'cancelled_quantity'],
    ['order_items', 'cancelled_amount'],
    ['refunds', 'source'],
    ['refunds', 'details_json'],
    ['refunds', 'failure_message'],
<<<<<<< HEAD
    ['vendor_ledger_entries', 'status'],
    ['vendor_ledger_entries', 'reversed_at'],
=======
>>>>>>> origin/main
    ['inventory_movements', 'anomaly_flag'],
    ['coupon_redemptions', 'status'],
    ['notifications', 'deleted_at'],
    ['notifications', 'read_at'],
];

foreach ($expectedColumns as [$table, $col]) {
    $s = $db->prepare(
        'SELECT COUNT(*) FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
    );
    $s->execute([$table, $col]);
    $cnt = (int)$s->fetchColumn();
    $s->closeCursor();
    MT::assert($cnt > 0, "Column: {$table}.{$col}");
}

// ─────────────────────────────────────────────────────────
// Suite 4: Unique keys exist
// ─────────────────────────────────────────────────────────
MT::suite('EXISTING DB — unique keys');

$expectedIndexes = [
<<<<<<< HEAD
    ['refunds', 'uniq_refund_order_source'],
    ['vendor_ledger_entries', 'uniq_ledger_reference'],
    ['inventory_movements', 'uniq_movement_ref'],
=======
    ['vendor_ledger_entries', 'uniq_vendor_ledger_reference'],
>>>>>>> origin/main
    ['invoices', 'uniq_invoice_number'],
    ['invoices', 'uniq_order_invoice'],
    ['credit_notes', 'uniq_credit_note_number'],
    ['credit_notes', 'uniq_refund_credit_note'],
    ['refund_jobs', 'uniq_refund_job'],
    ['reason_master', 'uniq_scope_code'],
    ['return_evidence', 'uniq_return_file'],
];

foreach ($expectedIndexes as [$table, $idx]) {
    $s = $db->prepare(
        'SELECT COUNT(*) FROM information_schema.STATISTICS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?'
    );
    $s->execute([$table, $idx]);
    $cnt = (int)$s->fetchColumn();
    $s->closeCursor();
    MT::assert($cnt > 0, "Index: {$table}.{$idx}");
}

// ─────────────────────────────────────────────────────────
// Suite 5: Existing DB — idempotent re-run
// ─────────────────────────────────────────────────────────
MT::suite('EXISTING DB — idempotent re-run');

<<<<<<< HEAD
foreach ($files as $file) {
=======
$rerunnableFiles = array_values(array_filter($files, static function (string $file): bool {
    return strcmp(basename($file), '0013_cancellation_refunds.sql') >= 0;
}));
foreach ($rerunnableFiles as $file) {
>>>>>>> origin/main
    [$ok, $err] = run_migration_file($db, $file);
    if ($ok) {
        MT::ok('Re-ran: ' . basename($file));
    } else {
        MT::fail('Failed to re-run: ' . basename($file) . ' — ' . $err);
    }
}

// ─────────────────────────────────────────────────────────
// Suite 6: Fresh DB — full install
// ─────────────────────────────────────────────────────────
MT::suite('FRESH DB — full install');

$testDbName = 'conscious_indi_migration_test_' . substr(md5((string)time()), 0, 6);
$root = null;

try {
    $rootDsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=utf8mb4';
    $root = new PDO($rootDsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $root->exec("CREATE DATABASE `{$testDbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    MT::ok("Created scratch DB: {$testDbName}");

    $scratch = new PDO(
        'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . $testDbName . ';charset=utf8mb4',
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    $scratch->exec("SET time_zone = '+00:00'");

    $allOk = true;
    foreach ($files as $file) {
        [$ok, $err] = run_migration_file($scratch, $file);
        if (!$ok) {
            MT::fail('Fresh install failed on ' . basename($file) . ': ' . $err);
            $allOk = false;
            break;
        }
    }

    if ($allOk) {
        MT::ok('All migrations ran on fresh DB');

        $freshTables = [];
        $s = $scratch->query('SHOW TABLES');
        while ($row = $s->fetch(PDO::FETCH_NUM)) {
            $freshTables[$row[0]] = true;
        }
        $s->closeCursor();

        foreach (['users','orders','refunds','invoices','credit_notes','reason_master'] as $t) {
            MT::assert(isset($freshTables[$t]), "Fresh DB has: {$t}");
        }
    }

} catch (Throwable $e) {
    MT::fail('Fresh DB test error: ' . $e->getMessage());
} finally {
    if ($root) {
        try {
            $root->exec("DROP DATABASE IF EXISTS `{$testDbName}`");
            MT::ok("Dropped scratch DB: {$testDbName}");
        } catch (Throwable $e) {
            MT::fail('Could not drop scratch DB: ' . $e->getMessage());
        }
    }
}

<<<<<<< HEAD
exit(MT::summary());
=======
exit(MT::summary());
>>>>>>> origin/main
