<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
send_cors_headers();

// In a real app, this would be require_principal(['admin']). 
// Since you haven't built the admin login yet, we will allow local testing.
// if (APP_ENV !== 'local') { $principal = require_principal(['admin']); }

$db = get_db();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    // Fetch packages pending approval, including the creator's name
    $stmt = $db->prepare('
        SELECT p.*, i.full_name, i.handle, i.email 
        FROM influencer_packages p 
        JOIN influencers i ON i.id = p.influencer_id 
        WHERE p.status = "pending_approval"
        ORDER BY p.created_at ASC
    ');
    $stmt->execute();
    $packages = $stmt->fetchAll();
    
    foreach ($packages as &$pkg) {
        $pkg['deliverables_json'] = json_decode($pkg['deliverables_json'] ?? '[]', true);
        $pkg['platforms_json'] = json_decode($pkg['platforms_json'] ?? '[]', true);
    }
    
    json_response(200, ['success' => true, 'packages' => $packages]);
}

if ($method === 'POST') {
    $body = get_json_body();
    $pkgId = trim($body['id'] ?? '');
    $action = trim($body['action'] ?? ''); // 'approve' or 'reject'

    if (!$pkgId || !in_array($action, ['approve', 'reject'])) {
        json_response(422, ['success' => false, 'message' => 'Invalid request.']);
    }

    // If approved, status becomes 'active'. If rejected, it goes back to 'draft'
    $newStatus = $action === 'approve' ? 'active' : 'draft';
    
    $stmt = $db->prepare('UPDATE influencer_packages SET status = :status, updated_at = UTC_TIMESTAMP() WHERE id = :id');
    $stmt->execute(['status' => $newStatus, 'id' => $pkgId]);

    json_response(200, ['success' => true, 'message' => "Package successfully " . ($action === 'approve' ? 'approved' : 'rejected')]);
}

json_response(405, ['success' => false, 'message' => 'Method not allowed.']);