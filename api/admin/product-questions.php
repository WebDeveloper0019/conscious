<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/admin.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/notifications.php';
require_once __DIR__ . '/../lib/audit.php';

send_cors_headers();

$context = require_admin('support.manage');
$adminId = $context['admin']['id'];
$db      = get_db();
$method  = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// ────────── GET: list for moderation ──────────
if ($method === 'GET') {
    $page   = max(1, (int)($_GET['page']  ?? 1));
    $limit  = min(100, max(1, (int)($_GET['limit'] ?? 30)));
    $offset = ($page - 1) * $limit;
    $status = strtolower(trim((string)($_GET['status'] ?? 'pending')));
    $allowed = ['pending', 'published', 'rejected', 'hidden', 'all'];
    if (!in_array($status, $allowed, true)) $status = 'pending';

    $where = '1=1';
    $params = [];
    if ($status !== 'all') {
        $where .= ' AND q.status = ?';
        $params[] = $status;
    }

    $countStmt = $db->prepare("SELECT COUNT(*) FROM product_questions q WHERE $where");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();
    $countStmt->closeCursor();

    $stmt = $db->prepare(
        "SELECT q.*, p.name AS product_name, p.vendor_id,
                u.first_name, u.last_name, u.email,
                (SELECT COUNT(*) FROM product_answers a WHERE a.question_id = q.id AND a.status = 'published') AS answer_count
         FROM product_questions q
         LEFT JOIN products p ON p.id = q.product_id
         LEFT JOIN users u ON u.id = q.user_id
         WHERE $where
         ORDER BY FIELD(q.status, 'pending', 'published', 'hidden', 'rejected'), q.created_at DESC
         LIMIT $limit OFFSET $offset"
    );
    $stmt->execute($params);

    $out = [];
    foreach ($stmt->fetchAll() as $r) {
        $out[] = [
            'id'           => $r['id'],
            'productId'    => $r['product_id'],
            'productName'  => $r['product_name'],
            'vendorId'     => $r['vendor_id'],
            'askedBy'      => trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')),
            'askerEmail'   => $r['email'],
            'questionText' => $r['question_text'],
            'status'       => $r['status'],
            'answerCount'  => (int)$r['answer_count'],
            'moderationNote' => $r['moderation_note'],
            'createdAt'    => $r['created_at'],
        ];
    }

    $totalPages = $total > 0 ? (int)ceil($total / $limit) : 0;

    json_response(200, [
        'success' => true,
        'questions' => $out,
        'pagination' => [
            'page' => $page, 'limit' => $limit, 'total' => $total,
            'totalPages' => $totalPages,
            'hasNext' => $page < $totalPages, 'hasPrev' => $page > 1,
        ],
    ]);
}

// ────────── POST: moderate or admin-answer ──────────
if ($method !== 'POST') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

require_csrf();

$body   = get_json_body();
$action = strtolower(trim((string)($body['action'] ?? '')));

// ─── Moderate ───
if (in_array($action, ['approve', 'reject', 'hide'], true)) {
    $id   = trim((string)($body['question_id'] ?? ''));
    $note = trim((string)($body['note'] ?? ''));

    if ($id === '') json_response(422, ['success' => false, 'message' => 'question_id required.']);
    if ($action !== 'approve' && mb_strlen($note) < 5) {
        json_response(422, ['success' => false, 'message' => 'Provide a moderation note (min 5 chars).']);
    }

    $nextStatus = ['approve' => 'published', 'reject' => 'rejected', 'hide' => 'hidden'][$action];

    $chk = $db->prepare('SELECT id, user_id, status FROM product_questions WHERE id = ? LIMIT 1');
    $chk->execute([$id]);
    $q = $chk->fetch();
    if (!$q) json_response(404, ['success' => false, 'message' => 'Question not found.']);

    $db->prepare(
        'UPDATE product_questions
         SET status = ?, moderated_by = ?, moderated_at = UTC_TIMESTAMP(),
             moderation_note = ?, updated_at = UTC_TIMESTAMP()
         WHERE id = ?'
    )->execute([$nextStatus, $adminId, $note ?: null, $id]);

    // Notify customer
    $msg = [
        'published' => 'Your question has been published.',
        'rejected'  => 'Your question was not approved.',
        'hidden'    => 'Your question is under review.',
    ][$nextStatus];

    create_notification(
        'customer',
        (string)$q['user_id'],
        'product_question_' . $nextStatus,
        'Question ' . $nextStatus,
        $msg . ($note ? ' Note: ' . $note : ''),
        null,
        ['question_id' => $id, 'status' => $nextStatus]
    );

    audit_event('admin.question_' . $action, 'product_question', $id, ['note' => $note]);

    json_response(200, ['success' => true, 'message' => 'Question ' . $nextStatus . '.', 'status' => $nextStatus]);
}

// ─── Admin answers on behalf of platform ───
if ($action === 'answer') {
    $questionId = trim((string)($body['question_id'] ?? ''));
    $answerText = trim((string)($body['answer_text'] ?? ''));

    if ($questionId === '') json_response(422, ['success' => false, 'message' => 'question_id required.']);
    if (mb_strlen($answerText) < 10 || mb_strlen($answerText) > 1500) {
        json_response(422, ['success' => false, 'message' => 'Answer must be 10-1500 characters.']);
    }

    $chk = $db->prepare('SELECT id, user_id, product_id FROM product_questions WHERE id = ? LIMIT 1');
    $chk->execute([$questionId]);
    $q = $chk->fetch();
    if (!$q) json_response(404, ['success' => false, 'message' => 'Question not found.']);

    $id = uuid_string();
    $db->prepare(
        'INSERT INTO product_answers
           (id, question_id, answered_by_type, answered_by_id, answer_text,
            is_official, status, created_at)
         VALUES (?, ?, "admin", ?, ?, 1, "published", UTC_TIMESTAMP())'
    )->execute([$id, $questionId, $adminId, $answerText]);

    $db->prepare(
        'UPDATE product_questions
         SET answer_count = (SELECT COUNT(*) FROM product_answers WHERE question_id = ? AND status = "published"),
             updated_at = UTC_TIMESTAMP()
         WHERE id = ?'
    )->execute([$questionId, $questionId]);

    create_notification(
        'customer',
        (string)$q['user_id'],
        'product_answer',
        'Your question was answered',
        'Conscious India Support answered your product question.',
        '/product-detail.html?id=' . urlencode((string)$q['product_id']),
        ['question_id' => $questionId]
    );

    audit_event('admin.product_question_answered', 'product_question', $questionId, ['answer_id' => $id]);

    json_response(201, ['success' => true, 'message' => 'Answer posted.', 'answerId' => $id]);
}

json_response(422, ['success' => false, 'message' => 'Invalid action. Use approve, reject, hide, or answer.']);