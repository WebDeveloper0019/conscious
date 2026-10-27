<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') exit(1);
require_once __DIR__ . '/../api/lib/helpers.php';
require_once __DIR__ . '/../api/lib/influencer-service-orders.php';
if (APP_ENV !== 'local' || !preg_match('/^conscious_backend_merge_[0-9_]+$/', DB_NAME)) {
    fwrite(STDERR, "Refusing to run outside a dedicated local merge-test database.\n");
    exit(1);
}

function service_check(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
    fwrite(STDOUT, "PASS {$message}\n");
}

$db = get_db();
$vendorId = uuid_string();
$influencerId = uuid_string();
$packageId = uuid_string();
$orderId = uuid_string();
$paymentId = uuid_string();
$suffix = bin2hex(random_bytes(4));

$db->beginTransaction();
try {
    $db->prepare(
        'INSERT INTO vendors
         (id,vendor_type,owner_name,display_name,email,mobile,password_hash,email_verified,status,verification_status,onboarding_status,created_at)
         VALUES (:id,"brand","Test Owner","Test Brand",:email,"9876543210",:password,1,"active","approved","completed",UTC_TIMESTAMP())'
    )->execute(['id' => $vendorId, 'email' => "service-vendor-{$suffix}@conscious.test", 'password' => password_hash('Test#12345', PASSWORD_DEFAULT)]);
    $db->prepare(
        'INSERT INTO influencers
         (id,full_name,email,mobile,password_hash,email_verified,handle,bio,platforms_json,audience_json,status,verification_status,created_at)
         VALUES (:id,"Service Creator",:email,"9876543211",:password,1,:handle,"Verified creator test profile.","[]","{}","active","approved",UTC_TIMESTAMP())'
    )->execute([
        'id' => $influencerId, 'email' => "service-creator-{$suffix}@conscious.test",
        'password' => password_hash('Test#12345', PASSWORD_DEFAULT), 'handle' => "service-creator-{$suffix}",
    ]);
    $db->prepare(
        'INSERT INTO influencer_packages
         (id,influencer_id,title,description,price_inr,deliverables_json,platforms_json,turnaround_days,revisions,
          usage_rights,vendor_requirements,capacity,tax_treatment,cancellation_terms,status,created_at,updated_at)
         VALUES (:id,:influencer,"Launch Reel","One launch reel with a reviewed campaign brief.",1000,"[\"One reel\"]","[\"Instagram\"]",7,1,
                 "Organic social use for 30 days.","Provide product and approved claims.",2,"GST charged at checkout.","Cancellation is reviewed before refund.","active",UTC_TIMESTAMP(),UTC_TIMESTAMP())'
    )->execute(['id' => $packageId, 'influencer' => $influencerId]);

    $quote = service_order_quote($packageId, true);
    service_check((float)$quote['base_amount'] === 1000.0, 'server quote keeps the approved base price');
    service_check((float)$quote['gst_amount'] === 180.0 && (float)$quote['total_amount'] === 1180.0, 'server quote calculates 18% GST');

    $db->prepare(
        'INSERT INTO influencer_service_orders
         (id,order_number,vendor_id,influencer_id,package_id,brief,contact_name,contact_email,currency,base_amount,gst_rate,gst_amount,total_amount,
          influencer_earning_amount,status,payment_status,reservation_expires_at,created_at,updated_at)
         VALUES (:id,:number,:vendor,:influencer,:package,"A complete campaign brief for the creator test.","Test Owner",:email,"INR",1000,18,180,1180,
                 1000,"pending_payment","pending",DATE_ADD(UTC_TIMESTAMP(),INTERVAL 20 MINUTE),UTC_TIMESTAMP(),UTC_TIMESTAMP())'
    )->execute(['id' => $orderId, 'number' => 'CIS-TEST-' . strtoupper($suffix), 'vendor' => $vendorId, 'influencer' => $influencerId, 'package' => $packageId, 'email' => "service-vendor-{$suffix}@conscious.test"]);
    $db->prepare(
        'INSERT INTO influencer_service_payments
         (id,order_id,provider,provider_order_id,amount,currency,status,created_at,updated_at)
         VALUES (:id,:order,"razorpay",:provider_order,1180,"INR","created",UTC_TIMESTAMP(),UTC_TIMESTAMP())'
    )->execute(['id' => $paymentId, 'order' => $orderId, 'provider_order' => 'order_test_' . $suffix]);

    $payment = $db->query(
        'SELECT p.*,o.vendor_id,o.influencer_id,o.package_id,o.order_number,o.status AS order_status,
                o.payment_status AS order_payment_status,o.reservation_expires_at
         FROM influencer_service_payments p JOIN influencer_service_orders o ON o.id=p.order_id
         WHERE p.id=' . $db->quote($paymentId)
    )->fetch();
    service_order_capture($db, $payment, 'pay_test_' . $suffix);
    $status = $db->query('SELECT status,payment_status FROM influencer_service_orders WHERE id=' . $db->quote($orderId))->fetch();
    service_check($status['status'] === 'paid' && $status['payment_status'] === 'paid', 'verified payment activates the collaboration');
    service_check((int)$db->query('SELECT COUNT(*) FROM admin_tasks WHERE subject_id=' . $db->quote($orderId))->fetchColumn() === 1, 'paid order creates one admin coordination task');

    $paidPayment = $db->query(
        'SELECT p.*,o.vendor_id,o.influencer_id,o.package_id,o.order_number,o.status AS order_status,
                o.payment_status AS order_payment_status,o.reservation_expires_at
         FROM influencer_service_payments p JOIN influencer_service_orders o ON o.id=p.order_id
         WHERE p.id=' . $db->quote($paymentId)
    )->fetch();
    $again = service_order_capture($db, $paidPayment, 'pay_test_' . $suffix);
    service_check(!empty($again['already_processed']), 'payment capture is idempotent');

    $order = $db->query('SELECT * FROM influencer_service_orders WHERE id=' . $db->quote($orderId))->fetch();
    service_order_complete_earning($db, $order, '00000000-0000-0000-0000-000000000000');
    service_order_complete_earning($db, $order, '00000000-0000-0000-0000-000000000000');
    service_check((int)$db->query('SELECT COUNT(*) FROM influencer_service_earnings WHERE order_id=' . $db->quote($orderId))->fetchColumn() === 1, 'creator earning is posted once');

    service_order_enqueue_refund($db, $orderId, 'Approved cancellation test.', 'test-refund-' . $orderId);
    service_order_enqueue_refund($db, $orderId, 'Approved cancellation test.', 'test-refund-' . $orderId);
    service_check((int)$db->query('SELECT COUNT(*) FROM influencer_service_refunds WHERE order_id=' . $db->quote($orderId))->fetchColumn() === 1, 'refund queue is idempotent');
    service_order_reverse_earning($db, $orderId, $influencerId);
    service_order_reverse_earning($db, $orderId, $influencerId);
    service_check((int)$db->query('SELECT COUNT(*) FROM influencer_ledger_entries WHERE reference_type="influencer_service_refund" AND reference_id=' . $db->quote($orderId))->fetchColumn() === 1, 'refund clawback is posted once');

    $db->rollBack();
    fwrite(STDOUT, "INFLUENCER_SERVICE_ORDERS_OK\n");
} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    fwrite(STDERR, "FAIL " . $e->getMessage() . "\n");
    exit(1);
}
