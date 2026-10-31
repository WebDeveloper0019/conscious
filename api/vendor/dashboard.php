<?php
// api/vendor/dashboard.php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';

send_cors_headers();

// Authenticate the vendor
$principal = require_principal(['vendor']); 
$vendorId = $principal['id'];
$db = get_db();

$stats = [
    'activeProducts' => 0,
    'newOrders' => 0,
    'deliveredOrders' => 0,
    'payout' => "0.00",
    'operational' => [
        'preparing' => 0,
        'ready' => 0,
        'shipping' => 0,
        'returns' => 0,
        'lowStock' => 0,
        'cod' => 0
    ]
];

try {
    // 1. Count Active Products
    $stmt = $db->prepare('SELECT COUNT(id) FROM products WHERE vendor_id = ? AND status = "active"');
    $stmt->execute([$vendorId]);
    $stats['activeProducts'] = (int)$stmt->fetchColumn();

    // 2. Count New/Pending Orders (Using vendor_order_groups)
    $stmt = $db->prepare('SELECT COUNT(id) FROM vendor_order_groups WHERE vendor_id = ? AND status IN ("pending", "placed", "new")');
    $stmt->execute([$vendorId]);
    $stats['newOrders'] = (int)$stmt->fetchColumn();

    // 3. Count Delivered Orders
    $stmt = $db->prepare('SELECT COUNT(id) FROM vendor_order_groups WHERE vendor_id = ? AND status = "delivered"');
    $stmt->execute([$vendorId]);
    $stats['deliveredOrders'] = (int)$stmt->fetchColumn();

    // 4. Calculate Available Payout (Using vendor_ledger_entries)
    $stmt = $db->prepare('SELECT SUM(amount) FROM vendor_ledger_entries WHERE vendor_id = ? AND status = "available"');
    $stmt->execute([$vendorId]);
    $payout = $stmt->fetchColumn();
    $stats['payout'] = number_format((float)($payout ?: 0), 2);

    // 5. Count Statuses for Operational Overview
    $stmt = $db->prepare('SELECT status, COUNT(id) as count FROM vendor_order_groups WHERE vendor_id = ? GROUP BY status');
    $stmt->execute([$vendorId]);
    $statusCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    $stats['operational']['preparing'] = $statusCounts['processing'] ?? 0;
    $stats['operational']['ready'] = $statusCounts['ready_to_ship'] ?? 0;
    $stats['operational']['shipping'] = $statusCounts['shipped'] ?? 0;

} catch (Throwable $e) {
    // If there's an SQL error, return it in the message so you can debug it
    json_response(500, ['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

json_response(200, [
    'success' => true,
    'data' => $stats
]);