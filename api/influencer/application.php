<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/crypto.php';
require_once __DIR__ . '/../lib/upload.php';
require_once __DIR__ . '/../lib/india.php';

send_cors_headers();
$principal = require_principal(['influencer']);
$id = $principal['id'];
$db = get_db();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $profileQuery = $db->prepare('SELECT id,full_name,email,mobile,handle,bio,platforms_json,audience_json,status,verification_status FROM influencers WHERE id=:id');
    $profileQuery->execute(['id' => $id]);
    $profile = $profileQuery->fetch();
    if (!$profile) json_response(404, ['success' => false, 'message' => 'Creator account was not found.']);

    $documentQuery = $db->prepare('SELECT d.id,d.document_type,d.verification_status,d.rejection_reason,d.created_at,d.uploaded_at,f.scan_status,f.id AS file_id FROM influencer_documents d JOIN file_assets f ON f.id=d.file_asset_id WHERE d.influencer_id=:id AND f.deleted_at IS NULL ORDER BY d.uploaded_at DESC,d.id DESC');
    $documentQuery->execute(['id' => $id]);
    $payoutQuery = $db->prepare('SELECT beneficiary_name,account_last4,pan_last4,verification_status FROM influencer_payout_profiles WHERE influencer_id=:id');
    $payoutQuery->execute(['id' => $id]);
    $reviewQuery = $db->prepare('SELECT to_status,reason,created_at FROM status_history WHERE entity_type="influencer" AND entity_id=:id AND actor_type="admin" AND reason IS NOT NULL ORDER BY id DESC LIMIT 1');
    $reviewQuery->execute(['id' => $id]);
    json_response(200, [
        'success' => true,
        'profile' => $profile,
        'documents' => $documentQuery->fetchAll(),
        'payout_profile' => $payoutQuery->fetch() ?: null,
        'review' => $reviewQuery->fetch() ?: null,
        'states' => india_states_and_uts(),
    ]);
}

if ($method === 'PATCH') {
    $body = get_json_body();
    $bio = trim((string)($body['bio'] ?? ''));
    $platforms = $body['platforms'] ?? null;
    $audience = $body['audience'] ?? null;
    if (mb_strlen($bio) < 40 || mb_strlen($bio) > 2000 || !is_array($platforms) || !array_is_list($platforms) || count($platforms) < 1 || count($platforms) > 5 || !is_array($audience) || array_is_list($audience)) {
        json_response(422, ['success' => false, 'message' => 'Enter a 40–2000 character bio, one to five social profiles, and an audience region.']);
    }
    $region = trim((string)($audience['region'] ?? ''));
    $city = trim((string)($audience['city'] ?? ''));
    $stateInput = trim((string)($audience['state'] ?? ''));
    $state = $stateInput === '' ? '' : canonical_india_state($stateInput);
    $niches = $audience['niches'] ?? null;
    $languages = $audience['languages'] ?? null;
    if (mb_strlen($region) > 100 || mb_strlen($city) > 100 || ($city === '') !== ($stateInput === '') || $state === null) {
        json_response(422, ['success' => false, 'message' => 'Enter a valid city and Indian state or leave both blank.']);
    }
    foreach (['niches' => $niches, 'languages' => $languages] as $field => $values) {
        if (!is_array($values) || !array_is_list($values) || count($values) < 1 || count($values) > 5) {
            json_response(422, ['success' => false, 'message' => 'Enter one to five creator niches and languages.']);
        }
        $clean = [];
        foreach ($values as $value) {
            if (!is_string($value) || mb_strlen(trim($value)) < 2 || mb_strlen(trim($value)) > 50) {
                json_response(422, ['success' => false, 'message' => 'Each niche and language must be 2–50 characters.']);
            }
            $clean[] = trim($value);
        }
        if (count(array_unique(array_map('mb_strtolower', $clean))) !== count($clean)) {
            json_response(422, ['success' => false, 'message' => 'Remove duplicate niches or languages.']);
        }
        if ($field === 'niches') $niches = $clean;
        else $languages = $clean;
    }
    $cleanPlatforms = [];
    foreach ($platforms as $platform) {
        if (!is_array($platform)) json_response(422, ['success' => false, 'message' => 'Check each social profile.']);
        $name = trim((string)($platform['name'] ?? ''));
        $url = trim((string)($platform['url'] ?? ''));
        $followers = filter_var($platform['followers'] ?? null, FILTER_VALIDATE_INT);
        if (mb_strlen($name) < 2 || mb_strlen($name) > 50 || mb_strlen($url) > 255 || !filter_var($url, FILTER_VALIDATE_URL) || strtolower((string)parse_url($url, PHP_URL_SCHEME)) !== 'https' || $followers === false || $followers < 0 || $followers > 1000000000) {
            json_response(422, ['success' => false, 'message' => 'Enter a valid HTTPS social profile URL and follower count for every platform.']);
        }
        $cleanPlatforms[] = ['name' => $name, 'url' => $url, 'followers' => $followers];
    }
    $db->beginTransaction();
    $currentQuery = $db->prepare('SELECT status FROM influencers WHERE id=:id FOR UPDATE');
    $currentQuery->execute(['id' => $id]);
    $previous = $currentQuery->fetchColumn();
    if (!in_array($previous, ['application_draft', 'changes_requested', 'pending_review', 'active'], true)) {
        json_response(409, ['success' => false, 'message' => 'This application cannot be edited in its current state.']);
    }
    $next = $previous === 'active' ? 'pending_review' : ($previous === 'pending_review' ? 'application_draft' : $previous);
    $db->prepare('UPDATE influencers SET bio=:bio,platforms_json=:platforms,audience_json=:audience,status=:status,verification_status=IF(:review="pending_review","submitted",IF(:draft="application_draft","not_submitted",verification_status)),updated_at=UTC_TIMESTAMP() WHERE id=:id')
       ->execute(['bio' => $bio, 'platforms' => json_encode($cleanPlatforms, JSON_UNESCAPED_SLASHES), 'audience' => json_encode(['region' => $region, 'city' => $city, 'state' => $state, 'niches' => $niches, 'languages' => $languages], JSON_UNESCAPED_UNICODE), 'status' => $next, 'review' => $next, 'draft' => $next, 'id' => $id]);
    if ($previous !== $next) record_status_change('influencer', $id, $previous, $next, 'Creator edited the application.');
    $db->commit();
    $message = $previous === 'active' ? 'Changes saved for admin re-review. Your public listing pauses until approval.' : ($previous === 'pending_review' ? 'Changes saved as a draft. Submit the application again when ready.' : 'Creator profile saved.');
    json_response(200, ['success' => true, 'message' => $message, 'status' => $next]);
}

if ($method === 'POST') {
    $action = trim((string)($_GET['action'] ?? ''));
    if ($action === 'upload') {
        $type = trim((string)($_POST['document_type'] ?? ''));
        if (!in_array($type, ['identity', 'pan', 'address', 'social_ownership'], true) || !isset($_FILES['document'])) {
            json_response(422, ['success' => false, 'message' => 'Choose a valid document type and file.']);
        }
        $statusQuery = $db->prepare('SELECT status FROM influencers WHERE id=:id');
        $statusQuery->execute(['id' => $id]);
        $status = $statusQuery->fetchColumn();
        if (!in_array($status, ['application_draft', 'changes_requested', 'pending_review', 'active'], true)) {
            json_response(409, ['success' => false, 'message' => 'Documents cannot be uploaded in the current account state.']);
        }
        $asset = store_private_upload($_FILES['document'], 'influencer', $id, 'influencer_' . $type, ['image/jpeg', 'image/png', 'application/pdf'], 5 * 1024 * 1024);
        $documentId = uuid_string();
        $db->prepare('INSERT INTO influencer_documents (id,influencer_id,document_type,file_asset_id,verification_status,created_at,uploaded_at,updated_at) VALUES (:id,:owner,:type,:file,"pending",UTC_TIMESTAMP(),UTC_TIMESTAMP(6),UTC_TIMESTAMP())')
           ->execute(['id' => $documentId, 'owner' => $id, 'type' => $type, 'file' => $asset['id']]);
        if ($status === 'pending_review') {
            $db->prepare('UPDATE influencers SET status="application_draft",verification_status="not_submitted",updated_at=UTC_TIMESTAMP() WHERE id=:id')->execute(['id' => $id]);
            record_status_change('influencer', $id, 'pending_review', 'application_draft', 'Creator uploaded a replacement document.');
        } elseif ($status === 'active') {
            $db->prepare('UPDATE influencers SET status="pending_review",verification_status="submitted",updated_at=UTC_TIMESTAMP() WHERE id=:id')->execute(['id' => $id]);
            record_status_change('influencer', $id, 'active', 'pending_review', 'Creator uploaded a replacement document for re-review.');
        }
        json_response(201, ['success' => true, 'message' => 'Document uploaded. Security scanning is pending.', 'document_id' => $documentId]);
    }
    if ($action === 'payout') {
        $body = get_json_body();
        $beneficiary = trim((string)($body['beneficiary_name'] ?? ''));
        $account = trim((string)($body['account_number'] ?? ''));
        $ifsc = strtoupper(trim((string)($body['ifsc'] ?? '')));
        $pan = strtoupper(trim((string)($body['pan'] ?? '')));
        if (mb_strlen($beneficiary) < 2 || mb_strlen($beneficiary) > 150 || !preg_match('/^[0-9]{9,18}$/', $account) || !preg_match('/^[A-Z]{4}0[A-Z0-9]{6}$/', $ifsc) || !preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]$/', $pan)) {
            json_response(422, ['success' => false, 'message' => 'Enter a valid beneficiary name, account number, IFSC and PAN.']);
        }
        $db->prepare('INSERT INTO influencer_payout_profiles (influencer_id,beneficiary_name,account_number_encrypted,account_last4,ifsc_encrypted,pan_encrypted,pan_last4,verification_status,updated_at) VALUES (:id,:name,:account,:last4,:ifsc,:pan,:pan4,"pending",UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE beneficiary_name=VALUES(beneficiary_name),account_number_encrypted=VALUES(account_number_encrypted),account_last4=VALUES(account_last4),ifsc_encrypted=VALUES(ifsc_encrypted),pan_encrypted=VALUES(pan_encrypted),pan_last4=VALUES(pan_last4),verification_status="pending",updated_at=UTC_TIMESTAMP()')
           ->execute(['id' => $id, 'name' => $beneficiary, 'account' => encrypt_sensitive($account), 'last4' => substr($account, -4), 'ifsc' => encrypt_sensitive($ifsc), 'pan' => encrypt_sensitive($pan), 'pan4' => substr($pan, -4)]);
        json_response(200, ['success' => true, 'message' => 'Payout details saved for verification.']);
    }
    if ($action === 'submit') {
        $db->beginTransaction();
        $profileQuery = $db->prepare('SELECT * FROM influencers WHERE id=:id FOR UPDATE');
        $profileQuery->execute(['id' => $id]);
        $creator = $profileQuery->fetch();
        if (!$creator || !in_array($creator['status'], ['application_draft', 'changes_requested'], true)) {
            json_response(409, ['success' => false, 'message' => 'This application cannot be submitted in its current state.']);
        }
        $platforms = json_decode((string)$creator['platforms_json'], true);
        $audience = json_decode((string)$creator['audience_json'], true);
        if (mb_strlen((string)$creator['bio']) < 40 || !is_array($platforms) || count($platforms) < 1 || !is_array($audience) || count($audience['niches'] ?? []) < 1 || count($audience['languages'] ?? []) < 1) {
            json_response(422, ['success' => false, 'message' => 'Save your creator bio, niches, languages and social profiles first.']);
        }
        $documentQuery = $db->prepare('SELECT d.document_type,d.verification_status,f.scan_status FROM influencer_documents d JOIN file_assets f ON f.id=d.file_asset_id WHERE d.influencer_id=:id AND f.deleted_at IS NULL ORDER BY d.uploaded_at DESC,d.id DESC');
        $documentQuery->execute(['id' => $id]);
        $latest = [];
        foreach ($documentQuery->fetchAll() as $document) {
            if (!isset($latest[$document['document_type']])) $latest[$document['document_type']] = $document;
        }
        foreach (['identity', 'pan', 'social_ownership'] as $type) {
            if (!isset($latest[$type])) json_response(422, ['success' => false, 'message' => 'Upload identity, PAN and social ownership documents before submitting.']);
            if ($latest[$type]['scan_status'] !== 'clean') json_response(422, ['success' => false, 'message' => 'Wait for every required document to pass the security scan.']);
            if ($latest[$type]['verification_status'] === 'rejected') json_response(422, ['success' => false, 'message' => 'Replace rejected documents before submitting again.']);
        }
        $db->prepare('UPDATE influencers SET status="pending_review",verification_status="submitted",updated_at=UTC_TIMESTAMP() WHERE id=:id')->execute(['id' => $id]);
        record_status_change('influencer', $id, $creator['status'], 'pending_review');
        $db->commit();
        json_response(200, ['success' => true, 'message' => 'Creator application submitted for admin review.']);
    }
}
json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
