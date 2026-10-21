<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
send_cors_headers();

if(($_SERVER['REQUEST_METHOD']??'GET')!=='GET') json_response(405,['success'=>false,'message'=>'Method not allowed.']);

try {
    $db = get_db();
    
    // In local dev, we might want to show pending ones too for testing. 
    // But let's stick to 'active' for the actual flow.
    $statusFilter = "p.status = 'active' AND i.status = 'active'";
    
    $sql = "SELECT p.*, i.full_name, i.handle, i.profile_image_id,
            (SELECT CONCAT('../files.php?id=',id) FROM file_assets WHERE id = i.profile_image_id AND visibility='public') AS avatar_url
            FROM influencer_packages p 
            JOIN influencers i ON i.id = p.influencer_id 
            WHERE $statusFilter 
            ORDER BY p.price_inr ASC";
            
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $packages = $stmt->fetchAll();
    
    foreach ($packages as &$pkg) {
        $pkg['deliverables_json'] = json_decode($pkg['deliverables_json'] ?? '[]', true);
        $pkg['platforms_json'] = json_decode($pkg['platforms_json'] ?? '[]', true);
        $pkg['price_inr'] = (float)$pkg['price_inr'];
    }
    
    json_response(200, ['success' => true, 'packages' => $packages]);

} catch (Throwable $e) {
    json_response(500, ['success' => false, 'message' => 'DEBUG ERROR: ' . $e->getMessage()]);
}