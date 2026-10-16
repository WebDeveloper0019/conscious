<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/audit.php';

send_cors_headers();

$payload = require_auth();
$userId  = auth_user_id($payload);
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
<<<<<<< HEAD
                f.original_name, f.mime_type, f.byte_size, f.width, f.height,
=======
                f.original_name, f.mime_type, f.size_bytes,
>>>>>>> origin/main
                CONCAT("../files.php?id=", f.id) AS url
         FROM return_evidence re
         JOIN file_assets f ON f.id = re.file_asset_id
         WHERE re.return_request_id = ?
           AND f.deleted_at IS NULL
           AND f.scan_status = "clean"
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
<<<<<<< HEAD
            'byteSize'    => (int)$r['byte_size'],
            'width'       => $r['width'] ? (int)$r['width'] : null,
            'height'      => $r['height'] ? (int)$r['height'] : null,
=======
            'byteSize'    => (int)$r['size_bytes'],
>>>>>>> origin/main
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
<<<<<<< HEAD
=======
$createdAssetPath = null;
>>>>>>> origin/main

// Detect multipart
if (!empty($_FILES['file'])) {
    // Multipart upload path
    $returnId = trim((string)($_POST['return_id'] ?? ''));
    $caption  = trim((string)($_POST['caption'] ?? ''));
    $uploaded = $_FILES['file'];

<<<<<<< HEAD
=======
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

>>>>>>> origin/main
    if (!is_uploaded_file($uploaded['tmp_name'])) {
        json_response(400, ['success' => false, 'message' => 'Invalid upload.']);
    }
    if ($uploaded['error'] !== UPLOAD_ERR_OK) {
        json_response(400, ['success' => false, 'message' => 'Upload error code: ' . $uploaded['error']]);
    }
    if ($uploaded['size'] > 5 * 1024 * 1024) {
<<<<<<< HEAD
        json_response(413, ['success' => false, 'message' => 'Image must be ≤ 5 MB.']);
=======
        json_response(413, ['success' => false, 'message' => 'Image must be at most 5 MB.']);
>>>>>>> origin/main
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
<<<<<<< HEAD

    $width = $height = null;
    $size = @getimagesize($storedPath);
    if ($size) { $width = $size[0]; $height = $size[1]; }
=======
    $createdAssetPath = $storedPath;
>>>>>>> origin/main

    try {
        $db->prepare(
            'INSERT INTO file_assets
<<<<<<< HEAD
               (id, storage_key, original_name, mime_type, byte_size, width, height,
                visibility, scan_status, uploaded_by_type, uploaded_by_id, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, "private", "clean", "customer", ?, UTC_TIMESTAMP())'
        )->execute([
            $fileId,
            'return-evidence/' . $storedName,
            mb_substr((string)$uploaded['name'], 0, 255),
            $mime,
            (int)$uploaded['size'],
            $width,
            $height,
            $userId,
=======
               (id, owner_type, owner_id, purpose, storage_key, original_name, mime_type,
                extension, size_bytes, sha256, visibility, scan_status, created_at)
             VALUES (?, "customer", ?, "return_evidence", ?, ?, ?, ?, ?, ?, "private", "clean", UTC_TIMESTAMP())'
        )->execute([
            $fileId,
            $userId,
            'return-evidence/' . $storedName,
            mb_substr((string)$uploaded['name'], 0, 255),
            $mime,
            $ext,
            (int)$uploaded['size'],
            hash_file('sha256', $storedPath),
>>>>>>> origin/main
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
<<<<<<< HEAD
    $chk = $db->prepare('SELECT id FROM file_assets WHERE id = ? AND uploaded_by_id = ? AND deleted_at IS NULL LIMIT 1');
=======
    $chk = $db->prepare(
        'SELECT id FROM file_assets WHERE id = ? AND owner_type = "customer" AND owner_id = ?
         AND mime_type IN ("image/jpeg","image/png","image/webp","image/gif")
         AND scan_status = "clean" AND deleted_at IS NULL LIMIT 1'
    );
>>>>>>> origin/main
    $chk->execute([$fileId, $userId]);
    if (!$chk->fetchColumn()) json_response(404, ['success' => false, 'message' => 'File not found.']);
}

if ($returnId === '') json_response(422, ['success' => false, 'message' => 'return_id is required.']);
<<<<<<< HEAD
if (strlen($caption) > 255) json_response(422, ['success' => false, 'message' => 'Caption must be ≤ 255 chars.']);
=======
if (strlen($caption) > 255) json_response(422, ['success' => false, 'message' => 'Caption must be at most 255 characters.']);
>>>>>>> origin/main

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
        'message'    => 'Evidence attached.',
        'evidenceId' => $evidenceId,
        'fileId'     => $fileId,
        'url'        => '../files.php?id=' . $fileId,
    ]);
} catch (PDOException $e) {
<<<<<<< HEAD
=======
    if ($createdAssetPath !== null) {
        $db->prepare('DELETE FROM file_assets WHERE id = ? AND owner_type = "customer" AND owner_id = ?')->execute([$fileId, $userId]);
        @unlink($createdAssetPath);
    }
>>>>>>> origin/main
    if ((string)$e->getCode() === '23000') {
        json_response(409, ['success' => false, 'message' => 'This image is already attached.']);
    }
    throw $e;
<<<<<<< HEAD
}
=======
}
>>>>>>> origin/main
