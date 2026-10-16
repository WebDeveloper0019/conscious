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

$editWindowDays = 30;

// ────────── GET: list own reviews ──────────
if ($method === 'GET') {
    $page   = max(1, (int)($_GET['page']  ?? 1));
    $limit  = min(50, max(1, (int)($_GET['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;

    $countStmt = $db->prepare('SELECT COUNT(*) FROM product_reviews WHERE user_id = ?');
    $countStmt->execute([$userId]);
    $total = (int)$countStmt->fetchColumn();
    $countStmt->closeCursor();

    $stmt = $db->prepare(
        "SELECT r.*, oi.product_name, oi.image AS product_image, o.order_number
         FROM product_reviews r
         LEFT JOIN order_items oi ON oi.id = r.order_item_id
         LEFT JOIN orders o ON o.id = oi.order_id
         WHERE r.user_id = ?
         ORDER BY r.created_at DESC, r.id DESC
         LIMIT $limit OFFSET $offset"
    );
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll();

    $out = [];
    foreach ($rows as $r) {
        $createdTs = strtotime((string)$r['created_at']);
        $ageDays = (time() - $createdTs) / 86400;
        $editable = $ageDays <= $editWindowDays
            && !in_array(strtolower((string)($r['status'] ?? '')), ['rejected', 'hidden', 'removed'], true);

        $out[] = [
            'id'           => $r['id'],
            'productId'    => $r['product_id'] ?? null,
            'productName'  => $r['product_name'] ?? null,
            'productImage' => $r['product_image'] ?? null,
            'orderItemId'  => (int)($r['order_item_id'] ?? 0),
            'orderNumber'  => $r['order_number'] ?? null,
            'rating'       => (int)$r['rating'],
            'title'        => $r['title'] ?? null,
            'comment'      => $r['review_text'] ?? null,
            'status'       => $r['status'] ?? 'published',
            'vendorReply'  => $r['vendor_reply'] ?? null,
            'repliedAt'    => $r['replied_at'] ?? null,
            'editable'     => $editable,
            'editUntil'    => date(DATE_ATOM, $createdTs + $editWindowDays * 86400),
            'createdAt'    => $r['created_at'],
            'updatedAt'    => $r['updated_at'] ?? null,
        ];
    }

    $totalPages = $total > 0 ? (int)ceil($total / $limit) : 0;
    json_response(200, [
        'success' => true,
        'reviews' => $out,
        'editWindowDays' => $editWindowDays,
        'pagination' => [
            'page' => $page, 'limit' => $limit, 'total' => $total,
            'totalPages' => $totalPages,
            'hasNext' => $page < $totalPages, 'hasPrev' => $page > 1,
        ],
    ]);
}

if (!in_array($method, ['POST', 'PUT', 'DELETE'], true)) {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

require_csrf();

// ────────── POST: create ──────────
if ($method === 'POST') {
    $body     = get_json_body();
    $itemId   = (int)($body['order_item_id'] ?? 0);
    $rating   = (int)($body['rating'] ?? 0);
    $title    = trim((string)($body['title'] ?? ''));
<<<<<<< HEAD
    $comment  = trim((string)($body['comment'] ?? ''));
=======
    $comment  = trim((string)($body['comment'] ?? $body['review_text'] ?? ''));
>>>>>>> origin/main

    if ($rating < 1 || $rating > 5) json_response(422, ['success' => false, 'message' => 'Rating must be 1-5.']);
    if (mb_strlen($comment) < 10 || mb_strlen($comment) > 2000) {
        json_response(422, ['success' => false, 'message' => 'Review must be 10-2000 chars.']);
    }
    if (mb_strlen($title) > 180) json_response(422, ['success' => false, 'message' => 'Title too long.']);

    // Verify item belongs to user's delivered order
    $chk = $db->prepare(
        'SELECT oi.id, oi.product_id, oi.vendor_id, o.status AS order_status
         FROM order_items oi
         JOIN orders o ON o.id = oi.order_id
         WHERE oi.id = ? AND o.user_id = ?
         LIMIT 1'
    );
    $chk->execute([$itemId, $userId]);
    $item = $chk->fetch();
    if (!$item) json_response(404, ['success' => false, 'message' => 'Order item not found.']);

    if (strtolower((string)$item['order_status']) !== 'delivered') {
        json_response(409, ['success' => false, 'message' => 'You can review only delivered orders.']);
    }

    // Duplicate check
    $dup = $db->prepare('SELECT id FROM product_reviews WHERE user_id = ? AND order_item_id = ? LIMIT 1');
    $dup->execute([$userId, $itemId]);
    if ($dup->fetchColumn()) {
        json_response(409, ['success' => false, 'message' => 'You already reviewed this item. Edit your review instead.']);
    }

    $id = uuid_string();
    try {
        $db->prepare(
            'INSERT INTO product_reviews
               (id, user_id, order_item_id, product_id, vendor_id, rating, title,
                review_text, status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, "published", UTC_TIMESTAMP())'
        )->execute([
            $id,
            $userId,
            $itemId,
            $item['product_id'],
            $item['vendor_id'],
            $rating,
            $title,
            $comment,
        ]);

        audit_event('customer.review_created', 'product_review', $id, ['rating' => $rating]);
        json_response(201, ['success' => true, 'message' => 'Review submitted.', 'reviewId' => $id]);
    } catch (PDOException $e) {
        if ((string)$e->getCode() === '23000') {
            json_response(409, ['success' => false, 'message' => 'You already reviewed this item.']);
        }
        throw $e;
    }
}

// ────────── PUT: edit ──────────
if ($method === 'PUT') {
    $body    = get_json_body();
    $id      = trim((string)($body['id'] ?? ''));
    $rating  = isset($body['rating'])  ? (int)$body['rating'] : null;
    $title   = isset($body['title'])   ? trim((string)$body['title']) : null;
<<<<<<< HEAD
    $comment = isset($body['comment']) ? trim((string)$body['comment']) : null;
=======
    $comment = isset($body['comment'])
        ? trim((string)$body['comment'])
        : (isset($body['review_text']) ? trim((string)$body['review_text']) : null);
>>>>>>> origin/main

    if ($id === '') json_response(422, ['success' => false, 'message' => 'id required.']);

    $stmt = $db->prepare('SELECT * FROM product_reviews WHERE id = ? AND user_id = ? LIMIT 1');
    $stmt->execute([$id, $userId]);
    $review = $stmt->fetch();
    if (!$review) json_response(404, ['success' => false, 'message' => 'Review not found.']);

    if (in_array(strtolower((string)($review['status'] ?? '')), ['rejected', 'hidden', 'removed'], true)) {
        json_response(403, ['success' => false, 'message' => 'This review cannot be edited.']);
    }

    $ageDays = (time() - strtotime((string)$review['created_at'])) / 86400;
    if ($ageDays > $editWindowDays) {
        json_response(403, ['success' => false, 'message' => "Reviews can only be edited within {$editWindowDays} days."]);
    }

    if ($rating !== null && ($rating < 1 || $rating > 5)) {
        json_response(422, ['success' => false, 'message' => 'Rating must be 1-5.']);
    }
    if ($comment !== null && (mb_strlen($comment) < 10 || mb_strlen($comment) > 2000)) {
        json_response(422, ['success' => false, 'message' => 'Review must be 10-2000 chars.']);
    }
    if ($title !== null && mb_strlen($title) > 180) {
        json_response(422, ['success' => false, 'message' => 'Title too long.']);
    }

    $fields = [];
    $params = [];
    if ($rating  !== null) { $fields[] = 'rating = ?';      $params[] = $rating; }
    if ($title   !== null) { $fields[] = 'title = ?';       $params[] = $title; }
    if ($comment !== null) { $fields[] = 'review_text = ?'; $params[] = $comment; }

    if (!$fields) json_response(422, ['success' => false, 'message' => 'Nothing to update.']);

    $fields[] = 'updated_at = UTC_TIMESTAMP()';
    $params[] = $id;

    $db->prepare('UPDATE product_reviews SET ' . implode(', ', $fields) . ' WHERE id = ?')->execute($params);

    audit_event('customer.review_updated', 'product_review', $id, ['fields' => array_keys($body)]);
    json_response(200, ['success' => true, 'message' => 'Review updated.']);
}

// ────────── DELETE ──────────
if ($method === 'DELETE') {
    $id = trim((string)($_GET['id'] ?? ''));
    if ($id === '') json_response(422, ['success' => false, 'message' => 'id required.']);

    $stmt = $db->prepare('SELECT id FROM product_reviews WHERE id = ? AND user_id = ? LIMIT 1');
    $stmt->execute([$id, $userId]);
    if (!$stmt->fetchColumn()) json_response(404, ['success' => false, 'message' => 'Review not found.']);

    $db->prepare('DELETE FROM product_reviews WHERE id = ? AND user_id = ?')->execute([$id, $userId]);

    audit_event('customer.review_deleted', 'product_review', $id);
    json_response(200, ['success' => true, 'message' => 'Review deleted.']);
<<<<<<< HEAD
}
=======
}
>>>>>>> origin/main
