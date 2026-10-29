<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/audit.php';

send_cors_headers();

$principal = require_principal(['customer']); $userId = $principal['id'];
$db      = get_db();
$method  = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// ─── GET: list evidence for a return ───
if ($method === 'GET') {
    $returnId = trim((string)($_GET['return_id'] ?? ''));
    if ($returnId === '') json_response(422, ['success' => false, 'message' => 'return_id is required.']);

    // Ownership check
    $chk = $db->prepare('SELECT id FROM return_requests WHERE id = ? AND user_id = ? LIMIT 1');
    $chk->execute([$returnId, $userId]);
    if (!$chk->fetchColumn()) json_response(404, ['success' => false, 'message' => 'Return request not found.']);

    $stmt = $db->prepare(
        'SELECT re.id, re.file_asset_id, re.caption, re.sort_order, re.created_at,
                f.original_name, f.mime_type, f.size_bytes, f.scan_status,
                CASE WHEN f.scan_status="clean" THEN CONCAT("../files.php?id=", f.id) ELSE NULL END AS url
         FROM return_evidence re
         JOIN file_assets f ON f.id = re.file_asset_id
         WHERE re.return_request_id = ?
           AND f.deleted_at IS NULL
         ORDER BY re.sort_order ASC, re.created_at ASC'
    );
    $stmt->execute([$returnId]);

    $evidence = [];
    foreach ($stmt->fetchAll() as $r) {
        $evidence[] = [
            'id'          => $r['id'],
            'fileId'      => $r['file_asset_id'],
            'url'         => $r['url'],
            'caption'     => $r['caption'],
            'mimeType'    => $r['mime_type'],
            'byteSize'    => (int)$r['size_bytes'],
            'scanStatus'  => $r['scan_status'],
            'uploadedAt'  => $r['created_at'],
        ];
    }

    json_response(200, ['success' => true, 'evidence' => $evidence]);
}

// ─── POST: upload evidence image ───
if ($method !== 'POST') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

require_csrf();

if (rate_limit('return-evidence:' . $userId, 30, 3600)) {
    json_response(429, ['success' => false, 'message' => 'Too many uploads. Try again later.']);
}

// Support both raw multipart and base64-in-JSON
$returnId = '';
$fileId   = '';
$caption  = '';
$createdAssetPath = null;

// Detect multipart
if (!empty($_FILES['file'])) {
    // Multipart upload path
    $returnId = trim((string)($_POST['return_id'] ?? ''));
    $caption  = trim((string)($_POST['caption'] ?? ''));
    $uploaded = $_FILES['file'];

    if ($returnId === '') json_response(422, ['success' => false, 'message' => 'return_id is required.']);
    if (strlen($caption) > 255) json_response(422, ['success' => false, 'message' => 'Caption must be at most 255 characters.']);
    $returnStmt = $db->prepare('SELECT status FROM return_requests WHERE id = ? AND user_id = ? LIMIT 1');
    $returnStmt->execute([$returnId, $userId]);
    $returnStatus = $returnStmt->fetchColumn();
    if ($returnStatus === false) json_response(404, ['success' => false, 'message' => 'Return request not found.']);
    if (in_array(strtolower((string)$returnStatus), ['completed', 'rejected', 'cancelled'], true)) {
        json_response(409, ['success' => false, 'message' => 'Cannot add evidence to a closed return.']);
    }
    $earlyCount = $db->prepare('SELECT COUNT(*) FROM return_evidence WHERE return_request_id = ?');
    $earlyCount->execute([$returnId]);
    if ((int)$earlyCount->fetchColumn() >= 8) {
        json_response(422, ['success' => false, 'message' => 'Maximum 8 images per return request.']);
    }

    if (!is_uploaded_file($uploaded['tmp_name'])) {
        json_response(400, ['success' => false, 'message' => 'Invalid upload.']);
    }
    if ($uploaded['error'] !== UPLOAD_ERR_OK) {
        json_response(400, ['success' => false, 'message' => 'Upload error code: ' . $uploaded['error']]);
    }
    if ($uploaded['size'] > 5 * 1024 * 1024) {
        json_response(413, ['success' => false, 'message' => 'Image must be at most 5 MB.']);
    }

    // Validate mime
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = (string)$finfo->file($uploaded['tmp_name']);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    if (!isset($allowed[$mime])) {
        json_response(422, ['success' => false, 'message' => 'Only JPEG, PNG, WEBP, GIF allowed.']);
    }

    $ext = $allowed[$mime];
    $fileId = uuid_string();
    $storageDir = __DIR__ . '/../../storage/private/return-evidence';
    if (!is_dir($storageDir) && !mkdir($storageDir, 0775, true) && !is_dir($storageDir)) {
        json_response(500, ['success' => false, 'message' => 'Storage unavailable.']);
    }

    $storedName = $fileId . '.' . $ext;
    $storedPath = $storageDir . '/' . $storedName;

    if (!move_uploaded_file($uploaded['tmp_name'], $storedPath)) {
        json_response(500, ['success' => false, 'message' => 'Failed to store file.']);
    }
    $createdAssetPath = $storedPath;

    try {
        $db->prepare(
            'INSERT INTO file_assets
               (id, owner_type, owner_id, purpose, storage_key, original_name, mime_type,
                extension, size_bytes, sha256, visibility, scan_status, created_at)
             VALUES (?, "customer", ?, "return_evidence", ?, ?, ?, ?, ?, ?, "private", "pending", UTC_TIMESTAMP())'
        )->execute([
            $fileId,
            $userId,
            'return-evidence/' . $storedName,
            mb_substr((string)$uploaded['name'], 0, 255),
            $mime,
            $ext,
            (int)$uploaded['size'],
            hash_file('sha256', $storedPath),
        ]);
    } catch (Throwable $e) {
        @unlink($storedPath);
        error_log('file_assets insert failed: ' . $e->getMessage());
        json_response(500, ['success' => false, 'message' => 'Failed to register file.']);
    }
} else {
    // JSON path: expects file_asset_id (already-uploaded file)
    $body     = get_json_body();
    $returnId = trim((string)($body['return_id'] ?? ''));
    $fileId   = trim((string)($body['file_asset_id'] ?? ''));
    $caption  = trim((string)($body['caption'] ?? ''));

    if ($fileId === '') json_response(422, ['success' => false, 'message' => 'Provide file_asset_id or multipart file.']);

    // Verify ownership
    $chk = $db->prepare(
        'SELECT id FROM file_assets WHERE id = ? AND owner_type = "customer" AND owner_id = ?
         AND mime_type IN ("image/jpeg","image/png","image/webp","image/gif")
         AND scan_status = "clean" AND deleted_at IS NULL LIMIT 1'
    );
    $chk->execute([$fileId, $userId]);
    if (!$chk->fetchColumn()) json_response(404, ['success' => false, 'message' => 'File not found.']);
}

if ($returnId === '') json_response(422, ['success' => false, 'message' => 'return_id is required.']);
if (strlen($caption) > 255) json_response(422, ['success' => false, 'message' => 'Caption must be at most 255 characters.']);

// Ownership check on the return
$chk = $db->prepare('SELECT id, status FROM return_requests WHERE id = ? AND user_id = ? LIMIT 1');
$chk->execute([$returnId, $userId]);
$ret = $chk->fetch();
if (!$ret) json_response(404, ['success' => false, 'message' => 'Return request not found.']);

if (in_array(strtolower((string)$ret['status']), ['completed', 'rejected', 'cancelled'], true)) {
    json_response(409, ['success' => false, 'message' => 'Cannot add evidence to a closed return.']);
}

// Cap: 8 images per return
$cnt = $db->prepare('SELECT COUNT(*) FROM return_evidence WHERE return_request_id = ?');
$cnt->execute([$returnId]);
if ((int)$cnt->fetchColumn() >= 8) {
    json_response(422, ['success' => false, 'message' => 'Maximum 8 images per return request.']);
}

try {
    $evidenceId = uuid_string();

    $next = $db->prepare('SELECT COALESCE(MAX(sort_order), -1) + 1 FROM return_evidence WHERE return_request_id = ?');
    $next->execute([$returnId]);
    $sortOrder = (int)$next->fetchColumn();

    $db->prepare(
        'INSERT INTO return_evidence
           (id, return_request_id, file_asset_id, uploaded_by_type, uploaded_by_id, caption, sort_order, created_at)
         VALUES (?, ?, ?, "customer", ?, ?, ?, UTC_TIMESTAMP())'
    )->execute([$evidenceId, $returnId, $fileId, $userId, $caption ?: null, $sortOrder]);

    audit_event('customer.return_evidence_uploaded', 'return_request', $returnId, [
        'evidence_id' => $evidenceId,
        'file_id'     => $fileId,
        'caption'     => $caption,
    ]);

    json_response(201, [
        'success'    => true,
        'message'    => 'Evidence uploaded and queued for security scanning.',
        'evidenceId' => $evidenceId,
        'fileId'     => $fileId,
        'scanStatus' => $createdAssetPath !== null ? 'pending' : 'clean',
        'url'        => $createdAssetPath !== null ? null : '../files.php?id=' . $fileId,
    ]);
} catch (PDOException $e) {
    if ($createdAssetPath !== null) {
        $db->prepare('DELETE FROM file_assets WHERE id = ? AND owner_type = "customer" AND owner_id = ?')->execute([$fileId, $userId]);
        @unlink($createdAssetPath);
    }
    if ((string)$e->getCode() === '23000') {
        json_response(409, ['success' => false, 'message' => 'This image is already attached.']);
    }
    throw $e;
}
