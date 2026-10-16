<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/commerce.php';

send_cors_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

$payload = require_auth();
$userId  = auth_user_id($payload);
require_csrf();

if (rate_limit('reorder:' . $userId, 10, 300)) {
    json_response(429, ['success' => false, 'message' => 'Too many reorder attempts. Please slow down.']);
}

$body        = get_json_body();
$orderId     = (int)($body['order_id'] ?? 0);
$orderNumber = trim((string)($body['order_number'] ?? ''));

if ($orderId <= 0 && $orderNumber === '') {
    json_response(422, ['success' => false, 'message' => 'Provide order_id or order_number.']);
}

$db = get_db();

// Load order
if ($orderId > 0) {
    $oStmt = $db->prepare('SELECT id, order_number, status FROM orders WHERE id = ? AND user_id = ? LIMIT 1');
    $oStmt->execute([$orderId, $userId]);
} else {
    $oStmt = $db->prepare('SELECT id, order_number, status FROM orders WHERE order_number = ? AND user_id = ? LIMIT 1');
    $oStmt->execute([$orderNumber, $userId]);
}
$order = $oStmt->fetch();
if (!$order) json_response(404, ['success' => false, 'message' => 'Order not found.']);

// Only certain statuses make sense to reorder
$allowedStatuses = ['delivered', 'completed', 'cancelled', 'partially_cancelled', 'completed_with_exceptions', 'returned'];
if (!in_array(strtolower((string)$order['status']), $allowedStatuses, true)) {
    json_response(409, ['success' => false, 'message' => 'This order is still in progress. Cannot reorder yet.']);
}

// Load items from the original order
$iStmt = $db->prepare(
    'SELECT oi.variant_id, oi.product_id, oi.product_name, oi.quantity, oi.vendor_id
     FROM order_items oi
     WHERE oi.order_id = ?
     ORDER BY oi.id'
);
$iStmt->execute([$order['id']]);
$items = $iStmt->fetchAll();

if (!$items) json_response(409, ['success' => false, 'message' => 'No items found on this order.']);

// Get or create the active cart
$cart = active_cart($userId);
$cartId = (string)$cart['id'];

$db->beginTransaction();
try {
    $added   = [];
    $skipped = [];

    foreach ($items as $item) {
        $variantId = (string)$item['variant_id'];
        if ($variantId === '') {
            $skipped[] = [
                'productName' => $item['product_name'],
                'reason'      => 'Variant no longer tracked',
            ];
            continue;
        }

        // Check variant + product + vendor + stock
        $vStmt = $db->prepare(
<<<<<<< HEAD
            'SELECT pv.id AS variant_id, pv.price_adjustment,
=======
            'SELECT pv.id AS variant_id, pv.price_adjustment, pv.is_active AS variant_active,
>>>>>>> origin/main
                    p.id AS product_id, p.name, p.status AS product_status,
                    p.vendor_id, p.selling_price, p.gst_percent,
                    i.stock_quantity, i.reserved_quantity,
                    (i.stock_quantity - i.reserved_quantity) AS available_quantity,
                    v.status AS vendor_status
             FROM product_variants pv
             JOIN products p ON p.id = pv.product_id
             JOIN vendors  v ON v.id = p.vendor_id
             LEFT JOIN inventory_levels i ON i.variant_id = pv.id
             WHERE pv.id = ? LIMIT 1'
        );
        $vStmt->execute([$variantId]);
        $v = $vStmt->fetch();

        if (!$v) {
            $skipped[] = [
                'productName' => $item['product_name'],
                'reason'      => 'Product no longer exists',
            ];
            continue;
        }

<<<<<<< HEAD
        if (strtolower((string)$v['product_status']) !== 'approved') {
=======
        if (!(int)$v['variant_active'] || !in_array(strtolower((string)$v['product_status']), ['approved', 'active'], true)) {
>>>>>>> origin/main
            $skipped[] = [
                'productName' => $item['product_name'],
                'reason'      => 'Product is no longer available',
            ];
            continue;
        }

        if (strtolower((string)$v['vendor_status']) !== 'active') {
            $skipped[] = [
                'productName' => $item['product_name'],
                'reason'      => 'Vendor is no longer active',
            ];
            continue;
        }

        $available = (int)($v['available_quantity'] ?? 0);
        $wanted    = (int)$item['quantity'];
        $addQty    = min($wanted, $available);

        if ($addQty <= 0) {
            $skipped[] = [
                'productName' => $item['product_name'],
                'reason'      => 'Out of stock',
            ];
            continue;
        }

        // Existing cart line?
        $existing = $db->prepare(
            'SELECT id, quantity FROM cart_items WHERE cart_id = ? AND variant_id = ? LIMIT 1 FOR UPDATE'
        );
        $existing->execute([$cartId, $variantId]);
        $row = $existing->fetch();

        if ($row) {
<<<<<<< HEAD
            // Cap at available stock
            $newQty = min((int)$row['quantity'] + $addQty, $available);
            $db->prepare('UPDATE cart_items SET quantity = ? WHERE id = ?')->execute([$newQty, $row['id']]);
        } else {
            $db->prepare(
                'INSERT INTO cart_items (id, cart_id, variant_id, quantity, created_at)
                 VALUES (?, ?, ?, ?, UTC_TIMESTAMP())'
=======
            // Match the normal cart limit and report only the quantity actually added.
            $oldQty = (int)$row['quantity'];
            $newQty = min($oldQty + $addQty, $available, 20);
            $addQty = max(0, $newQty - $oldQty);
            if ($addQty === 0) {
                $skipped[] = ['productName' => $item['product_name'], 'reason' => 'Cart quantity is already at its limit'];
                continue;
            }
            $db->prepare('UPDATE cart_items SET quantity = ?, updated_at = UTC_TIMESTAMP() WHERE id = ?')->execute([$newQty, $row['id']]);
        } else {
            $addQty = min($addQty, 20);
            $db->prepare(
                'INSERT INTO cart_items (id, cart_id, variant_id, quantity, created_at, updated_at)
                 VALUES (?, ?, ?, ?, UTC_TIMESTAMP(), UTC_TIMESTAMP())'
>>>>>>> origin/main
            )->execute([uuid_string(), $cartId, $variantId, $addQty]);
        }

        $added[] = [
            'productName' => (string)$v['name'],
            'variantId'   => $variantId,
            'quantity'    => $addQty,
            'requested'   => $wanted,
        ];

        // If we couldn't fulfil the full requested quantity, note the shortfall
        if ($addQty < $wanted) {
            $skipped[] = [
                'productName' => (string)$v['name'],
                'reason'      => "Only {$addQty} of {$wanted} available — added {$addQty}",
                'partial'     => true,
            ];
        }
    }

    // Update cart activity
    $db->prepare('UPDATE carts SET updated_at = UTC_TIMESTAMP() WHERE id = ?')->execute([$cartId]);

    $db->commit();

    $response = [
        'success'     => true,
        'message'     => count($added) > 0
            ? 'Added to cart.'
            : 'No items could be added.',
        'orderNumber' => $order['order_number'],
        'cartId'      => $cartId,
        'added'       => $added,
        'skipped'     => $skipped,
        'summary'     => [
            'totalItems'  => count($items),
            'addedCount'  => count($added),
            'skippedCount'=> count(array_filter($skipped, fn($s) => empty($s['partial']))),
            'partialCount'=> count(array_filter($skipped, fn($s) => !empty($s['partial']))),
        ],
    ];

    json_response(200, $response);

} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    error_log('Reorder failed: ' . $e->getMessage());
    json_response(500, ['success' => false, 'message' => 'Unable to reorder. Please try again.']);
<<<<<<< HEAD
}
=======
}
>>>>>>> origin/main
