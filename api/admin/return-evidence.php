<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';

send_cors_headers();
$principal = require_principal(['admin']);
$db = get_db();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_response(405, ['success' => false, 'message' => 'Method not allowed.']);

$returnId = trim((string)($_GET['return_id'] ?? ''));
if ($returnId === '') json_response(422, ['success' => false, 'message' => 'return_id is required.']);

$stmt = $db->prepare(
    'SELECT re.id, re.file_asset_id, re.caption, re.uploaded_by_type, re.created_at,
            f.original_name, f.mime_type, f.byte_size, f.width, f.height, f.scan_status,
            CONCAT("../files.php?id=", f.id) AS url
     FROM return_evidence re
     JOIN file_assets f ON f.id = re.file_asset_id
     WHERE re.return_request_id = ? AND f.deleted_at IS NULL
     ORDER BY re.sort_order, re.created_at'
);
$stmt->execute([$returnId]);

$evidence = [];
foreach ($stmt->fetchAll() as $r) {
    $evidence[] = [
        'id'         => $r['id'],
        'fileId'     => $r['file_asset_id'],
        'url'        => $r['url'],
        'caption'    => $r['caption'],
        'mimeType'   => $r['mime_type'],
        'byteSize'   => (int)$r['byte_size'],
        'width'      => $r['width'] ? (int)$r['width'] : null,
        'height'     => $r['height'] ? (int)$r['height'] : null,
        'uploadedBy' => $r['uploaded_by_type'],
        'scanStatus' => $r['scan_status'],
        'uploadedAt' => $r['created_at'],
    ];
}

json_response(200, ['success' => true, 'evidence' => $evidence]);