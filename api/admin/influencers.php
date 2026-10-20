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
    $creators = $db->query('SELECT id,full_name,email,mobile,handle,status,verification_status,email_verified,platforms_json,audience_json,created_at FROM influencers ORDER BY created_at DESC LIMIT 300');
    json_response(200, ['success' => true, 'influencers' => $creators->fetchAll()]);
}

if ($method !== 'POST') json_response(405, ['success' => false, 'message' => 'Method not allowed.']);

$body = get_json_body();
$id = trim((string)($body['influencer_id'] ?? ''));
$action = trim((string)($body['action'] ?? ''));
$reason = trim((string)($body['reason'] ?? ''));
$next = [
    'approve' => 'active',
    'request_changes' => 'changes_requested',
    'reject' => 'rejected',
    'suspend' => 'suspended',
    'reactivate' => 'active',
][$action] ?? '';
if ($id === '' || $next === '' || mb_strlen($reason) > 500 || (!in_array($action, ['approve', 'reactivate'], true) && mb_strlen($reason) < 10)) {
    json_response(422, ['success' => false, 'message' => 'Choose a valid decision and enter a detailed reason when required.']);
}

$db->beginTransaction();
$creatorQuery = $db->prepare('SELECT * FROM influencers WHERE id=:id FOR UPDATE');
$creatorQuery->execute(['id' => $id]);
$creator = $creatorQuery->fetch();
if (!$creator) json_response(404, ['success' => false, 'message' => 'Creator not found.']);
if (in_array($action, ['approve', 'reactivate'], true) && !(bool)$creator['email_verified']) {
    json_response(409, ['success' => false, 'message' => 'Verify the creator email before activating this account.']);
}
if ($action === 'approve') {
    $documentQuery = $db->prepare('SELECT d.document_type,d.verification_status,f.scan_status FROM influencer_documents d JOIN file_assets f ON f.id=d.file_asset_id WHERE d.influencer_id=:id AND f.deleted_at IS NULL ORDER BY d.uploaded_at DESC,d.id DESC');
    $documentQuery->execute(['id' => $id]);
    $latest = [];
    foreach ($documentQuery->fetchAll() as $document) {
        if (!isset($latest[$document['document_type']])) $latest[$document['document_type']] = $document;
    }
    foreach (['identity', 'pan', 'social_ownership'] as $type) {
        if (!isset($latest[$type]) || $latest[$type]['verification_status'] !== 'approved' || $latest[$type]['scan_status'] !== 'clean') {
            json_response(409, ['success' => false, 'message' => 'Approve the latest clean identity, PAN and social ownership documents before approving this creator.']);
        }
    }
    $audience = json_decode((string)$creator['audience_json'], true);
    if (mb_strlen((string)$creator['bio']) < 40 || !is_array($audience) || count($audience['niches'] ?? []) < 1 || count($audience['languages'] ?? []) < 1) {
        json_response(409, ['success' => false, 'message' => 'Check the creator bio, niches and languages before approval.']);
    }
}

$transitions = [
    'pending_review' => ['active', 'changes_requested', 'rejected'],
    'changes_requested' => ['pending_review', 'rejected'],
    'active' => ['suspended'],
    'suspended' => ['active'],
];
assert_transition((string)$creator['status'], $next, $transitions);
$verification = $next === 'active' ? 'approved' : ($next === 'changes_requested' ? 'changes_requested' : ($next === 'rejected' ? 'rejected' : $creator['verification_status']));
$db->prepare('UPDATE influencers SET status=:status,verification_status=:verification,updated_at=UTC_TIMESTAMP() WHERE id=:id')
   ->execute(['status' => $next, 'verification' => $verification, 'id' => $id]);
create_notification('influencer', $id, 'application_update', 'Creator programme update', 'Your application/account is now ' . str_replace('_', ' ', $next) . '.', null, ['reason' => $reason]);
record_status_change('influencer', $id, $creator['status'], $next, $reason ?: null);
audit_event('admin.influencer_' . $action, 'influencer', $id, ['reason' => $reason]);
$db->commit();
json_response(200, ['success' => true, 'message' => 'Creator status updated.', 'status' => $next]);
