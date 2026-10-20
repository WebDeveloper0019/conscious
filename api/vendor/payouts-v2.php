<?php
declare(strict_types=1);

function vendor_payouts_v2(): void
{
    send_cors_headers();
    $context = require_vendor(true);
    $vendorId = $context['vendor']['id'];
    $db = get_db();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'GET') {
        $balanceStmt = $db->prepare(
            'SELECT balance_bucket,COALESCE(SUM(amount),0) AS amount
             FROM vendor_ledger_entries WHERE vendor_id=:balance_vendor GROUP BY balance_bucket'
        );
        $balanceStmt->execute(['balance_vendor' => $vendorId]);
        $balance = ['pending' => 0.0, 'eligible' => 0.0, 'held' => 0.0, 'paid' => 0.0, 'reversed' => 0.0];
        foreach ($balanceStmt->fetchAll() as $row) {
            $balance[(string)$row['balance_bucket']] = (float)$row['amount'];
        }
        $heldStmt = $db->prepare(
            'SELECT COALESCE(SUM(amount),0) FROM payout_requests
             WHERE vendor_id=:held_vendor AND status IN ("requested","maker_approved","processing")'
        );
        $heldStmt->execute(['held_vendor' => $vendorId]);
        $balance['held'] = (float)$heldStmt->fetchColumn();
        $available = max(0.0, $balance['eligible'] + $balance['paid'] - $balance['held']);
        $requestsStmt = $db->prepare(
            'SELECT p.id,p.amount,p.status,p.requested_at,p.payment_reference,p.paid_at,
                    b.beneficiary_name,b.account_last4,b.bank_name
             FROM payout_requests p JOIN vendor_bank_accounts b ON b.id=p.bank_account_id
             WHERE p.vendor_id=:requests_vendor ORDER BY p.requested_at DESC'
        );
        $requestsStmt->execute(['requests_vendor' => $vendorId]);
        json_response(200, ['success' => true, 'balance' => $balance, 'available' => $available, 'payouts' => $requestsStmt->fetchAll()]);
    }

    if ($method !== 'POST') {
        json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
    }
    $body = get_json_body();
    $amount = round((float)($body['amount'] ?? 0), 2);
    $bankId = trim((string)($body['bank_account_id'] ?? ''));
    if ($amount < 100 || $amount > 100000000) {
        json_response(422, ['success' => false, 'message' => 'Minimum payout request is ₹100 and the amount is invalid.']);
    }
    $idempotency = begin_idempotent_request('payout.request', ['type' => 'vendor', 'id' => $vendorId], $body);
    $db->beginTransaction();
    try {
        $vendorLock = $db->prepare('SELECT id FROM vendors WHERE id=:vendor FOR UPDATE');
        $vendorLock->execute(['vendor' => $vendorId]);
        if (!$vendorLock->fetch()) {
            json_response(403, ['success' => false, 'message' => 'Vendor account not found.']);
        }
        $bankStmt = $db->prepare(
            'SELECT id FROM vendor_bank_accounts
             WHERE id=:bank_id AND vendor_id=:bank_vendor AND verification_status="verified" FOR UPDATE'
        );
        $bankStmt->execute(['bank_id' => $bankId, 'bank_vendor' => $vendorId]);
        if (!$bankStmt->fetch()) {
            json_response(422, ['success' => false, 'message' => 'Choose a verified bank account.']);
        }

        $ledgerStmt = $db->prepare(
            'SELECT COALESCE(SUM(CASE WHEN balance_bucket="eligible" THEN amount WHEN balance_bucket="paid" THEN amount ELSE 0 END),0)
             FROM vendor_ledger_entries WHERE vendor_id=:ledger_vendor'
        );
        $ledgerStmt->execute(['ledger_vendor' => $vendorId]);
        $earned = (float)$ledgerStmt->fetchColumn();
        $heldStmt = $db->prepare(
            'SELECT COALESCE(SUM(amount),0) FROM payout_requests
             WHERE vendor_id=:pending_vendor AND status IN ("requested","maker_approved","processing")'
        );
        $heldStmt->execute(['pending_vendor' => $vendorId]);
        $available = max(0.0, $earned - (float)$heldStmt->fetchColumn());
        if ($amount > $available + 0.0001) {
            json_response(422, ['success' => false, 'message' => 'Payout amount exceeds your available balance.']);
        }

        $payoutId = uuid_string();
        $db->prepare(
            'INSERT INTO payout_requests (id,vendor_id,bank_account_id,amount,status,requested_at,updated_at)
             VALUES (:payout_id,:payout_vendor,:payout_bank,:payout_amount,"requested",UTC_TIMESTAMP(),UTC_TIMESTAMP())'
        )->execute([
            'payout_id' => $payoutId, 'payout_vendor' => $vendorId, 'payout_bank' => $bankId, 'payout_amount' => $amount,
        ]);
        audit_event('vendor.payout_requested', 'payout_request', $payoutId, ['amount' => $amount]);
        $response = ['success' => true, 'message' => 'Payout request submitted.', 'payout_id' => $payoutId, 'status' => 'requested'];
        finish_idempotent_request((int)$idempotency['id'], 201, $response);
        $db->commit();
        json_response(201, $response);
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
}
