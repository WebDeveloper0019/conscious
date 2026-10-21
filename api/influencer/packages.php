<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
send_cors_headers();
$principal = require_principal(['influencer']);
$db = get_db();
$ownerId = $principal['id'];
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $query = $db->prepare('SELECT id,title,description,price_inr,deliverables_json,platforms_json,turnaround_days,revisions,usage_rights,vendor_requirements,capacity,tax_treatment,cancellation_terms,status,review_note,created_at,updated_at FROM influencer_packages WHERE influencer_id=:owner ORDER BY created_at DESC LIMIT 100');
    $query->execute(['owner' => $ownerId]);
    $packages = $query->fetchAll();
    foreach ($packages as &$package) {
        $package['deliverables_json'] = json_decode((string)$package['deliverables_json'], true) ?: [];
        $package['platforms_json'] = json_decode((string)$package['platforms_json'], true) ?: [];
    }
    unset($package);
    json_response(200, ['success' => true, 'packages' => $packages]);
}

if ($method === 'POST') {
    $body = get_json_body();
    $textFields = [
        'title' => [3, 160], 'description' => [20, 3000],
        'usage_rights' => [5, 1000], 'vendor_requirements' => [5, 1000],
        'tax_treatment' => [3, 160], 'cancellation_terms' => [10, 1000],
    ];
    $clean = [];
    foreach ($textFields as $field => [$min, $max]) {
        $value = trim((string)($body[$field] ?? ''));
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
    $id = uuid_string();
    $insert = $db->prepare('INSERT INTO influencer_packages (id,influencer_id,title,description,price_inr,deliverables_json,platforms_json,turnaround_days,revisions,usage_rights,vendor_requirements,capacity,tax_treatment,cancellation_terms,status,created_at,updated_at) VALUES (:id,:owner,:title,:description,:price,:deliverables,:platforms,:turnaround,:revisions,:rights,:requirements,:capacity,:tax,:cancellation,"draft",UTC_TIMESTAMP(),UTC_TIMESTAMP())');
    $insert->execute([
        'id' => $id, 'owner' => $ownerId, 'title' => $clean['title'],
        'description' => $clean['description'], 'price' => round((float)$price, 2),
        'deliverables' => $clean['deliverables_json'], 'platforms' => $clean['platforms_json'],
        'turnaround' => $turnaround, 'revisions' => $revisions,
        'rights' => $clean['usage_rights'], 'requirements' => $clean['vendor_requirements'],
        'capacity' => $capacity, 'tax' => $clean['tax_treatment'],
        'cancellation' => $clean['cancellation_terms'],
    ]);
    audit_event('influencer.package_created', 'influencer_package', $id);
    json_response(201, ['success' => true, 'message' => 'Package saved as a draft.', 'id' => $id]);
}

if ($method === 'PATCH') {
    $body = get_json_body();
    $id = trim((string)($body['id'] ?? ''));
    $action = trim((string)($body['action'] ?? ''));
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
