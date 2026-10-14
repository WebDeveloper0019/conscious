<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';

send_cors_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

$scope = strtolower(trim((string)($_GET['scope'] ?? '')));
$allowed = ['cancellation', 'return', 'return_replacement'];

if ($scope !== '' && !in_array($scope, $allowed, true)) {
    json_response(422, ['success' => false, 'message' => 'Invalid scope.']);
}

$db = get_db();

if ($scope !== '') {
    $stmt = $db->prepare(
        'SELECT id, scope, code, label, description, requires_note, requires_evidence, sort_order
         FROM reason_master
         WHERE scope = ? AND is_active = 1
         ORDER BY sort_order ASC, label ASC'
    );
    $stmt->execute([$scope]);
    $rows = $stmt->fetchAll();
} else {
    $stmt = $db->query(
        'SELECT id, scope, code, label, description, requires_note, requires_evidence, sort_order
         FROM reason_master
         WHERE is_active = 1
         ORDER BY scope ASC, sort_order ASC, label ASC'
    );
    $rows = $stmt->fetchAll();
}

if ($scope === '') {
    $grouped = ['cancellation' => [], 'return' => [], 'return_replacement' => []];
    foreach ($rows as $r) {
        $grouped[$r['scope']][] = [
            'id'               => $r['id'],
            'code'             => $r['code'],
            'label'            => $r['label'],
            'description'      => $r['description'],
            'requiresNote'     => (bool)$r['requires_note'],
            'requiresEvidence' => (bool)$r['requires_evidence'],
        ];
    }
    json_response(200, ['success' => true, 'reasons' => $grouped]);
}

$out = [];
foreach ($rows as $r) {
    $out[] = [
        'id'               => $r['id'],
        'code'             => $r['code'],
        'label'            => $r['label'],
        'description'      => $r['description'],
        'requiresNote'     => (bool)$r['requires_note'],
        'requiresEvidence' => (bool)$r['requires_evidence'],
    ];
}
json_response(200, ['success' => true, 'scope' => $scope, 'reasons' => $out]);