<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/admin.php';
require_once __DIR__ . '/../lib/notifications.php';
send_cors_headers();
$context = require_admin('influencers.manage');
$db = get_db();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $query = $db->query('SELECT p.id,p.influencer_id,p.title,p.description,p.price_inr,p.deliverables_json,p.platforms_json,p.turnaround_days,p.revisions,p.usage_rights,p.vendor_requirements,p.capacity,p.tax_treatment,p.cancellation_terms,p.status,p.review_note,p.created_at,p.updated_at,i.full_name,i.handle,i.status AS creator_status,i.verification_status AS creator_verification FROM influencer_packages p JOIN influencers i ON i.id=p.influencer_id ORDER BY p.created_at DESC LIMIT 300');
    json_response(200, ['success' => true, 'packages' => $query->fetchAll()]);
}
if ($method !== 'POST') json_response(405, ['success' => false, 'message' => 'Method not allowed.']);

$body = get_json_body();
$id = trim((string)($body['package_id'] ?? ''));
$action = trim((string)($body['action'] ?? ''));
$note = trim((string)($body['note'] ?? ''));
if (!preg_match('/^[a-f0-9-]{36}$/i', $id) || !in_array($action, ['approve', 'request_changes', 'reject'], true)
    || mb_strlen($note) > 500 || ($action !== 'approve' && mb_strlen($note) < 10)) {
    json_response(422, ['success' => false, 'message' => 'Choose a valid decision and provide a clear reason when requesting changes or rejecting.']);
}
$db->beginTransaction();
$query = $db->prepare('SELECT p.*,i.status AS creator_status,i.verification_status AS creator_verification FROM influencer_packages p JOIN influencers i ON i.id=p.influencer_id WHERE p.id=:id FOR UPDATE');
$query->execute(['id' => $id]);
$package = $query->fetch();
if (!$package) json_response(404, ['success' => false, 'message' => 'Package not found.']);
if ($package['status'] !== 'pending_approval') {
    json_response(409, ['success' => false, 'message' => 'Only submitted packages can be reviewed.']);
}
if ($action === 'approve' && ($package['creator_status'] !== 'active' || $package['creator_verification'] !== 'approved')) {
    json_response(409, ['success' => false, 'message' => 'Approve the creator account before publishing a package.']);
}
$status = ['approve' => 'active', 'request_changes' => 'changes_requested', 'reject' => 'rejected'][$action];
$db->prepare('UPDATE influencer_packages SET status=:status,reviewed_by=:admin,reviewed_at=UTC_TIMESTAMP(),review_note=:note,updated_at=UTC_TIMESTAMP() WHERE id=:id')
   ->execute(['status' => $status, 'admin' => $context['admin']['id'], 'note' => $note ?: null, 'id' => $id]);
create_notification('influencer', $package['influencer_id'], 'package_review', 'Package review update', 'Your package "' . $package['title'] . '" is now ' . str_replace('_', ' ', $status) . '.', null, ['package_id' => $id, 'note' => $note]);
audit_event('admin.influencer_package_' . $action, 'influencer_package', $id, ['note' => $note]);
$db->commit();
json_response(200, ['success' => true, 'status' => $status, 'message' => 'Package review saved.']);
