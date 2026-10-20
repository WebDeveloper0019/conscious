<?php
declare(strict_types=1);

function admin_payouts_v2(): void
{
    send_cors_headers();
    $context = require_admin('payouts.review');
    $db = get_db();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($method === 'GET') {
        $stmt = $db->query(
            'SELECT p.*,v.display_name,b.beneficiary_name,b.account_last4,b.bank_name,b.verification_status AS bank_status
             FROM payout_requests p JOIN vendors v ON v.id=p.vendor_id JOIN vendor_bank_accounts b ON b.id=p.bank_account_id
             ORDER BY p.requested_at DESC LIMIT 300'
        );
        json_response(200, ['success' => true, 'payouts' => $stmt->fetchAll()]);
    }
    if ($method !== 'POST') {
        json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
    }
    $body = get_json_body();
    $payoutId = trim((string)($body['payout_id'] ?? ''));
    $action = trim((string)($body['action'] ?? ''));
    $reason = trim((string)($body['reason'] ?? ''));
    $reference = trim((string)($body['payment_reference'] ?? ''));
    $allowed = ['maker_approve', 'checker_approve', 'reject', 'mark_paid', 'mark_failed'];
    if ($payoutId === '' || !in_array($action, $allowed, true)) {
        json_response(422, ['success' => false, 'message' => 'Invalid payout action.']);
    }
    if ($action === 'mark_paid' && mb_strlen($reference) < 4) {
        json_response(422, ['success' => false, 'message' => 'Payment reference is required.']);
    }
    if (in_array($action, ['reject', 'mark_failed'], true) && mb_strlen($reason) < 10) {
        json_response(422, ['success' => false, 'message' => 'A detailed reason is required.']);
    }
    $idempotency = begin_idempotent_request('admin.payout.update', $context['principal'], $body);
    $db->beginTransaction();
    try {
        $stmt = $db->prepare('SELECT * FROM payout_requests WHERE id=:payout_id FOR UPDATE');
        $stmt->execute(['payout_id' => $payoutId]);
        $payout = $stmt->fetch();
        if (!$payout) {
            json_response(404, ['success' => false, 'message' => 'Payout not found.']);
        }
        $next = [
            'maker_approve' => 'maker_approved', 'checker_approve' => 'processing', 'reject' => 'rejected',
            'mark_paid' => 'paid', 'mark_failed' => 'failed',
        ][$action];
        $map = [
            'requested' => ['maker_approved', 'rejected'],
            'maker_approved' => ['processing', 'rejected'],
            'processing' => ['paid', 'failed'],
        ];
        assert_transition($payout['status'], $next, $map);
        if ($action === 'checker_approve' && $payout['maker_admin_id'] === $context['admin']['id']) {
            json_response(409, ['success' => false, 'message' => 'Maker and checker must be different administrators.']);
        }

        $adminId = $context['admin']['id'];
        if ($action === 'maker_approve') {
            $db->prepare('UPDATE payout_requests SET status=:maker_status,maker_admin_id=:maker_admin,maker_at=UTC_TIMESTAMP(),updated_at=UTC_TIMESTAMP() WHERE id=:maker_id')->execute([
                'maker_status' => $next, 'maker_admin' => $adminId, 'maker_id' => $payoutId,
            ]);
        } elseif ($action === 'checker_approve') {
            $db->prepare('UPDATE payout_requests SET status=:checker_status,checker_admin_id=:checker_admin,checked_at=UTC_TIMESTAMP(),updated_at=UTC_TIMESTAMP() WHERE id=:checker_id')->execute([
                'checker_status' => $next, 'checker_admin' => $adminId, 'checker_id' => $payoutId,
            ]);
        } elseif ($action === 'reject') {
            $db->prepare('UPDATE payout_requests SET status=:reject_status,rejection_reason=:reject_reason,updated_at=UTC_TIMESTAMP() WHERE id=:reject_id')->execute([
                'reject_status' => $next, 'reject_reason' => $reason, 'reject_id' => $payoutId,
            ]);
        } elseif ($action === 'mark_paid') {
            $db->prepare('UPDATE payout_requests SET status=:paid_status,payment_reference=:paid_reference,paid_at=UTC_TIMESTAMP(),updated_at=UTC_TIMESTAMP() WHERE id=:paid_id')->execute([
                'paid_status' => $next, 'paid_reference' => $reference, 'paid_id' => $payoutId,
            ]);
            $db->prepare(
                'INSERT INTO vendor_ledger_entries
                 (id,vendor_id,entry_type,amount,currency,balance_bucket,reference_type,reference_id,description,created_at)
                 VALUES (:ledger_id,:ledger_vendor,"payout",:ledger_amount,"INR","paid","payout",:ledger_reference,"Vendor payout completed",UTC_TIMESTAMP())'
            )->execute([
                'ledger_id' => uuid_string(), 'ledger_vendor' => $payout['vendor_id'],
                'ledger_amount' => -abs((float)$payout['amount']), 'ledger_reference' => $payoutId,
            ]);
        } else {
            $db->prepare('UPDATE payout_requests SET status=:failed_status,rejection_reason=:failed_reason,updated_at=UTC_TIMESTAMP() WHERE id=:failed_id')->execute([
                'failed_status' => $next, 'failed_reason' => $reason, 'failed_id' => $payoutId,
            ]);
        }
        create_notification('vendor', $payout['vendor_id'], 'payout_update', 'Payout status updated', 'Your payout request is now ' . str_replace('_', ' ', $next) . '.', '../payouts.php', ['payout_id' => $payoutId]);
        record_status_change('payout_request', $payoutId, $payout['status'], $next, $reason ?: null);
        audit_event('admin.payout_' . $action, 'payout_request', $payoutId, ['payment_reference' => $reference ?: null]);
        $response = ['success' => true, 'message' => 'Payout status updated.', 'status' => $next];
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
