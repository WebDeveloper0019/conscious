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

// Summary
$sum = $db->prepare(
    'SELECT COUNT(*) AS total,
            AVG(rating) AS avg_rating,
            SUM(rating = 5) AS r5, SUM(rating = 4) AS r4,
            SUM(rating = 3) AS r3, SUM(rating = 2) AS r2,
            SUM(rating = 1) AS r1
     FROM product_reviews
     WHERE product_id = ? AND status = "published"'
);
$sum->execute([$productId]);
$summary = $sum->fetch() ?: [];
$sum->closeCursor();

$total = (int)($summary['total'] ?? 0);

// Reviews
$stmt = $db->prepare(
    "SELECT r.id, r.rating, r.title, r.review_text, r.vendor_reply, r.replied_at,
            r.created_at, u.first_name, u.last_name
     FROM product_reviews r
     JOIN users u ON u.id = r.user_id
     WHERE r.product_id = ? AND r.status = 'published'
     ORDER BY r.created_at DESC
     LIMIT $limit OFFSET $offset"
);
$stmt->execute([$productId]);
$rows = $stmt->fetchAll();

$reviews = [];
foreach ($rows as $r) {
    $name = trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''));
    if ($name === '') $name = 'Customer';
    // Mask: show first name + initial
    $parts = explode(' ', $name);
    $display = $parts[0];
    if (count($parts) > 1) $display .= ' ' . strtoupper(substr($parts[1], 0, 1)) . '.';

    $reviews[] = [
        'id'          => $r['id'],
        'rating'      => (int)$r['rating'],
        'title'       => $r['title'],
        'comment'     => $r['review_text'],
        'authorName'  => $display,
        'vendorReply' => $r['vendor_reply'],
        'repliedAt'   => $r['replied_at'],
        'createdAt'   => $r['created_at'],
    ];
}

$totalPages = $total > 0 ? (int)ceil($total / $limit) : 0;

json_response(200, [
    'success' => true,
    'summary' => [
        'total'      => $total,
        'avgRating'  => $total > 0 ? round((float)$summary['avg_rating'], 2) : 0,
        'distribution' => [
            5 => (int)($summary['r5'] ?? 0),
            4 => (int)($summary['r4'] ?? 0),
            3 => (int)($summary['r3'] ?? 0),
            2 => (int)($summary['r2'] ?? 0),
            1 => (int)($summary['r1'] ?? 0),
        ],
    ],
    'reviews' => $reviews,
    'pagination' => [
        'page' => $page, 'limit' => $limit, 'total' => $total,
        'totalPages' => $totalPages,
        'hasNext' => $page < $totalPages, 'hasPrev' => $page > 1,
    ],
]);