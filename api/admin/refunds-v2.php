<?php
declare(strict_types=1);

function admin_refunds_v2(): void
{
    send_cors_headers();
    $context = require_admin('returns.manage');
    $db = get_db();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($method === 'GET') {
        $stmt = $db->query(
            'SELECT f.*,o.order_number,r.vendor_id,r.user_id
             FROM refunds f JOIN orders o ON o.id=f.order_id LEFT JOIN return_requests r ON r.id=f.return_request_id
             ORDER BY f.created_at DESC LIMIT 300'
        );
        json_response(200, ['success' => true, 'refunds' => $stmt->fetchAll()]);
    }
    if ($method !== 'POST') {
        json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
    }
    $body = get_json_body();
    $refundId = trim((string)($body['refund_id'] ?? ''));
    $action = trim((string)($body['action'] ?? ''));
    $reference = trim((string)($body['provider_reference'] ?? ''));
    $allowed = ['approve', 'reject', 'mark_processing', 'mark_processed', 'mark_failed'];
    if ($refundId === '' || !in_array($action, $allowed, true)) {
        json_response(422, ['success' => false, 'message' => 'Invalid refund action.']);
    }
    if ($action === 'mark_processed' && mb_strlen($reference) < 4) {
        json_response(422, ['success' => false, 'message' => 'Payment provider refund reference is required.']);
    }
    $idempotency = begin_idempotent_request('admin.refund.update', $context['principal'], $body);
    $db->beginTransaction();
    try {
        $stmt = $db->prepare(
            'SELECT f.*,r.vendor_id,r.user_id,r.order_item_id,r.quantity AS returned_quantity,r.request_type,r.status AS return_status
             FROM refunds f LEFT JOIN return_requests r ON r.id=f.return_request_id
             WHERE f.id=:refund_id FOR UPDATE'
        );
        $stmt->execute(['refund_id' => $refundId]);
        $refund = $stmt->fetch();
        if (!$refund) {
            json_response(404, ['success' => false, 'message' => 'Refund not found.']);
        }
        $next = [
            'approve' => 'approved', 'reject' => 'rejected', 'mark_processing' => 'processing',
            'mark_processed' => 'processed', 'mark_failed' => 'failed',
        ][$action];
        $map = [
            'recommended' => ['approved', 'rejected'], 'approved' => ['processing'],
            'processing' => ['processed', 'failed'], 'failed' => ['processing'],
        ];
        assert_transition($refund['status'], $next, $map);
        $adminId = $context['admin']['id'];
        if ($action === 'approve') {
            $db->prepare('UPDATE refunds SET status=:approved_status,approved_by=:approved_admin,approved_at=UTC_TIMESTAMP(),updated_at=UTC_TIMESTAMP() WHERE id=:approved_id')->execute([
                'approved_status' => $next, 'approved_admin' => $adminId, 'approved_id' => $refundId,
            ]);
        } elseif ($action === 'reject') {
            $db->prepare('UPDATE refunds SET status=:rejected_status,updated_at=UTC_TIMESTAMP() WHERE id=:rejected_id')->execute([
                'rejected_status' => $next, 'rejected_id' => $refundId,
            ]);
        } elseif ($action === 'mark_processing') {
            $db->prepare('UPDATE refunds SET status=:processing_status,provider_reference=COALESCE(:processing_reference,provider_reference),updated_at=UTC_TIMESTAMP() WHERE id=:processing_id')->execute([
                'processing_status' => $next, 'processing_reference' => $reference ?: null, 'processing_id' => $refundId,
            ]);
        } elseif ($action === 'mark_failed') {
            $db->prepare('UPDATE refunds SET status=:failed_status,provider_reference=COALESCE(:failed_reference,provider_reference),updated_at=UTC_TIMESTAMP() WHERE id=:failed_id')->execute([
                'failed_status' => $next, 'failed_reference' => $reference ?: null, 'failed_id' => $refundId,
            ]);
        } else {
            $db->prepare('UPDATE refunds SET status=:processed_status,provider_reference=:processed_reference,processed_at=UTC_TIMESTAMP(),updated_at=UTC_TIMESTAMP() WHERE id=:processed_id')->execute([
                'processed_status' => $next, 'processed_reference' => $reference, 'processed_id' => $refundId,
            ]);
            if ($refund['vendor_id']) {
                $db->prepare(
                    'INSERT INTO vendor_ledger_entries
                     (id,vendor_id,entry_type,amount,currency,balance_bucket,reference_type,reference_id,description,created_at)
                     VALUES (:ledger_id,:ledger_vendor,"refund_reversal",:ledger_amount,"INR","reversed","refund",:ledger_reference,"Customer refund reversal",UTC_TIMESTAMP())'
                )->execute([
                    'ledger_id' => uuid_string(), 'ledger_vendor' => $refund['vendor_id'],
                    'ledger_amount' => -abs((float)$refund['amount']), 'ledger_reference' => $refundId,
                ]);
            }
            if ($refund['return_request_id']) {
                $db->prepare(
                    'UPDATE return_requests SET status="refunded",resolution="refund",completed_at=UTC_TIMESTAMP(),updated_at=UTC_TIMESTAMP()
                     WHERE id=:return_id'
                )->execute(['return_id' => $refund['return_request_id']]);
                if ($refund['request_type'] === 'return' && (int)$refund['returned_quantity'] > 0 && $refund['order_item_id']) {
                    $itemStmt = $db->prepare('SELECT variant_id,vendor_id FROM order_items WHERE id=:returned_item FOR UPDATE');
                    $itemStmt->execute(['returned_item' => $refund['order_item_id']]);
                    $item = $itemStmt->fetch();
                    if ($item && $item['variant_id']) {
                        $qty = (int)$refund['returned_quantity'];
                        $db->prepare('UPDATE inventory_levels SET stock_quantity=stock_quantity+:restock_qty,updated_at=UTC_TIMESTAMP() WHERE variant_id=:restock_variant')->execute([
                            'restock_qty' => $qty, 'restock_variant' => $item['variant_id'],
                        ]);
                        $levelStmt = $db->prepare('SELECT stock_quantity,reserved_quantity FROM inventory_levels WHERE variant_id=:restock_level');
                        $levelStmt->execute(['restock_level' => $item['variant_id']]);
                        $level = $levelStmt->fetch();
                        $db->prepare(
                            'INSERT INTO inventory_movements
                             (variant_id,vendor_id,movement_type,quantity_delta,reserved_delta,stock_after,reserved_after,reference_type,reference_id,note,actor_type,actor_id,created_at)
                             VALUES (:return_variant,:return_vendor,"return",:return_qty,0,:return_stock,:return_reserved,"refund",:return_reference,"Returned stock restocked after refund","admin",:return_actor,UTC_TIMESTAMP())'
                        )->execute([
                            'return_variant' => $item['variant_id'], 'return_vendor' => $item['vendor_id'],
                            'return_qty' => $qty, 'return_stock' => $level['stock_quantity'],
                            'return_reserved' => $level['reserved_quantity'], 'return_reference' => $refundId,
                            'return_actor' => $adminId,
                        ]);
                        $db->prepare('UPDATE order_items SET returned_quantity=returned_quantity+:returned_qty,status="returned" WHERE id=:returned_item_id')->execute([
                            'returned_qty' => $qty, 'returned_item_id' => $refund['order_item_id'],
                        ]);
                    }
                }
            }
            $refundTotals = $db->prepare('SELECT COALESCE(SUM(amount),0) FROM refunds WHERE order_id=:refund_order AND status="processed"');
            $refundTotals->execute(['refund_order' => $refund['order_id']]);
            $orderTotal = $db->prepare('SELECT total_amount FROM orders WHERE id=:total_order');
            $orderTotal->execute(['total_order' => $refund['order_id']]);
            $paidRefund = (float)$refundTotals->fetchColumn();
            $totalAmount = (float)$orderTotal->fetchColumn();
            $newPaymentStatus = $paidRefund >= $totalAmount ? 'refunded' : 'partially_refunded';
            $db->prepare('UPDATE orders SET payment_status=:refund_payment_status,updated_at=UTC_TIMESTAMP() WHERE id=:refund_order_id')->execute([
                'refund_payment_status' => $newPaymentStatus, 'refund_order_id' => $refund['order_id'],
            ]);
        }
        if ($refund['user_id']) {
            create_notification('customer', $refund['user_id'], 'refund_update', 'Refund status updated', 'Your refund is now ' . $next . '.', '../returns.php', ['refund_id' => $refundId]);
        }
        record_status_change('refund', $refundId, $refund['status'], $next);
        audit_event('admin.refund_' . $action, 'refund', $refundId, ['provider_reference' => $reference ?: null]);
        $response = ['success' => true, 'message' => 'Refund status updated.', 'status' => $next];
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
