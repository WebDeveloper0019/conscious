<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/notifications.php';
require_once __DIR__ . '/../lib/audit.php';
require_once __DIR__ . '/../lib/idempotency.php';   // ← ADDED

send_cors_headers();

$principal = require_principal(['vendor']);
$vendorId  = $principal['id'];
$db        = get_db();
$method    = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// ────────── GET: questions on my products ──────────
if ($method === 'GET') {
    $page   = max(1, (int)($_GET['page']  ?? 1));
    $limit  = min(50, max(1, (int)($_GET['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;
    $status = strtolower(trim((string)($_GET['status'] ?? 'published')));
    $allowed = ['pending', 'published', 'rejected', 'hidden', 'all'];
    if (!in_array($status, $allowed, true)) $status = 'published';

    $where = 'p.vendor_id = ?';
    $params = [$vendorId];
    if ($status !== 'all') {
        $where .= ' AND q.status = ?';
        $params[] = $status;
    }

    $countStmt = $db->prepare(
        "SELECT COUNT(*) FROM product_questions q
         JOIN products p ON p.id = q.product_id
         WHERE $where"
    );
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();
    $countStmt->closeCursor();

    $stmt = $db->prepare(
        "SELECT q.*, p.name AS product_name,
                u.first_name, u.last_name
         FROM product_questions q
         JOIN products p ON p.id = q.product_id
         JOIN users u ON u.id = q.user_id
         WHERE $where
         ORDER BY q.status = 'pending' DESC, q.created_at DESC
         LIMIT $limit OFFSET $offset"
    );
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $out = [];
    foreach ($rows as $r) {
        $ansStmt = $db->prepare(
            'SELECT id, answer_text, answered_by_type, answered_by_id, is_official, status, created_at
             FROM product_answers WHERE question_id = ? ORDER BY created_at ASC'
        );
        $ansStmt->execute([$r['id']]);
        $answers = array_map(fn($a) => [
            'id'             => $a['id'],
            'answerText'     => $a['answer_text'],
            'answeredByType' => $a['answered_by_type'],
            'isMine'         => $a['answered_by_type'] === 'vendor' && $a['answered_by_id'] === $vendorId,
            'isOfficial'     => (bool)$a['is_official'],
            'status'         => $a['status'],
            'createdAt'      => $a['created_at'],
        ], $ansStmt->fetchAll());

        $out[] = [
            'id'             => $r['id'],
            'productId'      => $r['product_id'],
            'productName'    => $r['product_name'],
            'askedBy'        => trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')),
            'questionText'   => $r['question_text'],
            'status'         => $r['status'],
            'answerCount'    => count($answers),
            'answers'        => $answers,
            'createdAt'      => $r['created_at'],
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
}

// ────────── POST: answer a question ──────────
if ($method !== 'POST') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

require_csrf();

// Rate limit — NEW
if (rate_limit('vendor-answer:' . $vendorId, 30, 3600)) {
    json_response(429, ['success' => false, 'message' => 'Too many answers posted. Try again later.']);
}

$body       = get_json_body();
$questionId = trim((string)($body['question_id'] ?? ''));
$answerText = trim((string)($body['answer_text'] ?? ''));

if ($questionId === '') json_response(422, ['success' => false, 'message' => 'question_id required.']);
if (mb_strlen($answerText) < 10 || mb_strlen($answerText) > 1500) {
    json_response(422, ['success' => false, 'message' => 'Answer must be 10-1500 characters.']);
}

// Idempotency guard — NEW
$idem = begin_idempotent_request(
    'product_question.answer',
    ['type' => 'vendor', 'id' => $vendorId],
    ['question_id' => $questionId, 'answer_text' => $answerText]
);

// Verify the question belongs to my product
$chk = $db->prepare(
    'SELECT q.*, p.vendor_id FROM product_questions q
     JOIN products p ON p.id = q.product_id
     WHERE q.id = ? LIMIT 1'
);
$chk->execute([$questionId]);
$question = $chk->fetch();

if (!$question) json_response(404, ['success' => false, 'message' => 'Question not found.']);
if ((string)$question['vendor_id'] !== $vendorId) {
    json_response(403, ['success' => false, 'message' => 'You can only answer questions on your products.']);
}
if ($question['status'] === 'rejected') {
    json_response(409, ['success' => false, 'message' => 'This question was rejected by moderation.']);
}

try {
    $id = uuid_string();
    $db->prepare(
        'INSERT INTO product_answers
           (id, question_id, answered_by_type, answered_by_id, answer_text,
            is_official, status, created_at)
         VALUES (?, ?, "vendor", ?, ?, 1, "published", UTC_TIMESTAMP())'
    )->execute([$id, $questionId, $vendorId, $answerText]);

    // Update answer count on question
    $db->prepare(
        'UPDATE product_questions
         SET answer_count = (SELECT COUNT(*) FROM product_answers WHERE question_id = ? AND status = "published"),
             updated_at = UTC_TIMESTAMP()
         WHERE id = ?'
    )->execute([$questionId, $questionId]);

    // Notify the question asker
    create_notification(
        'customer',
        (string)$question['user_id'],
        'product_answer',
        'Your question was answered',
        'A seller answered your product question.',
        '/product-detail.html?id=' . urlencode((string)$question['product_id']),
        ['question_id' => $questionId, 'product_id' => $question['product_id']]
    );

    audit_event('vendor.product_question_answered', 'product_question', $questionId, [
        'answer_id' => $id,
    ]);

    $response = [
        'success'  => true,
        'message'  => 'Answer posted.',
        'answerId' => $id,
    ];

    finish_idempotent_request((int)$idem['id'], 201, $response);
    json_response(201, $response);

} catch (Throwable $e) {
    error_log('Vendor answer failed: ' . $e->getMessage());
    json_response(500, ['success' => false, 'message' => 'Unable to post answer.']);
}