<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/audit.php';
require_once __DIR__ . '/../lib/notifications.php';

send_cors_headers();

$payload = require_auth();
$userId  = auth_user_id($payload);
$db      = get_db();
$method  = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// ────────── GET: my questions ──────────
if ($method === 'GET') {
    $stmt = $db->prepare(
        'SELECT q.*, p.name AS product_name,
                (SELECT COUNT(*) FROM product_answers a
                 WHERE a.question_id = q.id AND a.status = "published") AS answer_count
         FROM product_questions q
         LEFT JOIN products p ON p.id = q.product_id
         WHERE q.user_id = ?
         ORDER BY q.created_at DESC
         LIMIT 100'
    );
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll();

    $out = [];
    foreach ($rows as $r) {
        $ansStmt = $db->prepare(
            'SELECT a.*, 
                    CASE a.answered_by_type
                      WHEN "vendor" THEN (SELECT display_name FROM vendors WHERE id = a.answered_by_id)
                      WHEN "admin"  THEN "Conscious Indi Support"
                      ELSE "Unknown"
                    END AS answered_by_name
             FROM product_answers a
             WHERE a.question_id = ? AND a.status = "published"
             ORDER BY a.created_at ASC'
        );
        $ansStmt->execute([$r['id']]);
        $answers = $ansStmt->fetchAll();

        $out[] = [
            'id'           => $r['id'],
            'productId'    => $r['product_id'],
            'productName'  => $r['product_name'],
            'questionText' => $r['question_text'],
            'status'       => $r['status'],
            'moderationNote' => $r['moderation_note'],
            'answerCount'  => (int)$r['answer_count'],
            'helpfulCount' => (int)$r['helpful_count'],
            'createdAt'    => $r['created_at'],
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

    json_response(200, ['success' => true, 'questions' => $out]);
}

// ────────── POST: ask a question ──────────
if ($method !== 'POST') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

require_csrf();

if (rate_limit('product-q:' . $userId, 10, 3600)) {
    json_response(429, ['success' => false, 'message' => 'Too many questions. Please try again later.']);
}

$body        = get_json_body();
$productId   = trim((string)($body['product_id'] ?? ''));
$questionText = trim((string)($body['question_text'] ?? ''));

if ($productId === '') json_response(422, ['success' => false, 'message' => 'product_id required.']);
if (mb_strlen($questionText) < 10 || mb_strlen($questionText) > 500) {
    json_response(422, ['success' => false, 'message' => 'Question must be 10-500 characters.']);
}

// Product must exist
$pStmt = $db->prepare('SELECT id, name, vendor_id FROM products WHERE id = ? LIMIT 1');
$pStmt->execute([$productId]);
$product = $pStmt->fetch();
if (!$product) json_response(404, ['success' => false, 'message' => 'Product not found.']);

// Duplicate check — same user, same product, same question within 24h
$dup = $db->prepare(
    'SELECT id FROM product_questions
     WHERE user_id = ? AND product_id = ?
       AND created_at > DATE_SUB(UTC_TIMESTAMP(), INTERVAL 24 HOUR)
     LIMIT 1'
);
$dup->execute([$userId, $productId]);
if ($dup->fetchColumn()) {
    json_response(429, ['success' => false, 'message' => 'You recently asked a question on this product.']);
}

try {
    $id = uuid_string();
    $db->prepare(
        'INSERT INTO product_questions
           (id, product_id, user_id, question_text, status, created_at)
         VALUES (?, ?, ?, ?, "pending", UTC_TIMESTAMP())'
    )->execute([$id, $productId, $userId, $questionText]);

    audit_event('customer.product_question_asked', 'product_question', $id, [
        'product_id' => $productId,
    ]);

    // Notify vendor
    if (!empty($product['vendor_id'])) {
        create_notification(
            'vendor',
            (string)$product['vendor_id'],
            'product_question',
            'New question on your product',
            'Someone asked about "' . $product['name'] . '". Answer to help buyers.',
            '../products.html',
            ['question_id' => $id, 'product_id' => $productId]
        );
    }

    json_response(201, [
        'success'    => true,
        'message'    => 'Question submitted. It will appear once approved.',
        'questionId' => $id,
        'status'     => 'pending',
    ]);
} catch (Throwable $e) {
    error_log('Question create failed: ' . $e->getMessage());
    json_response(500, ['success' => false, 'message' => 'Unable to submit question.']);
}