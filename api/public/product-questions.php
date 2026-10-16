<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';

send_cors_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

$productId = trim((string)($_GET['product_id'] ?? ''));
if ($productId === '') json_response(422, ['success' => false, 'message' => 'product_id required.']);

$page   = max(1, (int)($_GET['page']  ?? 1));
$limit  = min(50, max(1, (int)($_GET['limit'] ?? 10)));
$offset = ($page - 1) * $limit;

$db = get_db();

$countStmt = $db->prepare(
    'SELECT COUNT(*) FROM product_questions
     WHERE product_id = ? AND status = "published"'
);
$countStmt->execute([$productId]);
$total = (int)$countStmt->fetchColumn();
$countStmt->closeCursor();

$stmt = $db->prepare(
    "SELECT q.id, q.question_text, q.helpful_count, q.created_at,
            u.first_name, u.last_name
     FROM product_questions q
     JOIN users u ON u.id = q.user_id
     WHERE q.product_id = ? AND q.status = 'published'
     ORDER BY q.helpful_count DESC, q.created_at DESC
     LIMIT $limit OFFSET $offset"
);
$stmt->execute([$productId]);
$questions = $stmt->fetchAll();

$out = [];
foreach ($questions as $q) {
    $name = trim(($q['first_name'] ?? '') . ' ' . ($q['last_name'] ?? ''));
    $parts = explode(' ', $name);
    $displayName = $parts[0] ?? 'Customer';
    if (count($parts) > 1) $displayName .= ' ' . strtoupper(substr($parts[1], 0, 1)) . '.';

    $ansStmt = $db->prepare(
        'SELECT a.id, a.answer_text, a.answered_by_type, a.is_official,
                a.helpful_count, a.created_at,
                CASE a.answered_by_type
                  WHEN "vendor" THEN (SELECT display_name FROM vendors WHERE id = a.answered_by_id)
                  WHEN "admin"  THEN "Conscious Indi Support"
                  ELSE "Unknown"
                END AS answered_by_name
         FROM product_answers a
         WHERE a.question_id = ? AND a.status = "published"
         ORDER BY a.is_official DESC, a.created_at ASC'
    );
    $ansStmt->execute([$q['id']]);
    $answers = $ansStmt->fetchAll();

    $out[] = [
        'id'           => $q['id'],
        'questionText' => $q['question_text'],
        'askedBy'      => $displayName,
        'helpfulCount' => (int)$q['helpful_count'],
        'createdAt'    => $q['created_at'],
        'answers'      => array_map(fn($a) => [
            'id'             => $a['id'],
            'answerText'     => $a['answer_text'],
            'answeredByType' => $a['answered_by_type'],
            'answeredByName' => $a['answered_by_name'],
            'isOfficial'     => (bool)$a['is_official'],
            'helpfulCount'   => (int)$a['helpful_count'],
            'createdAt'      => $a['created_at'],
        ], $answers),
    ];
}

$totalPages = $total > 0 ? (int)ceil($total / $limit) : 0;

json_response(200, [
    'success'   => true,
    'questions' => $out,
    'pagination' => [
        'page' => $page, 'limit' => $limit, 'total' => $total,
        'totalPages' => $totalPages,
        'hasNext' => $page < $totalPages, 'hasPrev' => $page > 1,
    ],
]);