<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/invoice-pdf.php';
require_once __DIR__ . '/../lib/credit-note.php';

if (ob_get_level()) ob_end_clean();

$payload = require_auth();
$userId  = auth_user_id($payload);
$db      = get_db();

$number = trim((string)($_GET['credit_note_number'] ?? ''));
$id     = trim((string)($_GET['id'] ?? ''));
$mode   = strtolower(trim((string)($_GET['mode'] ?? 'download')));
if (!in_array($mode, ['download','preview'], true)) $mode = 'download';

if ($number === '' && $id === '') {
    header('Content-Type: application/json'); http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Provide credit_note_number or id.']); exit;
}

if ($id !== '') {
    $stmt = $db->prepare('SELECT * FROM credit_notes WHERE id = ? AND user_id = ? LIMIT 1');
    $stmt->execute([$id, $userId]);
} else {
    $stmt = $db->prepare('SELECT * FROM credit_notes WHERE credit_note_number = ? AND user_id = ? LIMIT 1');
    $stmt->execute([$number, $userId]);
}
$row = $stmt->fetch();

if (!$row) {
    header('Content-Type: application/json'); http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Credit note not found.']); exit;
}

try {
    require_once __DIR__ . '/../lib/credit-note-pdf.php';
    $inv = credit_note_to_json($row);
    $pdf = credit_note_render_pdf($inv);

    $filename = str_replace(['/','\\'], '_', (string)$row['credit_note_number']) . '.pdf';
    header('Content-Type: application/pdf');
    header('Content-Disposition: ' . ($mode === 'preview' ? 'inline' : 'attachment') . '; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($pdf));
    header('Cache-Control: private, max-age=0, no-store');
    echo $pdf;
    exit;
} catch (Throwable $e) {
    error_log('Credit note PDF failed: ' . $e->getMessage());
    header('Content-Type: application/json'); http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to generate PDF.']); exit;
}