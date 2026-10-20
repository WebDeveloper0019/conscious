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

$ALLOWED_TYPES = ['back_in_stock', 'price_drop'];

// ────────── GET: list own subscriptions ──────────
if ($method === 'GET') {
    $status = strtolower(trim((string)($_GET['status'] ?? 'active')));
    $where  = 'user_id = ?';
    $params = [$userId];

    if (in_array($status, ['active','notified','cancelled','expired'], true)) {
        $where .= ' AND status = ?';
        $params[] = $status;
    } elseif ($status !== 'all') {
        $where .= ' AND status = "active"';
    }

    $stmt = $db->prepare(
        "SELECT s.*, p.name AS product_name, p.slug AS product_slug,
                p.selling_price AS current_price,
                p.status AS product_status,
                (SELECT CONCAT('../files.php?id=', f.id)
                 FROM product_images pi
                 JOIN file_assets f ON f.id = pi.file_asset_id
                 WHERE pi.product_id = p.id
                   AND f.visibility = 'public' AND f.scan_status = 'clean' AND f.deleted_at IS NULL
                 ORDER BY pi.is_primary DESC, pi.sort_order LIMIT 1) AS image_url
         FROM stock_price_subscriptions s
         JOIN products p ON p.id = s.product_id
         WHERE $where
         ORDER BY s.created_at DESC"
    );
    $stmt->execute($params);

    $rows = [];
    foreach ($stmt->fetchAll() as $r) {
        $currentPrice = (float)($r['current_price'] ?? 0);
        $original     = (float)($r['original_price'] ?? 0);
        $target       = $r['target_price'] !== null ? (float)$r['target_price'] : null;

        $rows[] = [
            'id'               => $r['id'],
            'subscriptionType' => $r['subscription_type'],
            'productId'        => $r['product_id'],
            'productName'      => $r['product_name'],
            'productSlug'      => $r['product_slug'],
            'imageUrl'         => $r['image_url'],
            'variantId'        => $r['variant_id'],
            'originalPrice'    => $original,
            'targetPrice'      => $target,
            'currentPrice'     => $currentPrice,
            'priceDropped'     => $original > 0 && $currentPrice < $original,
            'status'           => $r['status'],
            'notifyCount'      => (int)$r['notify_count'],
            'lastNotifiedAt'   => $r['last_notified_at'],
            'createdAt'        => $r['created_at'],
        ];
    }

    json_response(200, ['success' => true, 'subscriptions' => $rows]);
}

// ────────── POST: create subscription ──────────
if ($method === 'POST') {
    require_csrf();

    if (rate_limit('stock-subs:' . $userId, 30, 3600)) {
        json_response(429, ['success' => false, 'message' => 'Too many subscriptions. Try again later.']);
    }

    $body = get_json_body();
    $productId = trim((string)($body['product_id'] ?? ''));
    $variantId = trim((string)($body['variant_id'] ?? ''));
    $type      = strtolower(trim((string)($body['subscription_type'] ?? '')));
    $target    = isset($body['target_price']) ? (float)$body['target_price'] : null;

    if ($productId === '') json_response(422, ['success' => false, 'message' => 'product_id required.']);
    if (!in_array($type, $ALLOWED_TYPES, true)) {
        json_response(422, ['success' => false, 'message' => 'subscription_type must be back_in_stock or price_drop.']);
    }
    if ($type === 'price_drop' && ($target === null || $target <= 0)) {
        json_response(422, ['success' => false, 'message' => 'target_price required for price_drop.']);
    }

    $variantIdDb = $variantId !== '' ? $variantId : null;

    // Fetch current price for snapshot
    $pStmt = $db->prepare('SELECT id, name, selling_price, status FROM products WHERE id = ? LIMIT 1');
    $pStmt->execute([$productId]);
    $product = $pStmt->fetch();
    if (!$product) json_response(404, ['success' => false, 'message' => 'Product not found.']);

    $originalPrice = (float)$product['selling_price'];

    if ($type === 'price_drop' && $target >= $originalPrice) {
        json_response(422, ['success' => false, 'message' => 'Target price must be below current price.']);
    }

    // Upsert
    try {
        $id = uuid_string();
        $db->prepare(
            'INSERT INTO stock_price_subscriptions
               (id, user_id, product_id, variant_id, subscription_type,
                target_price, original_price, status, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, "active", UTC_TIMESTAMP(), UTC_TIMESTAMP())
             ON DUPLICATE KEY UPDATE
               target_price = VALUES(target_price),
               original_price = VALUES(original_price),
               status = "active",
               updated_at = UTC_TIMESTAMP()'
        )->execute([$id, $userId, $productId, $variantIdDb, $type, $target, $originalPrice]);

        audit_event('customer.stock_subscription_created', 'stock_subscription', $id, [
            'product_id' => $productId,
            'type'       => $type,
            'target'     => $target,
        ]);

        json_response(201, [
            'success' => true,
            'message' => $type === 'back_in_stock'
                ? "We'll notify you when it's back in stock."
                : "We'll notify you when the price drops.",
            'subscriptionId' => $id,
        ]);
    } catch (Throwable $e) {
        error_log('Subscription create failed: ' . $e->getMessage());
        json_response(500, ['success' => false, 'message' => 'Unable to save subscription.']);
    }
}

// ────────── DELETE: cancel subscription ──────────
if ($method === 'DELETE') {
    require_csrf();

    $id = trim((string)($_GET['id'] ?? ''));
    if ($id === '') json_response(422, ['success' => false, 'message' => 'id required.']);

    $stmt = $db->prepare(
        'UPDATE stock_price_subscriptions
         SET status = "cancelled", updated_at = UTC_TIMESTAMP()
         WHERE id = ? AND user_id = ? AND status = "active"'
    );
    $stmt->execute([$id, $userId]);

    if ($stmt->rowCount() === 0) {
        json_response(404, ['success' => false, 'message' => 'Subscription not found.']);
    }

    audit_event('customer.stock_subscription_cancelled', 'stock_subscription', $id);
    json_response(200, ['success' => true, 'message' => 'Subscription cancelled.']);
}

json_response(405, ['success' => false, 'message' => 'Method not allowed.']);