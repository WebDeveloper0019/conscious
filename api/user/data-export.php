<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/audit.php';

send_cors_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

$payload = require_auth();
$userId  = auth_user_id($payload);
$db      = get_db();

// ─── GET: check status of latest export job ───
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $db->prepare(
        'SELECT id, status, file_asset_id, size_bytes, expires_at, error_message,
                started_at, completed_at, created_at
         FROM user_data_export_jobs
         WHERE user_id = ?
         ORDER BY created_at DESC LIMIT 1'
    );
    $stmt->execute([$userId]);
    $job = $stmt->fetch();

    if (!$job) {
        json_response(200, ['success' => true, 'job' => null]);
    }

    $response = [
        'id'          => $job['id'],
        'status'      => $job['status'],
        'sizeBytes'   => $job['size_bytes'] ? (int)$job['size_bytes'] : null,
        'expiresAt'   => $job['expires_at'],
        'error'       => $job['error_message'],
        'requestedAt' => $job['created_at'],
        'completedAt' => $job['completed_at'],
    ];

    if ($job['status'] === 'ready' && $job['file_asset_id']) {
        $response['downloadUrl'] = '../api/user/data-export-download.php?id=' . urlencode((string)$job['id']);
    }

    json_response(200, ['success' => true, 'job' => $response]);
}

// ─── POST: request a new export ───
require_csrf();

if (rate_limit('data-export:' . $userId, 2, 86400)) {
    json_response(429, ['success' => false, 'message' => 'You can request at most 2 exports per day.']);
}

// Reject if a fresh job already exists
$chk = $db->prepare(
    'SELECT id, status FROM user_data_export_jobs
     WHERE user_id = ? AND status IN ("queued","processing") LIMIT 1'
);
$chk->execute([$userId]);
if ($row = $chk->fetch()) {
    json_response(202, [
        'success' => true,
        'message' => 'Export already in progress.',
        'jobId' => $row['id'],
        'status' => $row['status'],
    ]);
}

$jobId = uuid_string();
$db->prepare(
    'INSERT INTO user_data_export_jobs (id, user_id, status, created_at, updated_at)
     VALUES (?, ?, "queued", UTC_TIMESTAMP(), UTC_TIMESTAMP())'
)->execute([$jobId, $userId]);

audit_event('customer.data_export_requested', 'user', $userId, ['job_id' => $jobId]);

json_response(202, [
    'success' => true,
    'message' => 'Export queued. You will be notified when ready.',
    'jobId' => $jobId,
    'status' => 'queued',
]);