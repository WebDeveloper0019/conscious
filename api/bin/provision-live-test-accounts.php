<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/../lib/helpers.php';

if (($argv[1] ?? '') !== '--confirm-live-test-accounts' || count($argv) !== 2) {
    fwrite(STDERR, "Usage: php provision-live-test-accounts.php --confirm-live-test-accounts\n");
    exit(1);
}
if (APP_ENV !== 'production') {
    fwrite(STDERR, "This command is for the production test environment only.\n");
    exit(1);
}

$customers = [
    ['Aanya', 'Sharma'], ['Kabir', 'Mehta'], ['Meera', 'Iyer'], ['Rohan', 'Kapoor'], ['Isha', 'Nair'],
    ['Dev', 'Patel'], ['Tara', 'Sen'], ['Arjun', 'Rao'], ['Naina', 'Das'], ['Samar', 'Jain'],
];
$vendors = [
    ['artisan', 'Meera Kumari', 'CI Test Meera Pottery'],
    ['artisan', 'Kabir Ansari', 'CI Test Kabir Weaves'],
    ['artisan', 'Tara Mondal', 'CI Test Tara Handcraft'],
    ['artisan', 'Naina Devi', 'CI Test Naina Naturals'],
    ['artisan', 'Samar Ali', 'CI Test Samar Woodworks'],
    ['brand', 'Aarav Shah', 'CI Test Earthloom Textiles'],
    ['brand', 'Diya Rao', 'CI Test Bamboo Everyday'],
    ['brand', 'Vivaan Sethi', 'CI Test Refill Collective'],
    ['brand', 'Anika Gupta', 'CI Test Kind Home Co'],
    ['brand', 'Ishaan Malhotra', 'CI Test Terra Goods'],
];

$folder = rtrim(PRIVATE_STORAGE_PATH, '/\\') . '/test-accounts';
$mailboxFile = $folder . '/mailboxes.json';
if (!is_file($mailboxFile)) { fwrite(STDERR, "Create the 20 disposable inboxes first.\n"); exit(1); }
try {
    $manifest = json_decode((string)file_get_contents($mailboxFile), true, 512, JSON_THROW_ON_ERROR);
} catch (Throwable $error) {
    fwrite(STDERR, "The private mailbox manifest is invalid.\n"); exit(1);
}
$mailboxes = $manifest['accounts'] ?? null;
if (($manifest['provider'] ?? '') !== 'mail.tm' || !is_array($mailboxes) || !array_is_list($mailboxes) || count($mailboxes) !== 20) {
    fwrite(STDERR, "Exactly 20 Mail.tm inboxes are required.\n"); exit(1);
}
$testEmails = [];
foreach ($mailboxes as $index => $mailbox) {
    $expectedRole = $index < 10 ? 'customer' : 'vendor';
    $email = $mailbox['email'] ?? null;
    $mailboxPassword = $mailbox['mailbox_password'] ?? null;
    if (!is_array($mailbox) || ($mailbox['role'] ?? '') !== $expectedRole
        || !is_string($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)
        || !is_string($mailboxPassword) || strlen($mailboxPassword) < 16
        || !is_string($mailbox['mailbox_id'] ?? null) || $mailbox['mailbox_id'] === '') {
        fwrite(STDERR, "The private mailbox manifest has an invalid account.\n"); exit(1);
    }
    $testEmails[] = strtolower($email);
}
if (count(array_unique($testEmails)) !== 20) { fwrite(STDERR, "Mailbox addresses must be unique.\n"); exit(1); }

$db = get_db();
$checkCustomer = $db->prepare('SELECT id FROM users WHERE email=:email OR mobile=:mobile LIMIT 1');
$checkVendor = $db->prepare('SELECT id FROM vendors WHERE email=:email OR mobile=:mobile LIMIT 1');
foreach ($customers as $index => $_) {
    $checkCustomer->execute(['email' => $testEmails[$index], 'mobile' => sprintf('000000%04d', $index + 1)]);
    if ($checkCustomer->fetch()) { fwrite(STDERR, "Existing customer test account or number; no changes made.\n"); exit(1); }
}
foreach ($vendors as $index => $_) {
    $checkVendor->execute(['email' => $testEmails[$index + 10], 'mobile' => sprintf('000001%04d', $index + 1)]);
    if ($checkVendor->fetch()) { fwrite(STDERR, "Existing vendor test account or number; no changes made.\n"); exit(1); }
}

if (!is_dir($folder) && !mkdir($folder, 0700, true) && !is_dir($folder)) {
    fwrite(STDERR, "Private credential directory is unavailable.\n");
    exit(1);
}
$credentialPath = $folder . '/ci-test-credentials-' . gmdate('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.csv';
$handle = fopen($credentialPath, 'x');
if (!$handle) { fwrite(STDERR, "Could not create private credential file.\n"); exit(1); }
if (!chmod($credentialPath, 0600)) {
    fclose($handle);
    unlink($credentialPath);
    fwrite(STDERR, "Could not restrict credential file permissions.\n");
    exit(1);
}
function write_credentials($handle, array $row): void
{
    if (fputcsv($handle, $row) === false) throw new RuntimeException('Could not write private credentials.');
}
write_credentials($handle, ['role', 'type', 'name', 'email', 'site_password', 'mailbox_password', 'account_id', 'application_reference', 'status']);

$insertCustomer = $db->prepare('INSERT INTO users (id,first_name,last_name,email,mobile,password_hash,email_verified,status,marketing_opt_in,created_at) VALUES (:id,:first,:last,:email,:mobile,:hash,1,"active",0,UTC_TIMESTAMP())');
$insertVendor = $db->prepare('INSERT INTO vendors (id,vendor_type,owner_name,display_name,email,mobile,password_hash,email_verified,status,verification_status,onboarding_status,created_at) VALUES (:id,:type,:owner,:display,:email,:mobile,:hash,1,"pending","not_submitted","in_progress",UTC_TIMESTAMP())');
$insertApplication = $db->prepare('INSERT INTO vendor_applications (id,vendor_id,reference,status,completion_percent,created_at) VALUES (:id,:vendor,:reference,"draft",0,UTC_TIMESTAMP())');
$insertProfile = $db->prepare('INSERT INTO vendor_profiles (vendor_id,legal_name,public_name,story,created_at) VALUES (:vendor,:legal,:public,:story,UTC_TIMESTAMP())');
$insertRole = $db->prepare('INSERT INTO principal_roles (principal_type,principal_id,role_id,granted_at) SELECT "vendor",:vendor,id,UTC_TIMESTAMP() FROM roles WHERE code="vendor"');

$db->beginTransaction();
try {
    foreach ($customers as $index => [$first, $last]) {
        $id = uuid_string();
        $password = 'Ci!' . bin2hex(random_bytes(16));
        $insertCustomer->execute([
            'id' => $id, 'first' => 'Test ' . $first, 'last' => $last,
            'email' => $testEmails[$index], 'mobile' => sprintf('000000%04d', $index + 1),
            'hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);
        write_credentials($handle, ['customer', '', 'Test ' . $first . ' ' . $last, $testEmails[$index], $password, $mailboxes[$index]['mailbox_password'], $id, '', 'active test account']);
    }
    foreach ($vendors as $index => [$type, $owner, $store]) {
        $id = uuid_string();
        $password = 'Ci!' . bin2hex(random_bytes(16));
        $reference = 'QA-' . strtoupper($type === 'brand' ? 'BR' : 'AR') . '-' . strtoupper(bin2hex(random_bytes(4)));
        $insertVendor->execute([
            'id' => $id, 'type' => $type, 'owner' => 'Test ' . $owner, 'display' => $store,
            'email' => $testEmails[$index + 10], 'mobile' => sprintf('000001%04d', $index + 1),
            'hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);
        $insertApplication->execute(['id' => uuid_string(), 'vendor' => $id, 'reference' => $reference]);
        $insertProfile->execute([
            'vendor' => $id, 'legal' => $store, 'public' => $store,
            'story' => 'Internal test vendor. Identity, business details and products are pending owner submission and administrator review.',
        ]);
        $insertRole->execute(['vendor' => $id]);
        if ($insertRole->rowCount() !== 1) throw new RuntimeException('Vendor role is missing.');
        write_credentials($handle, ['vendor', $type, 'Test ' . $owner . ' — ' . $store, $testEmails[$index + 10], $password, $mailboxes[$index + 10]['mailbox_password'], $id, $reference, 'onboarding pending']);
    }
    fflush($handle);
    fclose($handle);
    $handle = null;
    audit_event('test_accounts.provisioned', 'test_account_batch', null, ['customers' => 10, 'artisans' => 5, 'brands' => 5], ['type' => 'system']);
    $db->commit();
    fwrite(STDOUT, "CREATED: 10 customers, 5 artisans, 5 brands\n");
    fwrite(STDOUT, "PRIVATE_CREDENTIALS: {$credentialPath}\n");
    fwrite(STDOUT, "These are internal test accounts with disposable inboxes. Vendors and products are not approved.\n");
} catch (Throwable $error) {
    if ($db->inTransaction()) $db->rollBack();
    if (is_resource($handle)) fclose($handle);
    @unlink($credentialPath);
    fwrite(STDERR, "No test accounts were provisioned: " . $error->getMessage() . "\n");
    exit(1);
}
