<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/rewards.php';
require_once __DIR__ . '/../lib/invoice.php';

function vendor_cod_collections_v2(): void
{
    send_cors_headers();
    $context = require_vendor(true);
    $vendorId = $context['vendor']['id'];
    $db = get_db();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'GET') {
        $groupId = trim((string)($_GET['group_id'] ?? ''));
        $sql = 'SELECT c.*,g.group_number,g.status AS group_status,o.order_number
                FROM cod_collections c
                JOIN vendor_order_groups g ON g.id=c.vendor_group_id
                JOIN orders o ON o.id=c.order_id
                WHERE c.vendor_id=:list_vendor';
        $params = ['list_vendor' => $vendorId];
        if ($groupId !== '') {
            $sql .= ' AND c.vendor_group_id=:list_group';
            $params['list_group'] = $groupId;
        }
        $sql .= ' ORDER BY c.created_at DESC';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        json_response(200, ['success' => true, 'collections' => $stmt->fetchAll()]);
    }

    if ($method !== 'POST') {
        json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
    }

    $body = get_json_body();
    $groupId = trim((string)($body['group_id'] ?? ''));
    $status = trim((string)($body['status'] ?? ''));
    $collectionMethod = trim((string)($body['collection_method'] ?? ''));
    $reference = trim((string)($body['collection_reference'] ?? ''));
    $amount = round((float)($body['amount'] ?? 0), 2);
    if ($groupId === '' || !in_array($status, ['collected', 'failed'], true)
        || !in_array($collectionMethod, ['cash', 'upi', 'card', 'bank_transfer'], true)
        || mb_strlen($reference) < 3 || $amount < 0) {
        json_response(422, ['success' => false, 'message' => 'Collection status, method, reference, and amount are required.']);
    }

    $principal = ['type' => 'vendor', 'id' => $vendorId];
    $idempotency = begin_idempotent_request('vendor.cod_collection', $principal, $body);
    $db->beginTransaction();
    try {
        $groupStmt = $db->prepare(
            'SELECT g.*,o.payment_status,o.user_id,o.order_number
             FROM vendor_order_groups g JOIN orders o ON o.id=g.order_id
             WHERE g.id=:group_id AND g.vendor_id=:group_vendor FOR UPDATE'
        );
        $groupStmt->execute(['group_id' => $groupId, 'group_vendor' => $vendorId]);
        $group = $groupStmt->fetch();
        if (!$group) {
            json_response(404, ['success' => false, 'message' => 'Vendor order group not found.']);
        }
        if ($group['status'] !== 'delivered') {
            json_response(409, ['success' => false, 'message' => 'Cash can be reconciled only after the package is delivered.']);
        }
        if ($status === 'collected' && abs($amount - (float)$group['total_amount']) > 0.01) {
            json_response(422, ['success' => false, 'message' => 'Collected amount must exactly match the vendor order total.']);
        }

        $existingStmt = $db->prepare('SELECT * FROM cod_collections WHERE vendor_group_id=:existing_group FOR UPDATE');
        $existingStmt->execute(['existing_group' => $groupId]);
        $existing = $existingStmt->fetch();
        if ($existing && $existing['status'] === 'collected') {
            json_response(409, ['success' => false, 'message' => 'Cash collection is already reconciled.']);
        }

        $collectionId = $existing['id'] ?? uuid_string();
        $failureReason = $status === 'failed' ? mb_substr(trim((string)($body['failure_reason'] ?? '')), 0, 500) : null;
        if ($existing) {
            $db->prepare(
                'UPDATE cod_collections
                 SET amount=:update_amount,status=:update_status,collection_method=:update_method,collection_reference=:update_reference,
                     collected_by_type="vendor",collected_by_id=:update_actor,
                     collected_at=IF(:update_collected_status="collected",UTC_TIMESTAMP(),NULL),failure_reason=:update_reason,updated_at=UTC_TIMESTAMP()
                 WHERE id=:update_id'
            )->execute([
                'update_amount' => $amount, 'update_status' => $status, 'update_method' => $collectionMethod,
                'update_reference' => $reference, 'update_actor' => $vendorId,
                'update_collected_status' => $status, 'update_reason' => $failureReason, 'update_id' => $collectionId,
            ]);
        } else {
            $db->prepare(
                'INSERT INTO cod_collections
                 (id,order_id,vendor_group_id,vendor_id,amount,status,collection_method,collection_reference,collected_by_type,collected_by_id,collected_at,failure_reason,created_at,updated_at)
                 VALUES (:insert_id,:insert_order,:insert_group,:insert_vendor,:insert_amount,:insert_status,:insert_method,:insert_reference,"vendor",:insert_actor,IF(:insert_collected_status="collected",UTC_TIMESTAMP(),NULL),:insert_reason,UTC_TIMESTAMP(),UTC_TIMESTAMP())'
            )->execute([
                'insert_id' => $collectionId, 'insert_order' => $group['order_id'], 'insert_group' => $groupId,
                'insert_vendor' => $vendorId, 'insert_amount' => $amount, 'insert_status' => $status,
                'insert_method' => $collectionMethod, 'insert_reference' => $reference, 'insert_actor' => $vendorId,
                'insert_collected_status' => $status, 'insert_reason' => $failureReason,
            ]);
        }

        if ($status === 'collected') {
            $db->prepare(
                'UPDATE vendor_ledger_entries le
                 JOIN order_items oi ON le.reference_type="order_item" AND CAST(le.reference_id AS UNSIGNED)=oi.id
                 SET le.balance_bucket="eligible",le.available_at=UTC_TIMESTAMP()
                 WHERE oi.vendor_group_id=:eligible_group AND le.balance_bucket="pending"'
            )->execute(['eligible_group' => $groupId]);

            $allStmt = $db->prepare(
                'SELECT COUNT(*) AS total,SUM(status="collected") AS collected
                 FROM cod_collections WHERE order_id=:collection_order FOR UPDATE'
            );
            $allStmt->execute(['collection_order' => $group['order_id']]);
            $counts = $allStmt->fetch() ?: ['total' => 0, 'collected' => 0];
            if ((int)$counts['total'] > 0 && (int)$counts['total'] === (int)$counts['collected']) {
                $db->prepare(
                    'UPDATE orders SET payment_status="paid",updated_at=UTC_TIMESTAMP()
                     WHERE id=:paid_order AND payment_status="cod_pending"'
                )->execute(['paid_order' => $group['order_id']]);
                record_status_change('order', (string)$group['order_id'], 'confirmed', 'confirmed', 'Cash on delivery collected.');
                try { invoice_generate_for_order((int)$group['order_id']); }
                catch (Throwable $e) { error_log('COD invoice generation failed: ' . $e->getMessage()); }
                $awarded = reward_award_if_eligible((int)$group['order_id']);
                if ($awarded > 0) {
                    create_notification('customer', (string)$group['user_id'], 'reward_earned',
                        'You earned reward points!', $awarded . ' points added to your account.', null,
                        ['order_id' => $group['order_id'], 'points' => $awarded]);
                }
            }
        }

        create_notification(
            'customer', $group['user_id'], 'cod_update', 'Cash on delivery update',
            $status === 'collected' ? 'Cash on delivery was confirmed for your order.' : 'Cash collection could not be reconciled for this delivery.',
            '../orders.php', ['order_id' => $group['order_id'], 'group_id' => $groupId]
        );
        audit_event('vendor.cod_' . $status, 'cod_collection', $collectionId, [
            'group_id' => $groupId, 'amount' => $amount, 'reference' => $reference,
        ]);
        $response = [
            'success' => true,
            'message' => $status === 'collected' ? 'Cash collection reconciled.' : 'Cash collection marked failed.',
            'status' => $status,
        ];
        finish_idempotent_request((int)$idempotency['id'], 200, $response);
        $db->commit();
        json_response(200, $response);
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
}
