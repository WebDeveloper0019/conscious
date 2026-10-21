<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
send_cors_headers();

$principal = require_principal(['influencer']);
$id = $principal['id'];
$db = get_db();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $stmt = $db->prepare('SELECT * FROM influencer_packages WHERE influencer_id = :id ORDER BY created_at DESC');
    $stmt->execute(['id' => $id]);
    $packages = $stmt->fetchAll();
    
    foreach ($packages as &$pkg) {
        $pkg['deliverables_json'] = json_decode($pkg['deliverables_json'], true) ?: [];
        $pkg['platforms_json'] = json_decode($pkg['platforms_json'], true) ?: [];
    }
    
    json_response(200, ['success' => true, 'packages' => $packages]);
}

if ($method === 'POST') {
    $body = get_json_body();
    
    $title = trim($body['title'] ?? '');
    $description = trim($body['description'] ?? '');
    $price = (float)($body['price_inr'] ?? 0);
    $turnaround = (int)($body['turnaround_days'] ?? 0);
    $revisions = (int)($body['revisions'] ?? 1);
    $capacity = (int)($body['capacity'] ?? 1);
    
    if (mb_strlen($title) < 3 || $price < 100 || $turnaround < 1 || $capacity < 1) {
        json_response(422, ['success' => false, 'message' => 'Please fill in all required fields correctly (Min price ₹100).']);
    }

    $pkgId = uuid_string();
    $db->prepare('INSERT INTO influencer_packages 
        (id, influencer_id, title, description, price_inr, deliverables_json, platforms_json, turnaround_days, revisions, usage_rights, vendor_requirements, capacity, tax_treatment, cancellation_terms, status, created_at, updated_at) 
        VALUES (:id, :inf, :title, :desc, :price, :deliv, :plat, :turn, :rev, :usage, :vendor, :cap, :tax, :cancel, "draft", UTC_TIMESTAMP(), UTC_TIMESTAMP())')
       ->execute([
           'id' => $pkgId,
           'inf' => $id,
           'title' => $title,
           'desc' => $description,
           'price' => $price,
           'deliv' => json_encode($body['deliverables_json'] ?? []),
           'plat' => json_encode($body['platforms_json'] ?? []),
           'turn' => $turnaround,
           'rev' => $revisions,
           'usage' => trim($body['usage_rights'] ?? ''),
           'vendor' => trim($body['vendor_requirements'] ?? ''),
           'cap' => $capacity,
           'tax' => trim($body['tax_treatment'] ?? 'Inclusive of GST'),
           'cancel' => trim($body['cancellation_terms'] ?? '')
       ]);
       
    json_response(201, ['success' => true, 'message' => 'Package created as Draft.', 'id' => $pkgId]);
}

if ($method === 'PATCH') {
    $body = get_json_body();
    $pkgId = trim($body['id'] ?? '');
    $action = trim($body['action'] ?? '');
    
    // Verify ownership
    $check = $db->prepare('SELECT status FROM influencer_packages WHERE id=:id AND influencer_id=:inf');
    $check->execute(['id' => $pkgId, 'inf' => $id]);
    $currentStatus = $check->fetchColumn();
    
    if (!$currentStatus) json_response(404, ['success' => false, 'message' => 'Package not found.']);

    if ($action === 'submit') {
        $newStatus = 'pending_approval';
    } elseif ($action === 'pause') {
        $newStatus = 'paused';
    } elseif ($action === 'archive') {
        $newStatus = 'archived';
    } else {
        json_response(422, ['success' => false, 'message' => 'Invalid action.']);
    }

    $db->prepare('UPDATE influencer_packages SET status=:status, updated_at=UTC_TIMESTAMP() WHERE id=:id')
       ->execute(['status' => $newStatus, 'id' => $pkgId]);

    json_response(200, ['success' => true, 'message' => 'Package status updated to ' . $newStatus]);
}

json_response(405, ['success' => false, 'message' => 'Method not allowed.']);