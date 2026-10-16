<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/helpers.php';
<<<<<<< HEAD
require_once __DIR__ . '/lib/audit.php';
=======
require_once __DIR__ . '/lib/admin.php';
require_once __DIR__ . '/lib/upload.php';
>>>>>>> origin/main

send_cors_headers();

$principal = require_principal(['customer', 'vendor', 'admin', 'influencer']);
<<<<<<< HEAD
$db        = get_db();
$userId    = $principal['id'];
$userType  = $principal['type'];
$method    = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $messageId = trim((string)($_GET['message_id'] ?? ''));
    if ($messageId === '') json_response(422, ['success' => false, 'message' => 'message_id is required.']);

    $msg = $db->prepare(
        'SELECT m.id, t.id AS ticket_id, t.opened_by_type, t.opened_by_id
         FROM support_messages m
         JOIN support_tickets t ON t.id = m.ticket_id
         WHERE m.id = ? LIMIT 1'
    );
    $msg->execute([$messageId]);
    $m = $msg->fetch();
    if (!$m) json_response(404, ['success' => false, 'message' => 'Message not found.']);

    if ($userType !== 'admin') {
        if ($m['opened_by_type'] !== $userType || $m['opened_by_id'] !== $userId) {
            json_response(403, ['success' => false, 'message' => 'Forbidden.']);
        }
    }

    $stmt = $db->prepare(
        'SELECT sa.file_asset_id, f.original_name, f.mime_type, f.size_bytes, f.created_at
         FROM support_attachments sa
         JOIN file_assets f ON f.id = sa.file_asset_id
         WHERE sa.message_id = ? AND f.deleted_at IS NULL
         ORDER BY f.created_at ASC'
    );
    $stmt->execute([$messageId]);

    $attachments = [];
    foreach ($stmt->fetchAll() as $r) {
        $attachments[] = [
            'fileId'       => $r['file_asset_id'],
            'url'          => '../files.php?id=' . urlencode((string)$r['file_asset_id']),
            'originalName' => $r['original_name'],
            'mimeType'     => $r['mime_type'],
            'sizeBytes'    => (int)$r['size_bytes'],
            'uploadedAt'   => $r['created_at'],
        ];
    }
    json_response(200, ['success' => true, 'attachments' => $attachments]);
}

if ($method !== 'POST') json_response(405, ['success' => false, 'message' => 'Method not allowed.']);

require_csrf();

if (rate_limit('support-attachment:' . $userId, 30, 3600)) {
    json_response(429, ['success' => false, 'message' => 'Too many uploads. Try again later.']);
}

if (empty($_FILES['file'])) json_response(422, ['success' => false, 'message' => 'No file uploaded.']);

$messageId = trim((string)($_POST['message_id'] ?? ''));
if ($messageId === '') json_response(422, ['success' => false, 'message' => 'message_id is required.']);

$msg = $db->prepare(
    'SELECT m.id, m.sender_type, m.sender_id, t.id AS ticket_id, t.status AS ticket_status,
            t.opened_by_type, t.opened_by_id
     FROM support_messages m
     JOIN support_tickets t ON t.id = m.ticket_id
     WHERE m.id = ? LIMIT 1'
);
$msg->execute([$messageId]);
$m = $msg->fetch();
if (!$m) json_response(404, ['success' => false, 'message' => 'Message not found.']);

if ($userType !== 'admin') {
    if ((string)$m['sender_type'] !== $userType || (string)$m['sender_id'] !== $userId) {
        json_response(403, ['success' => false, 'message' => 'You can attach files only to your own message.']);
    }
}
if (in_array(strtolower((string)$m['ticket_status']), ['resolved', 'closed'], true)) {
    json_response(409, ['success' => false, 'message' => 'Cannot add attachments to a closed ticket.']);
}

// Max 5 per message
$cnt = $db->prepare('SELECT COUNT(*) FROM support_attachments WHERE message_id = ?');
$cnt->execute([$messageId]);
if ((int)$cnt->fetchColumn() >= 5) {
    json_response(422, ['success' => false, 'message' => 'Maximum 5 attachments per message.']);
}

$uploaded = $_FILES['file'];
if ($uploaded['error'] !== UPLOAD_ERR_OK) {
    json_response(400, ['success' => false, 'message' => 'Upload error code: ' . $uploaded['error']]);
}
if ((int)$uploaded['size'] > 5 * 1024 * 1024) {
    json_response(413, ['success' => false, 'message' => 'File must be 5 MB or smaller.']);
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime  = (string)$finfo->file($uploaded['tmp_name']);
$allowed = [
    'image/jpeg'      => 'jpg',
    'image/png'       => 'png',
    'image/webp'      => 'webp',
    'image/gif'       => 'gif',
    'application/pdf' => 'pdf',
];
if (!isset($allowed[$mime])) {
    json_response(422, ['success' => false, 'message' => 'Only JPEG, PNG, WEBP, GIF, or PDF allowed.']);
}

if (str_starts_with($mime, 'image/')) {
    $img = @getimagesize($uploaded['tmp_name']);
    if (!$img || $img[0] > 8000 || $img[1] > 8000) {
        json_response(422, ['success' => false, 'message' => 'Image dimensions are not allowed.']);
    }
}

$ext        = $allowed[$mime];
$storageDir = __DIR__ . '/../storage/private/support-attachments';
if (!is_dir($storageDir) && !mkdir($storageDir, 0775, true) && !is_dir($storageDir)) {
    json_response(500, ['success' => false, 'message' => 'Storage unavailable.']);
}

$fileId     = uuid_string();
$storedName = $fileId . '.' . $ext;
$storedPath = $storageDir . '/' . $storedName;

if (!move_uploaded_file($uploaded['tmp_name'], $storedPath)) {
    json_response(500, ['success' => false, 'message' => 'Failed to store file.']);
}

$sha = hash_file('sha256', $storedPath);

try {
    $db->beginTransaction();

    $db->prepare(
        'INSERT INTO file_assets
           (id, owner_type, owner_id, purpose, storage_key, original_name, mime_type, extension,
            size_bytes, sha256, visibility, scan_status, created_at)
         VALUES (?, ?, ?, "support_attachment", ?, ?, ?, ?, ?, ?, "private", "clean", UTC_TIMESTAMP())'
    )->execute([
        $fileId,
        $userType,
        $userId,
        'support-attachments/' . $storedName,
        mb_substr((string)$uploaded['name'], 0, 255),
        $mime,
        $ext,
        (int)$uploaded['size'],
        $sha,
    ]);

    $db->prepare(
        'INSERT INTO support_attachments (message_id, file_asset_id) VALUES (?, ?)'
    )->execute([$messageId, $fileId]);

    audit_event('support.attachment_uploaded', 'support_ticket', (string)$m['ticket_id'], [
        'message_id' => $messageId,
        'file_id'    => $fileId,
        'mime'       => $mime,
        'size'       => (int)$uploaded['size'],
    ]);

    $db->commit();

    json_response(201, [
        'success'      => true,
        'message'      => 'Attachment uploaded.',
        'fileId'       => $fileId,
        'url'          => '../files.php?id=' . urlencode($fileId),
        'mimeType'     => $mime,
        'sizeBytes'    => (int)$uploaded['size'],
        'originalName' => (string)$uploaded['name'],
    ]);
} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    @unlink($storedPath);
    error_log('Support attachment insert failed: ' . $e->getMessage());
    json_response(500, ['success' => false, 'message' => 'Failed to register attachment.']);
}
=======
$db = get_db();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$isAdmin = $principal['type'] === 'admin';

if ($isAdmin) require_permission('support.manage', ['admin']);

function support_attachment_access(array $row, array $principal, bool $isAdmin): bool
{
    if ($isAdmin) return true;

    return (int)$row['is_internal'] === 0
        && hash_equals((string)$row['opened_by_type'], (string)$principal['type'])
        && hash_equals((string)$row['opened_by_id'], (string)$principal['id']);
}

if ($method === 'GET') {
    $fileId = trim((string)($_GET['file_id'] ?? ''));
    $messageId = trim((string)($_GET['message_id'] ?? ''));

    if ($fileId !== '') {
        $stmt = $db->prepare(
            'SELECT f.*, m.is_internal, t.opened_by_type, t.opened_by_id
             FROM support_attachments sa
             JOIN file_assets f ON f.id = sa.file_asset_id
             JOIN support_messages m ON m.id = sa.message_id
             JOIN support_tickets t ON t.id = m.ticket_id
             WHERE f.id = :file AND f.deleted_at IS NULL
             LIMIT 1'
        );
        $stmt->execute(['file' => $fileId]);
        $asset = $stmt->fetch();

        if (!$asset) json_response(404, ['success' => false, 'message' => 'Attachment not found.']);
        if (!support_attachment_access($asset, $principal, $isAdmin)) {
            json_response(403, ['success' => false, 'message' => 'You cannot access this attachment.']);
        }
        if ($asset['scan_status'] !== 'clean') {
            json_response(423, ['success' => false, 'message' => 'Attachment is still being checked and is not ready to download.']);
        }

        $base = realpath(PRIVATE_STORAGE_PATH);
        $path = realpath(rtrim(PRIVATE_STORAGE_PATH, '/\\') . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, (string)$asset['storage_key']));
        if ($base === false || $path === false
            || !str_starts_with($path, $base . DIRECTORY_SEPARATOR)
            || !is_file($path)) {
            json_response(404, ['success' => false, 'message' => 'Attachment file not found.']);
        }

        $name = preg_replace('/[^A-Za-z0-9._-]/', '_', basename((string)$asset['original_name'])) ?: 'attachment';
        header('Content-Type: ' . $asset['mime_type']);
        header('Content-Length: ' . filesize($path));
        header('Content-Disposition: attachment; filename="' . $name . '"');
        header('Cache-Control: private, no-store');
        header('X-Content-Type-Options: nosniff');
        readfile($path);
        exit;
    }

    if ($messageId === '') json_response(422, ['success' => false, 'message' => 'message_id is required.']);

    $messageStmt = $db->prepare(
        'SELECT m.is_internal, t.opened_by_type, t.opened_by_id
         FROM support_messages m
         JOIN support_tickets t ON t.id = m.ticket_id
         WHERE m.id = :message LIMIT 1'
    );
    $messageStmt->execute(['message' => $messageId]);
    $message = $messageStmt->fetch();
    if (!$message) json_response(404, ['success' => false, 'message' => 'Support message not found.']);
    if (!support_attachment_access($message, $principal, $isAdmin)) {
        json_response(403, ['success' => false, 'message' => 'You cannot access these attachments.']);
    }

    $stmt = $db->prepare(
        'SELECT f.id, f.original_name, f.mime_type, f.size_bytes, f.scan_status, f.created_at
         FROM support_attachments sa
         JOIN file_assets f ON f.id = sa.file_asset_id
         WHERE sa.message_id = :message AND f.deleted_at IS NULL
         ORDER BY f.created_at ASC'
    );
    $stmt->execute(['message' => $messageId]);
    $attachments = [];
    foreach ($stmt->fetchAll() as $file) {
        $item = [
            'id' => $file['id'],
            'name' => $file['original_name'],
            'mimeType' => $file['mime_type'],
            'sizeBytes' => (int)$file['size_bytes'],
            'scanStatus' => $file['scan_status'],
            'createdAt' => $file['created_at'],
        ];
        if ($file['scan_status'] === 'clean') {
            $item['downloadUrl'] = '../api/support-attachment.php?file_id=' . urlencode((string)$file['id']);
        }
        $attachments[] = $item;
    }

    json_response(200, ['success' => true, 'attachments' => $attachments]);
}

if ($method === 'POST') {
    $messageId = trim((string)($_POST['message_id'] ?? ''));
    if ($messageId === '') json_response(422, ['success' => false, 'message' => 'message_id is required.']);

    $messageStmt = $db->prepare(
        'SELECT m.sender_type, m.sender_id, m.is_internal,
                t.status, t.opened_by_type, t.opened_by_id
         FROM support_messages m
         JOIN support_tickets t ON t.id = m.ticket_id
         WHERE m.id = :message LIMIT 1'
    );
    $messageStmt->execute(['message' => $messageId]);
    $message = $messageStmt->fetch();
    if (!$message) json_response(404, ['success' => false, 'message' => 'Support message not found.']);
    if (!$isAdmin && (!support_attachment_access($message, $principal, false)
        || !hash_equals((string)$message['sender_type'], (string)$principal['type'])
        || !hash_equals((string)$message['sender_id'], (string)$principal['id']))) {
        json_response(403, ['success' => false, 'message' => 'You can attach files only to your own support message.']);
    }
    if (in_array((string)$message['status'], ['resolved', 'closed'], true)) {
        json_response(409, ['success' => false, 'message' => 'Attachments cannot be added to a closed ticket.']);
    }
    if (empty($_FILES['file']) || !is_array($_FILES['file'])) {
        json_response(422, ['success' => false, 'message' => 'A file is required.']);
    }

    $asset = store_private_upload(
        $_FILES['file'],
        $principal['type'],
        $principal['id'],
        'support_attachment',
        ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'],
        min(MAX_UPLOAD_BYTES, 10 * 1024 * 1024)
    );

    try {
        $db->beginTransaction();
        $lock = $db->prepare(
            'SELECT t.status
             FROM support_messages m
             JOIN support_tickets t ON t.id = m.ticket_id
             WHERE m.id = :message
             FOR UPDATE'
        );
        $lock->execute(['message' => $messageId]);
        $lockedStatus = $lock->fetchColumn();
        if ($lockedStatus === false) throw new RuntimeException('Support message no longer exists.');
        if (in_array((string)$lockedStatus, ['resolved', 'closed'], true)) {
            throw new DomainException('Attachments cannot be added to a closed ticket.');
        }

        $countStmt = $db->prepare('SELECT COUNT(*) FROM support_attachments WHERE message_id = :message');
        $countStmt->execute(['message' => $messageId]);
        if ((int)$countStmt->fetchColumn() >= 5) {
            throw new DomainException('A support message can have at most 5 attachments.');
        }

        $db->prepare(
            'INSERT INTO support_attachments (message_id, file_asset_id) VALUES (:message, :file)'
        )->execute(['message' => $messageId, 'file' => $asset['id']]);
        audit_event('support.attachment_uploaded', 'support_message', $messageId, ['file_asset_id' => $asset['id']]);
        $db->commit();
    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        $fileStmt = $db->prepare('SELECT storage_key FROM file_assets WHERE id = :id LIMIT 1');
        $fileStmt->execute(['id' => $asset['id']]);
        $storageKey = $fileStmt->fetchColumn();
        $db->prepare('DELETE FROM file_assets WHERE id = :id')->execute(['id' => $asset['id']]);
        if (is_string($storageKey)) {
            $storedPath = rtrim(PRIVATE_STORAGE_PATH, '/\\') . DIRECTORY_SEPARATOR
                . str_replace('/', DIRECTORY_SEPARATOR, $storageKey);
            if (is_file($storedPath)) @unlink($storedPath);
        }
        if ($e instanceof DomainException) {
            json_response(422, ['success' => false, 'message' => $e->getMessage()]);
        }
        throw $e;
    }

    json_response(201, [
        'success' => true,
        'message' => 'Attachment uploaded and queued for security scanning.',
        'attachment' => [
            'id' => $asset['id'],
            'scanStatus' => $asset['scan_status'],
        ],
    ]);
}

header('Allow: GET, POST');
json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
>>>>>>> origin/main
