<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/credit-note.php';

send_cors_headers();
$payload = require_auth();
$userId  = auth_user_id($payload);
$db      = get_db();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_response(405, ['success' => false, 'message' => 'Method not allowed.']);

$number = trim((string)($_GET['credit_note_number'] ?? ''));
$id     = trim((string)($_GET['id'] ?? ''));

if ($number === '' && $id === '') json_response(422, ['success' => false, 'message' => 'Provide credit_note_number or id.']);

if ($id !== '') {
    $stmt = $db->prepare('SELECT * FROM credit_notes WHERE id = ? AND user_id = ? LIMIT 1');
    $stmt->execute([$id, $userId]);
} else {
    $stmt = $db->prepare('SELECT * FROM credit_notes WHERE credit_note_number = ? AND user_id = ? LIMIT 1');
    $stmt->execute([$number, $userId]);
}
$row = $stmt->fetch();
if (!$row) json_response(404, ['success' => false, 'message' => 'Credit note not found.']);

json_response(200, ['success' => true, 'creditNote' => credit_note_to_json($row)]);