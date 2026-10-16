<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/notifications.php';
require_once __DIR__ . '/../lib/idempotency.php';

send_cors_headers();
$auth   = require_auth();
$userId = auth_user_id($auth);
$db     = get_db();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// ────────── GET: list return requests ──────────
if ($method === 'GET') {
    $stmt = $db->prepare(
        'SELECT r.*, oi.product_name, oi.image, o.order_number
         FROM return_requests r
         JOIN order_items oi ON oi.id = r.order_item_id
         JOIN orders      o  ON o.id  = r.order_id
         WHERE r.user_id = ?
         ORDER BY r.requested_at DESC'
    );
    $stmt->execute([$userId]);
    json_response(200, ['success' => true, 'returns' => $stmt->fetchAll()]);
}

// ────────── POST: create return request ──────────
if ($method !== 'POST') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

$body        = get_json_body();
$itemId      = (int)($body['order_item_id'] ?? 0);
$quantity    = (int)($body['quantity'] ?? 0);
$type        = trim((string)($body['request_type'] ?? ''));
$reason      = trim((string)($body['reason_code'] ?? ''));
$description = trim((string)($body['description'] ?? ''));

if (!in_array($type, ['return', 'replacement'], true)
    || $quantity < 1
    || mb_strlen($reason) < 2
    || mb_strlen($description) < 20
    || mb_strlen($description) > 1200) {
    json_response(422, [
        'success' => false,
        'message' => 'Return type, quantity, reason and a detailed description (20-1200 chars) are required.',
    ]);
}

// Validate evidence file IDs if provided
$evidenceFileIds = [];
if (!empty($body['evidence_file_ids']) && is_array($body['evidence_file_ids'])) {
    $evidenceFileIds = array_slice(
        array_values(array_filter($body['evidence_file_ids'], 'is_string')),
        0,
        8
    );
}

$idem = begin_idempotent_request('return.create', ['type' => 'customer', 'id' => $userId], $body);

$db->beginTransaction();

try {
    // ── Lock and validate the order item ──
    $stmt = $db->prepare(
        'SELECT oi.*, o.user_id, o.order_number,
                g.status AS group_status, g.policy_snapshot_json
         FROM order_items oi
         JOIN orders o ON o.id = oi.order_id
         JOIN vendor_order_groups g ON g.id = oi.vendor_group_id
         WHERE oi.id = ? AND o.user_id = ?
         FOR UPDATE'
    );
    $stmt->execute([$itemId, $userId]);
    $item = $stmt->fetch();

<<<<<<< HEAD
    if (!$item) {
        json_response(404, ['success' => false, 'message' => 'Order item not found.']);
    }

    if ($item['group_status'] !== 'delivered' && $item['status'] !== 'delivered') {
        json_response(409, ['success' => false, 'message' => 'A return can be requested only after delivery.']);
=======
    if (!$item) throw new DomainException('ITEM_NOT_FOUND');

    if ($item['group_status'] !== 'delivered' && $item['status'] !== 'delivered') {
        throw new DomainException('NOT_DELIVERED');
>>>>>>> origin/main
    }

    $openStmt = $db->prepare(
        'SELECT COALESCE(SUM(quantity), 0) FROM return_requests
         WHERE order_item_id = ? AND status NOT IN ("rejected","cancelled")'
    );
    $openStmt->execute([$itemId]);
    $openQty = (int)$openStmt->fetchColumn();

<<<<<<< HEAD
    if ($quantity > (int)$item['quantity'] - $openQty) {
        json_response(422, ['success' => false, 'message' => 'Requested quantity exceeds the eligible quantity.']);
    }
=======
    $eligibleQty = (int)$item['quantity'] - (int)$item['cancelled_quantity'] - (int)$item['returned_quantity'] - $openQty;
    if ($quantity > $eligibleQty) throw new DomainException('QUANTITY_EXCEEDED');
>>>>>>> origin/main

    // ── Insert the return request ──
    $returnId = uuid_string();
    $db->prepare(
        'INSERT INTO return_requests
           (id, order_id, order_item_id, user_id, vendor_id,
            request_type, quantity, reason_code, description, status,
            policy_snapshot_json, requested_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, "requested", ?, UTC_TIMESTAMP(), UTC_TIMESTAMP())'
    )->execute([
        $returnId,
        (int)$item['order_id'],
        $itemId,
        $userId,
        (string)$item['vendor_id'],
        $type,
        $quantity,
        $reason,
        $description,
        $item['policy_snapshot_json'] ?: '{}',
    ]);

    // ── Attach evidence files ──
    $attachedCount = 0;
    if (!empty($evidenceFileIds)) {
        // Verify each file belongs to this user
        $placeholders = implode(',', array_fill(0, count($evidenceFileIds), '?'));
        $chk = $db->prepare(
            "SELECT id FROM file_assets
             WHERE id IN ($placeholders)
<<<<<<< HEAD
               AND uploaded_by_id = ?
=======
               AND owner_type = 'customer' AND owner_id = ?
               AND mime_type IN ('image/jpeg','image/png','image/webp','image/gif')
>>>>>>> origin/main
               AND deleted_at IS NULL
               AND scan_status = 'clean'"
        );
        $chk->execute(array_merge($evidenceFileIds, [$userId]));
        $validIds = array_column($chk->fetchAll(), 'id');

        $sortOrder = 0;
        foreach ($validIds as $fid) {
            try {
                $db->prepare(
                    'INSERT INTO return_evidence
                       (id, return_request_id, file_asset_id, uploaded_by_type, uploaded_by_id, sort_order, created_at)
                     VALUES (?, ?, ?, "customer", ?, ?, UTC_TIMESTAMP())'
                )->execute([uuid_string(), $returnId, $fid, $userId, $sortOrder++]);
                $attachedCount++;
            } catch (PDOException $e) {
                if ((string)$e->getCode() !== '23000') throw $e;
                // Duplicate — skip
            }
        }
    }

    // ── Notifications + audit ──
    create_notification(
        'vendor',
        (string)$item['vendor_id'],
        'return_requested',
        'Return request received',
        "A {$type} request was opened for {$item['product_name']}."
            . ($attachedCount > 0 ? " {$attachedCount} photo(s) attached." : ''),
        '../returns.php',
        ['return_id' => $returnId, 'evidence_count' => $attachedCount]
    );

    record_status_change('return_request', $returnId, null, 'requested', $description);
    audit_event('customer.return_requested', 'return_request', $returnId, [
        'order_item_id'  => $itemId,
        'quantity'       => $quantity,
        'evidence_count' => $attachedCount,
    ]);

    $response = [
        'success'        => true,
        'message'        => 'Request submitted for review.',
        'return_id'      => $returnId,
        'status'         => 'requested',
        'evidenceCount'  => $attachedCount,
    ];

    finish_idempotent_request((int)$idem['id'], 201, $response);
    $db->commit();
    json_response(201, $response);

<<<<<<< HEAD
} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    throw $e;
}
=======
} catch (DomainException $e) {
    if ($db->inTransaction()) $db->rollBack();
    $messages = [
        'ITEM_NOT_FOUND' => [404, 'Order item not found.'],
        'NOT_DELIVERED' => [409, 'A return can be requested only after delivery.'],
        'QUANTITY_EXCEEDED' => [422, 'Requested quantity exceeds the eligible quantity.'],
    ];
    [$status, $message] = $messages[$e->getMessage()] ?? [409, 'Return request cannot be created.'];
    json_response($status, ['success' => false, 'message' => $message]);
} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    throw $e;
}
>>>>>>> origin/main
