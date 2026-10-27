<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
send_cors_headers();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

$db = get_db();
$q = trim($_GET['q'] ?? '');
$platform = trim($_GET['platform'] ?? '');
$maxPrice = (float)($_GET['max_price'] ?? 0);

$sql = "SELECT p.*, i.full_name, i.handle, i.profile_image_id 
        FROM influencer_packages p 
        JOIN influencers i ON i.id = p.influencer_id 
        WHERE p.status = 'active' AND i.status = 'active'";

$params = [];

if ($q !== '') {
    $sql .= " AND (p.title LIKE :q1 OR i.full_name LIKE :q2)";
    $params['q1'] = "%$q%";
    $params['q2'] = "%$q%";
}

if ($platform !== '') {
    $sql .= " AND p.platforms_json LIKE :plat";
    $params['plat'] = "%$platform%";
}

if ($maxPrice > 0) {
    $sql .= " AND p.price_inr <= :max";
    $params['max'] = $maxPrice;
}

$sql .= " ORDER BY p.price_inr ASC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$packages = $stmt->fetchAll();

foreach ($packages as &$pkg) {
    $pkg['deliverables_json'] = json_decode($pkg['deliverables_json'] ?? '[]', true);
    $pkg['platforms_json'] = json_decode($pkg['platforms_json'] ?? '[]', true);
    
    // Build avatar URL
    $pkg['avatar_url'] = null;
    if (!empty($pkg['profile_image_id'])) {
        $pkg['avatar_url'] = '../files.php?id=' . $pkg['profile_image_id'];
    }
}

json_response(200, ['success' => true, 'packages' => $packages]);