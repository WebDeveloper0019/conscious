<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';

if (ob_get_level()) ob_end_clean();

$payload = require_auth();
$userId  = auth_user_id($payload);
$db      = get_db();

$jobId = trim((string)($_GET['id'] ?? ''));
if ($jobId === '') {
    header('Content-Type: application/json');
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'id is required.']);
    exit;
}

$stmt = $db->prepare(
    'SELECT j.id, j.status, j.expires_at, j.file_asset_id,
            f.storage_key, f.original_name, f.size_bytes
     FROM user_data_export_jobs j
     LEFT JOIN file_assets f ON f.id = j.file_asset_id
     WHERE j.id = ? AND j.user_id = ? LIMIT 1'
);
$stmt->execute([$jobId, $userId]);
$job = $stmt->fetch();

if (!$job) {
    header('Content-Type: application/json'); http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Export not found.']); exit;
}
if ($job['status'] !== 'ready' || !$job['file_asset_id']) {
    header('Content-Type: application/json'); http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'Export not ready.']); exit;
}
if ($job['expires_at'] && strtotime((string)$job['expires_at']) < time()) {
    header('Content-Type: application/json'); http_response_code(410);
    echo json_encode(['success' => false, 'message' => 'Export has expired.']); exit;
}

$storageDir = realpath(PRIVATE_STORAGE_PATH);
$path = $storageDir === false ? false : realpath($storageDir . '/' . ltrim((string)$job['storage_key'], '/'));

if ($storageDir === false || $path === false
    || !str_starts_with($path, $storageDir . DIRECTORY_SEPARATOR)
    || !is_file($path)) {
    header('Content-Type: application/json'); http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'File no longer available.']); exit;
}

header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="' . (preg_replace('/[^A-Za-z0-9._-]/', '_', basename((string)$job['original_name'])) ?: 'export.json') . '"');
header('Content-Length: ' . filesize($path));
header('Cache-Control: private, max-age=0, no-store');
header('X-Content-Type-Options: nosniff');

readfile($path);
exit;
