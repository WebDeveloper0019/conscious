<?php
declare(strict_types=1);
<<<<<<< HEAD
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
    
=======

require_once __DIR__ . '/../lib/helpers.php';
send_cors_headers();
$principal = require_principal(['influencer']);
$db = get_db();
$ownerId = $principal['id'];
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

function validated_package_fields(array $body): array {
    $textFields = [
        'title' => [3, 160], 'description' => [20, 3000],
        'usage_rights' => [5, 1000], 'vendor_requirements' => [5, 1000],
        'tax_treatment' => [3, 160], 'cancellation_terms' => [10, 1000],
    ];
    $clean = [];
    foreach ($textFields as $field => [$min, $max]) {
        $raw = $body[$field] ?? null;
        $value = is_string($raw) ? trim($raw) : '';
        if (mb_strlen($value) < $min || mb_strlen($value) > $max) {
            json_response(422, ['success' => false, 'message' => 'Check the ' . str_replace('_', ' ', $field) . ' field.']);
        }
        $clean[$field] = $value;
    }
    $price = filter_var($body['price_inr'] ?? null, FILTER_VALIDATE_FLOAT);
    $turnaround = filter_var($body['turnaround_days'] ?? null, FILTER_VALIDATE_INT);
    $revisions = filter_var($body['revisions'] ?? null, FILTER_VALIDATE_INT);
    $capacity = filter_var($body['capacity'] ?? null, FILTER_VALIDATE_INT);
    if ($price === false || !is_finite((float)$price) || $price < 100 || $price > 10000000
        || $turnaround === false || $turnaround < 1 || $turnaround > 365
        || $revisions === false || $revisions < 0 || $revisions > 20
        || $capacity === false || $capacity < 1 || $capacity > 1000) {
        json_response(422, ['success' => false, 'message' => 'Enter a valid price, turnaround, revisions and monthly capacity.']);
    }
    foreach (['deliverables_json', 'platforms_json'] as $field) {
        $values = $body[$field] ?? null;
        if (!is_array($values) || !array_is_list($values) || count($values) < 1 || count($values) > 12) {
            json_response(422, ['success' => false, 'message' => 'Add one to twelve deliverables and platforms.']);
        }
        $validated = [];
        foreach ($values as $value) {
            if (!is_string($value) || mb_strlen(trim($value)) < 2 || mb_strlen(trim($value)) > 120) {
                json_response(422, ['success' => false, 'message' => 'Each deliverable and platform must be 2–120 characters.']);
            }
            $validated[] = trim($value);
        }
        $clean[$field] = json_encode($validated, JSON_UNESCAPED_UNICODE);
    }
    $clean['price_inr'] = round((float)$price, 2);
    $clean['turnaround_days'] = $turnaround;
    $clean['revisions'] = $revisions;
    $clean['capacity'] = $capacity;
    return $clean;
}

if ($method === 'GET') {
    $query = $db->prepare('SELECT id,title,description,price_inr,deliverables_json,platforms_json,turnaround_days,revisions,usage_rights,vendor_requirements,capacity,tax_treatment,cancellation_terms,status,review_note,created_at,updated_at FROM influencer_packages WHERE influencer_id=:owner ORDER BY created_at DESC LIMIT 100');
    $query->execute(['owner' => $ownerId]);
    $packages = $query->fetchAll();
    foreach ($packages as &$package) {
        $package['deliverables_json'] = json_decode((string)$package['deliverables_json'], true) ?: [];
        $package['platforms_json'] = json_decode((string)$package['platforms_json'], true) ?: [];
    }
    unset($package);
>>>>>>> main
    json_response(200, ['success' => true, 'packages' => $packages]);
}

if ($method === 'POST') {
    $body = get_json_body();
<<<<<<< HEAD
    
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
=======
    $clean = validated_package_fields($body);
    $id = uuid_string();
    $insert = $db->prepare('INSERT INTO influencer_packages (id,influencer_id,title,description,price_inr,deliverables_json,platforms_json,turnaround_days,revisions,usage_rights,vendor_requirements,capacity,tax_treatment,cancellation_terms,status,created_at,updated_at) VALUES (:id,:owner,:title,:description,:price,:deliverables,:platforms,:turnaround,:revisions,:rights,:requirements,:capacity,:tax,:cancellation,"draft",UTC_TIMESTAMP(),UTC_TIMESTAMP())');
    $insert->execute([
        'id' => $id, 'owner' => $ownerId, 'title' => $clean['title'],
        'description' => $clean['description'], 'price' => $clean['price_inr'],
        'deliverables' => $clean['deliverables_json'], 'platforms' => $clean['platforms_json'],
        'turnaround' => $clean['turnaround_days'], 'revisions' => $clean['revisions'],
        'rights' => $clean['usage_rights'], 'requirements' => $clean['vendor_requirements'],
        'capacity' => $clean['capacity'], 'tax' => $clean['tax_treatment'],
        'cancellation' => $clean['cancellation_terms'],
    ]);
    audit_event('influencer.package_created', 'influencer_package', $id);
    json_response(201, ['success' => true, 'message' => 'Package saved as a draft.', 'id' => $id]);
>>>>>>> main
}

if ($method === 'PATCH') {
    $body = get_json_body();
<<<<<<< HEAD
    $pkgId = trim($body['id'] ?? '');
    $action = trim($body['action'] ?? '');
    
    // Verify ownership
    $check = $db->prepare('SELECT status FROM influencer_packages WHERE id=:id AND influencer_id=:inf');
    $check->execute(['id' => $pkgId, 'inf' => $id]);
    $currentStatus = $check->fetchColumn();
    
    if (!$currentStatus) json_response(404, ['success' => false, 'message' => 'Package not found.']);

    if ($action === 'edit') {
        $title = trim($body['title'] ?? '');
        $description = trim($body['description'] ?? '');
        $price = (float)($body['price_inr'] ?? 0);
        $turnaround = (int)($body['turnaround_days'] ?? 0);
        $revisions = (int)($body['revisions'] ?? 1);
        $capacity = (int)($body['capacity'] ?? 1);
        
        if (mb_strlen($title) < 3 || $price < 100 || $turnaround < 1 || $capacity < 1) {
            json_response(422, ['success' => false, 'message' => 'Please fill in all required fields correctly (Min price ₹100).']);
        }

        $db->prepare('UPDATE influencer_packages SET 
            title=:title, description=:desc, price_inr=:price, deliverables_json=:deliv, 
            platforms_json=:plat, turnaround_days=:turn, revisions=:rev, usage_rights=:usage, 
            vendor_requirements=:vendor, capacity=:cap, tax_treatment=:tax, cancellation_terms=:cancel, 
            updated_at=UTC_TIMESTAMP() WHERE id=:id')
           ->execute([
               'title' => $title, 'desc' => $description, 'price' => $price,
               'deliv' => json_encode($body['deliverables_json'] ?? []),
               'plat' => json_encode($body['platforms_json'] ?? []),
               'turn' => $turnaround, 'rev' => $revisions, 'usage' => trim($body['usage_rights'] ?? ''),
               'vendor' => trim($body['vendor_requirements'] ?? ''), 'cap' => $capacity,
               'tax' => trim($body['tax_treatment'] ?? 'Inclusive of GST'), 'cancel' => trim($body['cancellation_terms'] ?? ''),
               'id' => $pkgId
           ]);
        json_response(200, ['success' => true, 'message' => 'Package updated successfully.']);
    }

    if ($action === 'submit') {
        $newStatus = 'pending_approval';
    } elseif ($action === 'pause') {
        $newStatus = 'paused';
    } elseif ($action === 'resume' || $action === 'activate') {
        $newStatus = 'active'; // Reactivating a paused package
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
=======
    $id = trim((string)($body['id'] ?? ''));
    $action = trim((string)($body['action'] ?? ''));
    if ($action === 'edit') {
        if (!preg_match('/^[a-f0-9-]{36}$/i', $id)) json_response(422, ['success' => false, 'message' => 'Choose a valid package.']);
        $clean = validated_package_fields($body);
        $db->beginTransaction();
        $query = $db->prepare('SELECT p.status FROM influencer_packages p WHERE p.id=:id AND p.influencer_id=:owner FOR UPDATE');
        $query->execute(['id' => $id, 'owner' => $ownerId]);
        $package = $query->fetch();
        if (!$package) json_response(404, ['success' => false, 'message' => 'Package not found.']);
        if (!in_array($package['status'], ['draft', 'changes_requested', 'active', 'paused'], true)) {
            json_response(409, ['success' => false, 'message' => 'This package cannot be edited while it is under review or archived.']);
        }
        $next = in_array($package['status'], ['active', 'paused'], true) ? 'pending_approval' : 'draft';
        $db->prepare('UPDATE influencer_packages SET title=:title,description=:description,price_inr=:price,deliverables_json=:deliverables,platforms_json=:platforms,turnaround_days=:turnaround,revisions=:revisions,usage_rights=:rights,vendor_requirements=:requirements,capacity=:capacity,tax_treatment=:tax,cancellation_terms=:cancellation,status=:status,review_note=NULL,reviewed_by=NULL,reviewed_at=NULL,updated_at=UTC_TIMESTAMP() WHERE id=:id AND influencer_id=:owner')
            ->execute([
                'title' => $clean['title'], 'description' => $clean['description'], 'price' => $clean['price_inr'],
                'deliverables' => $clean['deliverables_json'], 'platforms' => $clean['platforms_json'],
                'turnaround' => $clean['turnaround_days'], 'revisions' => $clean['revisions'],
                'rights' => $clean['usage_rights'], 'requirements' => $clean['vendor_requirements'],
                'capacity' => $clean['capacity'], 'tax' => $clean['tax_treatment'],
                'cancellation' => $clean['cancellation_terms'], 'status' => $next,
                'id' => $id, 'owner' => $ownerId,
            ]);
        $db->commit();
        audit_event('influencer.package_edited', 'influencer_package', $id, ['previous_status' => $package['status'], 'status' => $next]);
        json_response(200, ['success' => true, 'status' => $next, 'message' => $next === 'pending_approval' ? 'Changes saved. The package is hidden until admin approves the revised terms.' : 'Package draft updated.']);
    }
    $transitions = [
        'submit' => ['draft' => 'pending_approval', 'changes_requested' => 'pending_approval'],
        'pause' => ['active' => 'paused'],
        'resume' => ['paused' => 'active'],
        'archive' => ['draft' => 'archived', 'changes_requested' => 'archived', 'active' => 'archived', 'paused' => 'archived'],
    ];
    if (!isset($transitions[$action]) || !preg_match('/^[a-f0-9-]{36}$/i', $id)) {
        json_response(422, ['success' => false, 'message' => 'Choose a valid package and action.']);
    }
    $db->beginTransaction();
    $query = $db->prepare('SELECT p.status,i.status AS creator_status,i.verification_status FROM influencer_packages p JOIN influencers i ON i.id=p.influencer_id WHERE p.id=:id AND p.influencer_id=:owner FOR UPDATE');
    $query->execute(['id' => $id, 'owner' => $ownerId]);
    $package = $query->fetch();
    if (!$package) json_response(404, ['success' => false, 'message' => 'Package not found.']);
    $next = $transitions[$action][$package['status']] ?? null;
    if ($next === null) json_response(409, ['success' => false, 'message' => 'This package cannot be changed from its current status.']);
    if (in_array($action, ['submit', 'resume'], true) && ($package['creator_status'] !== 'active' || $package['verification_status'] !== 'approved')) {
        json_response(409, ['success' => false, 'message' => 'Your creator account must be approved before this package can be published.']);
    }
    $db->prepare('UPDATE influencer_packages SET status=:status,review_note=NULL,updated_at=UTC_TIMESTAMP() WHERE id=:id')->execute(['status' => $next, 'id' => $id]);
    $db->commit();
    audit_event('influencer.package_' . $action, 'influencer_package', $id);
    json_response(200, ['success' => true, 'status' => $next, 'message' => 'Package updated.']);
}

json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
>>>>>>> main
