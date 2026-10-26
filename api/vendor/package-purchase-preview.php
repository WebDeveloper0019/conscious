<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/influencer-service-orders.php';

send_cors_headers();
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}
$context = require_vendor(true);
$package = service_order_quote(trim((string)($_GET['package_id'] ?? '')));

json_response(200, [
    'success' => true,
    'quote' => [
        'package_id' => $package['id'],
        'package_title' => $package['title'],
        'creator' => ['name' => $package['influencer_name'], 'handle' => $package['influencer_handle']],
        'deliverables' => json_decode((string)$package['deliverables_json'], true) ?: [],
        'platforms' => json_decode((string)$package['platforms_json'], true) ?: [],
        'turnaround_days' => (int)$package['turnaround_days'],
        'revisions' => (int)$package['revisions'],
        'usage_rights' => $package['usage_rights'],
        'vendor_requirements' => $package['vendor_requirements'],
        'cancellation_terms' => $package['cancellation_terms'],
        'tax_treatment' => $package['tax_treatment'],
        'currency' => 'INR',
        'base_amount' => $package['base_amount'],
        'gst_rate' => $package['gst_rate'],
        'gst_amount' => $package['gst_amount'],
        'total_amount' => $package['total_amount'],
        'payment_method' => 'razorpay',
    ],
    'vendor' => [
        'display_name' => $context['vendor']['display_name'],
        'contact_name' => $context['vendor']['owner_name'],
        'contact_email' => $context['vendor']['email'],
        'contact_mobile' => $context['vendor']['mobile'],
    ],
]);
