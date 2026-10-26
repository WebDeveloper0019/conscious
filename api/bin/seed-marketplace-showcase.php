<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__.'/../lib/helpers.php';

if (APP_ENV !== 'local') {
    fwrite(STDERR, "Marketplace showcase seeding requires APP_ENV=local.\n");
    exit(1);
}

$rotate = in_array('--rotate', $argv, true);
$vendorPassword = 'DemoVendor#2026!';
$customerPassword = 'DemoBuyer#2026!';
$vendors = [
    ['Demo Earthloom Textiles', 'brand', 'Aarav Demo', 'Mumbai'],
    ['Demo Bamboo Everyday', 'brand', 'Diya Demo', 'Bengaluru'],
    ['Demo Refill Collective', 'brand', 'Vivaan Demo', 'Pune'],
    ['Demo Kind Home Co', 'brand', 'Anika Demo', 'Delhi'],
    ['Demo Terra Goods', 'brand', 'Ishaan Demo', 'Jaipur'],
    ['Demo Meera Pottery', 'artisan', 'Meera Demo', 'Jaipur'],
    ['Demo Kabir Weaves', 'artisan', 'Kabir Demo', 'Varanasi'],
    ['Demo Tara Handcraft', 'artisan', 'Tara Demo', 'Kolkata'],
    ['Demo Naina Naturals', 'artisan', 'Naina Demo', 'Chennai'],
    ['Demo Samar Woodworks', 'artisan', 'Samar Demo', 'Lucknow'],
];
$customers = [
    ['Aanya', 'Demo'], ['Kabir', 'Demo'], ['Meera', 'Demo'], ['Rohan', 'Demo'], ['Isha', 'Demo'],
    ['Dev', 'Demo'], ['Tara', 'Demo'], ['Arjun', 'Demo'], ['Naina', 'Demo'], ['Samar', 'Demo'],
];

$db = get_db();
$findVendor = $db->prepare('SELECT id,verification_status FROM vendors WHERE email=:email LIMIT 1');
$findCustomer = $db->prepare('SELECT id FROM users WHERE email=:email LIMIT 1');
$insertVendor = $db->prepare('INSERT INTO vendors (id,vendor_type,owner_name,display_name,email,mobile,password_hash,email_verified,status,verification_status,onboarding_status,created_at) VALUES (:id,:type,:owner,:display,:email,:mobile,:password,1,"active","demo_fixture","completed",UTC_TIMESTAMP())');
$insertApplication = $db->prepare('INSERT INTO vendor_applications (id,vendor_id,reference,status,completion_percent,created_at) VALUES (:id,:vendor,:reference,"demo_fixture",0,UTC_TIMESTAMP())');
$insertProfile = $db->prepare('INSERT INTO vendor_profiles (vendor_id,legal_name,public_name,story,created_at) VALUES (:id,:legal_name,:public_name,:story,UTC_TIMESTAMP())');
$insertVendorRole = $db->prepare('INSERT INTO principal_roles (principal_type,principal_id,role_id,granted_at) SELECT "vendor",:id,id,UTC_TIMESTAMP() FROM roles WHERE code="vendor"');
$insertCustomer = $db->prepare('INSERT INTO users (id,first_name,last_name,email,mobile,password_hash,email_verified,status,marketing_opt_in,created_at) VALUES (:id,:first,:last,:email,:mobile,:password,1,"active",0,UTC_TIMESTAMP())');
$rotateVendor = $db->prepare('UPDATE vendors SET password_hash=:password,updated_at=UTC_TIMESTAMP() WHERE id=:id AND verification_status="demo_fixture"');
$rotateCustomer = $db->prepare('UPDATE users SET password_hash=:password,updated_at=UTC_TIMESTAMP() WHERE id=:id AND email LIKE "customer.demo%@conscious.test"');

$db->beginTransaction();
try {
    foreach ($vendors as $index => [$name, $type, $owner, $city]) {
        $number = $index + 1;
        $email = sprintf('vendor.demo%02d@conscious.test', $number);
        $findVendor->execute(['email' => $email]);
        $existing = $findVendor->fetch();
        if ($existing) {
            if ($existing['verification_status'] !== 'demo_fixture') throw new RuntimeException("{$email} is not a demo fixture.");
            if (!$rotate) throw new RuntimeException('Vendor showcase accounts exist. Use --rotate to change their passwords.');
            $rotateVendor->execute(['id' => $existing['id'], 'password' => password_hash($vendorPassword, PASSWORD_DEFAULT)]);
            continue;
        }
        $id = uuid_string();
        $insertVendor->execute([
            'id' => $id, 'type' => $type, 'owner' => $owner, 'display' => $name,
            'email' => $email, 'mobile' => sprintf('911110%04d', 100 + $number),
            'password' => password_hash($vendorPassword, PASSWORD_DEFAULT),
        ]);
        $insertApplication->execute(['id' => uuid_string(), 'vendor' => $id, 'reference' => sprintf('DEMO-VND-%02d', $number)]);
        $insertProfile->execute(['id' => $id, 'legal_name' => $name, 'public_name' => $name, 'story' => "Fictional {$type} vendor in {$city}, created only for a local Conscious India walkthrough."]);
        $insertVendorRole->execute(['id' => $id]);
    }
    foreach ($customers as $index => [$first, $last]) {
        $number = $index + 1;
        $email = sprintf('customer.demo%02d@conscious.test', $number);
        $findCustomer->execute(['email' => $email]);
        $existing = $findCustomer->fetch();
        if ($existing) {
            if (!$rotate) throw new RuntimeException('Customer showcase accounts exist. Use --rotate to change their passwords.');
            $rotateCustomer->execute(['id' => $existing['id'], 'password' => password_hash($customerPassword, PASSWORD_DEFAULT)]);
            continue;
        }
        $insertCustomer->execute([
            'id' => uuid_string(), 'first' => $first, 'last' => $last,
            'email' => $email, 'mobile' => sprintf('922220%04d', 100 + $number),
            'password' => password_hash($customerPassword, PASSWORD_DEFAULT),
        ]);
    }
    $db->commit();
} catch (Throwable $error) {
    if ($db->inTransaction()) $db->rollBack();
    fwrite(STDERR, $error->getMessage()."\n");
    exit(1);
}

fwrite(STDOUT, "Local-only vendor password (all 10): {$vendorPassword}\n");
foreach ($vendors as $index => [$name, $type]) fwrite(STDOUT, sprintf("V%02d vendor.demo%02d@conscious.test %s %s\n", $index + 1, $index + 1, $type, $name));
fwrite(STDOUT, "Local-only customer password (all 10): {$customerPassword}\n");
foreach ($customers as $index => [$first, $last]) fwrite(STDOUT, sprintf("C%02d customer.demo%02d@conscious.test %s %s\n", $index + 1, $index + 1, $first, $last));
fwrite(STDOUT, "These are fictional local demo accounts. No vendor documents, product approvals, orders or payments were fabricated.\n");
