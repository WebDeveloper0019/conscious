<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/audit.php';

send_cors_headers();
$principal = require_principal(['admin']);
$db = get_db();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// ─── GET: list all (including inactive) ───
if ($method === 'GET') {
    $scope = strtolower(trim((string)($_GET['scope'] ?? '')));
    $includeInactive = !empty($_GET['include_inactive']);

    $where = '1=1';
    $params = [];
    if ($scope !== '') {
        if (!in_array($scope, ['cancellation','return','return_replacement'], true)) {
            json_response(422, ['success' => false, 'message' => 'Invalid scope.']);
        }
        $where .= ' AND scope = ?';
        $params[] = $scope;
    }
    if (!$includeInactive) $where .= ' AND is_active = 1';

    $stmt = $db->prepare(
        "SELECT * FROM reason_master WHERE $where ORDER BY scope, sort_order, label"
    );
    $stmt->execute($params);
    json_response(200, ['success' => true, 'reasons' => $stmt->fetchAll()]);
}

// ─── POST: create ───
if ($method === 'POST') {
    require_csrf();
    $body = get_json_body();

    $scope  = strtolower(trim((string)($body['scope'] ?? '')));
    $code   = strtolower(trim((string)($body['code'] ?? '')));
    $label  = trim((string)($body['label'] ?? ''));
    $desc   = trim((string)($body['description'] ?? ''));
    $reqN   = !empty($body['requires_note'])     ? 1 : 0;
    $reqE   = !empty($body['requires_evidence']) ? 1 : 0;
    $sort   = (int)($body['sort_order'] ?? 100);

    if (!in_array($scope, ['cancellation','return','return_replacement'], true)) {
        json_response(422, ['success' => false, 'message' => 'Invalid scope.']);
    }
    if (!preg_match('/^[a-z0-9_]{3,40}$/', $code)) {
        json_response(422, ['success' => false, 'message' => 'Code must be 3-40 chars: a-z, 0-9, underscore.']);
    }
    if (mb_strlen($label) < 2 || mb_strlen($label) > 120) {
        json_response(422, ['success' => false, 'message' => 'Label must be 2-120 chars.']);
    }
    if (mb_strlen($desc) > 500) {
        json_response(422, ['success' => false, 'message' => 'Description must be ≤ 500 chars.']);
    }

    try {
        $id = uuid_string();
        $db->prepare(
            'INSERT INTO reason_master
               (id, scope, code, label, description, requires_note, requires_evidence,
                sort_order, is_active, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, UTC_TIMESTAMP(), UTC_TIMESTAMP())'
        )->execute([$id, $scope, $code, $label, $desc ?: null, $reqN, $reqE, $sort]);

        audit_event('admin.reason_created', 'reason_master', $id, ['scope'=>$scope, 'code'=>$code]);
        json_response(201, ['success' => true, 'id' => $id]);
    } catch (PDOException $e) {
        if ((string)$e->getCode() === '23000') {
            json_response(409, ['success' => false, 'message' => 'A reason with this code already exists for this scope.']);
        }
        throw $e;
    }
}

// ─── PUT: update ───
if ($method === 'PUT') {
    require_csrf();
    $body = get_json_body();
    $id = trim((string)($body['id'] ?? ''));
    if ($id === '') json_response(422, ['success' => false, 'message' => 'id required.']);

    $chk = $db->prepare('SELECT id FROM reason_master WHERE id = ? LIMIT 1');
    $chk->execute([$id]);
    if (!$chk->fetchColumn()) json_response(404, ['success' => false, 'message' => 'Reason not found.']);

    $fields = [];
    $params = [];
    if (isset($body['label']))            { $fields[] = 'label = ?';            $params[] = mb_substr(trim((string)$body['label']), 0, 120); }
    if (isset($body['description']))      { $fields[] = 'description = ?';      $params[] = mb_substr(trim((string)$body['description']), 0, 500); }
    if (isset($body['requires_note']))    { $fields[] = 'requires_note = ?';    $params[] = $body['requires_note'] ? 1 : 0; }
    if (isset($body['requires_evidence'])){ $fields[] = 'requires_evidence = ?';$params[] = $body['requires_evidence'] ? 1 : 0; }
    if (isset($body['sort_order']))       { $fields[] = 'sort_order = ?';       $params[] = (int)$body['sort_order']; }
    if (isset($body['is_active']))        { $fields[] = 'is_active = ?';        $params[] = $body['is_active'] ? 1 : 0; }

    if (!$fields) json_response(422, ['success' => false, 'message' => 'Nothing to update.']);

    $fields[] = 'updated_at = UTC_TIMESTAMP()';
    $params[] = $id;

    $db->prepare('UPDATE reason_master SET ' . implode(', ', $fields) . ' WHERE id = ?')->execute($params);

    audit_event('admin.reason_updated', 'reason_master', $id, ['fields'=>array_keys($body)]);
    json_response(200, ['success' => true]);
}

// ─── DELETE: soft (deactivate) ───
if ($method === 'DELETE') {
    require_csrf();
    $id = trim((string)($_GET['id'] ?? ''));
    if ($id === '') json_response(422, ['success' => false, 'message' => 'id required.']);

    $db->prepare('UPDATE reason_master SET is_active = 0, updated_at = UTC_TIMESTAMP() WHERE id = ?')->execute([$id]);
    audit_event('admin.reason_deactivated', 'reason_master', $id);
    json_response(200, ['success' => true, 'message' => 'Reason deactivated.']);
}

json_response(405, ['success' => false, 'message' => 'Method not allowed.']);