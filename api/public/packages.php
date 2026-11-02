<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
send_cors_headers();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

try {
    $db = get_db();
    
    $sql = "SELECT p.id, p.title, p.description, p.price_inr, p.deliverables_json, p.platforms_json, 
                   p.turnaround_days, p.revisions, p.usage_rights, p.vendor_requirements, 
                   p.cancellation_terms, i.full_name, i.handle,
                   (SELECT CONCAT('../files.php?id=', id) FROM file_assets WHERE id = i.profile_image_id AND visibility='public') AS avatar_url
            FROM influencer_packages p 
            JOIN influencers i ON i.id = p.influencer_id 
            WHERE p.status = 'active' 
              AND i.status = 'active' 
              AND i.verification_status = 'approved' 
              AND i.email_verified = 1
            ORDER BY p.price_inr ASC";
            
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $packages = $stmt->fetchAll();
    
    foreach ($packages as &$pkg) {
        $pkg['deliverables_json'] = json_decode($pkg['deliverables_json'] ?? '[]', true) ?: [];
        $pkg['platforms_json'] = json_decode($pkg['platforms_json'] ?? '[]', true) ?: [];
        $pkg['price_inr'] = (float)$pkg['price_inr'];
    }
    unset($pkg);
    
    json_response(200, ['success' => true, 'packages' => $packages]);

} catch (Throwable $e) {
    json_response(500, ['success' => false, 'message' => 'DEBUG ERROR: ' . $e->getMessage()]);
}