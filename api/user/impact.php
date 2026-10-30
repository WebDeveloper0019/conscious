<?php
// api/user/impact.php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';

send_cors_headers();

$principal = require_principal(['customer']); 
$userId = $principal['id'];
$db = get_db();

// 1. Basic Impact Stats
$stmt = $db->prepare('SELECT COUNT(id) FROM orders WHERE user_id = ?');
$stmt->execute([$userId]);
$orderCount = (int)$stmt->fetchColumn();

$artisans = $orderCount > 0 ? $orderCount * 2 : 0; 
$waterSaved = $orderCount > 0 ? $orderCount * 150 : 0;
$wasteDiverted = $orderCount > 0 ? $orderCount * 1.2 : 0;
$regions = $orderCount > 0 ? min($orderCount, 5) : 0; 

// 2. Calculate Impact by Craft
// Note: Change 'p.category' to whatever your column is called (e.g., 'p.category_name')
// 2. Calculate Impact by Craft
$sql = "
    SELECT c.name AS category_name, SUM(oi.quantity) as total_bought
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    JOIN categories c ON p.category_id = c.id
    WHERE o.user_id = ?
    GROUP BY c.name
";
$stmt = $db->prepare($sql);
$stmt->execute([$userId]);
$categories = $stmt->fetchAll();

$totalItems = 0;
foreach ($categories as $cat) {
    $totalItems += (int)$cat['total_bought'];
}

$craftData = [];
foreach ($categories as $cat) {
    $percentage = $totalItems > 0 ? round(($cat['total_bought'] / $totalItems) * 100) : 0;
    $craftData[] = [
        'name' => $cat['category_name'] ?: 'Other Crafts',
        'percentage' => $percentage
    ];
}

// If no orders exist, provide default empty categories
if (empty($craftData)) {
    $craftData = [
        ['name' => 'Handloom & textiles', 'percentage' => 0],
        ['name' => 'Pottery & ceramics', 'percentage' => 0],
        ['name' => 'Jewellery & metal craft', 'percentage' => 0],
        ['name' => 'Folk art & decor', 'percentage' => 0]
    ];
}

// 3. Return everything to the frontend
json_response(200, [
    'success' => true,
    'impact' => [
        'artisans' => $artisans,
        'waterSaved' => $waterSaved,
        'wasteDiverted' => number_format($wasteDiverted, 1), 
        'regions' => $regions,
        'crafts' => $craftData 
    ]
]);