<?php
declare(strict_types=1);
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/invoice.php';

/**
 * Financial year string, shared with invoice logic.
 */
function credit_note_financial_year(?DateTimeImmutable $when = null): string
{
    return invoice_financial_year($when);
}

/**
 * Atomically allocate the next credit-note number for the FY.
 */
function credit_note_next_number(PDO $db, string $fy): array
{
    $prefix = (string)invoice_setting('credit_note_prefix', 'CN');

    $db->prepare(
        'INSERT INTO credit_note_sequences (fy, next_number, updated_at)
         VALUES (:fy, 1, UTC_TIMESTAMP())
         ON DUPLICATE KEY UPDATE fy = fy'
    )->execute(['fy' => $fy]);

    $stmt = $db->prepare('SELECT next_number FROM credit_note_sequences WHERE fy = :fy FOR UPDATE');
    $stmt->execute(['fy' => $fy]);
    $seq = (int)$stmt->fetchColumn();

    $db->prepare('UPDATE credit_note_sequences SET next_number = next_number + 1, updated_at = UTC_TIMESTAMP() WHERE fy = :fy')
       ->execute(['fy' => $fy]);

    $formatted = sprintf('%s/%s/%06d', $prefix, $fy, $seq);
    return ['number' => $formatted, 'sequence' => $seq];
}

/**
 * Generate a credit note for a completed refund. Idempotent — returns the
 * existing credit note if already generated for this refund.
 *
 * MUST be called inside a transaction.
 */
function credit_note_generate_for_refund(string $refundId): array
{
    $db = get_db();

    // Already generated?
    $existing = $db->prepare('SELECT * FROM credit_notes WHERE refund_id = ? LIMIT 1');
    $existing->execute([$refundId]);
    $row = $existing->fetch();
    if ($row) return $row;

    // Load refund + order + user
    $rStmt = $db->prepare(
        'SELECT r.*, o.order_number, o.user_id, o.address_snapshot_json,
                o.total_amount, o.subtotal, o.tax_amount, o.shipping_amount,
                u.first_name, u.last_name, u.email, u.mobile
         FROM refunds r
         JOIN orders o ON o.id = r.order_id
         JOIN users  u ON u.id = o.user_id
         WHERE r.id = ? LIMIT 1 FOR UPDATE'
    );
    $rStmt->execute([$refundId]);
    $refund = $rStmt->fetch();
    if (!$refund) throw new RuntimeException('Refund not found for credit note.');

    // Load original invoice if any
    $invStmt = $db->prepare('SELECT id, invoice_number, seller_snapshot_json, items_snapshot_json FROM invoices WHERE order_id = ? LIMIT 1');
    $invStmt->execute([$refund['order_id']]);
    $invoice = $invStmt->fetch() ?: null;

    // Seller snapshot — reuse from invoice if available, else read fresh
    if ($invoice) {
        $seller = json_decode((string)$invoice['seller_snapshot_json'], true) ?: [];
    } else {
        $seller = [
            'name'       => (string)invoice_setting('seller_legal_name', 'Conscious India'),
            'gstin'      => (string)invoice_setting('seller_gstin', ''),
            'pan'        => (string)invoice_setting('seller_pan', ''),
            'address'    => (string)invoice_setting('seller_address', ''),
            'state'      => (string)invoice_setting('seller_state', ''),
            'state_code' => (string)invoice_setting('seller_state_code', ''),
            'email'      => (string)invoice_setting('seller_email', ''),
            'phone'      => (string)invoice_setting('seller_phone', ''),
        ];
    }

    // Buyer snapshot
    $billing = json_decode((string)$refund['address_snapshot_json'], true) ?: [];
    $buyer = [
        'name'    => trim(($refund['first_name'] ?? '') . ' ' . ($refund['last_name'] ?? '')),
        'email'   => (string)$refund['email'],
        'phone'   => (string)$refund['mobile'],
        'billing' => $billing,
        'state'   => (string)($billing['state'] ?? ''),
    ];

    // Refund amount
    $totalCredit = (float)$refund['amount'];

    // GST split — same logic as invoice
    $isIntraState = $buyer['state'] !== '' && strcasecmp($buyer['state'], $seller['state']) === 0;

    // Reverse GST from the refunded amount. If refund details have per-item
    // amounts, use those; else derive from order proportion.
    $details = json_decode((string)($refund['details_json'] ?? 'null'), true) ?: [];
    $itemsOut = [];

    // For proportional GST: original order tax / original subtotal = tax rate
    $origSubtotal = (float)$refund['subtotal'];
    $origTax      = (float)$refund['tax_amount'];
    $taxRate      = $origSubtotal > 0 ? ($origTax / $origSubtotal) : 0.05;

    // Try to reconstruct per-item credit from details_json
    $shippingRefunded = (float)($details['shipping_refunded'] ?? 0);
    $itemCreditTotal  = max(0.0, $totalCredit - $shippingRefunded);

    if (!empty($details['items']) && is_array($details['items'])) {
        // Approximate GST on each returned item using overall tax rate
        foreach ($details['items'] as $it) {
            $lineAmount = (float)($it['amount'] ?? 0);
            $gstAmount  = round($lineAmount * $taxRate / (1 + $taxRate), 2);
            $taxable    = round($lineAmount - $gstAmount, 2);

            $cgst = $sgst = $igst = 0.0;
            if ($isIntraState) {
                $cgst = round($gstAmount / 2, 2);
                $sgst = round($gstAmount - $cgst, 2);
            } else {
                $igst = $gstAmount;
            }

            $itemsOut[] = [
                'name'      => (string)($it['name'] ?? ''),
                'quantity'  => (int)($it['quantity'] ?? 0),
                'amount'    => $lineAmount,
                'taxable'   => $taxable,
                'gstPercent'=> round($taxRate * 100, 2),
                'cgst'      => $cgst,
                'sgst'      => $sgst,
                'igst'      => $igst,
            ];
        }
    }

    // If no per-item breakdown, treat the whole credit as one line
    if (!$itemsOut) {
        $gstAmount = round($itemCreditTotal * $taxRate / (1 + $taxRate), 2);
        $taxable   = round($itemCreditTotal - $gstAmount, 2);
        $cgst = $sgst = $igst = 0.0;
        if ($isIntraState) {
            $cgst = round($gstAmount / 2, 2);
            $sgst = round($gstAmount - $cgst, 2);
        } else {
            $igst = $gstAmount;
        }
        $itemsOut[] = [
            'name'      => 'Refund against order ' . $refund['order_number'],
            'quantity'  => 1,
            'amount'    => $itemCreditTotal,
            'taxable'   => $taxable,
            'gstPercent'=> round($taxRate * 100, 2),
            'cgst'      => $cgst,
            'sgst'      => $sgst,
            'igst'      => $igst,
        ];
    }

    // Sum tax lines
    $sumTaxable = 0.0; $sumCgst = 0.0; $sumSgst = 0.0; $sumIgst = 0.0;
    foreach ($itemsOut as $it) {
        $sumTaxable += $it['taxable'];
        $sumCgst    += $it['cgst'];
        $sumSgst    += $it['sgst'];
        $sumIgst    += $it['igst'];
    }
    $sumTaxable = round($sumTaxable, 2);
    $sumCgst    = round($sumCgst, 2);
    $sumSgst    = round($sumSgst, 2);
    $sumIgst    = round($sumIgst, 2);
    $totalTax   = round($sumCgst + $sumSgst + $sumIgst, 2);

    // Allocate number
    $issuedAt = new DateTimeImmutable('now', new DateTimeZone('UTC'));
    $fy   = credit_note_financial_year($issuedAt);
    $alloc = credit_note_next_number($db, $fy);

    $id = uuid_string();
    $reason = (string)($refund['reason'] ?? 'Customer cancellation');
    $scope  = $details['scope'] ?? 'full';

    $db->prepare(
        'INSERT INTO credit_notes
           (id, credit_note_number, financial_year, sequence_number,
            refund_id, order_id, user_id,
            original_invoice_id, original_invoice_number,
            reason, cancellation_scope,
            taxable_value, cgst_amount, sgst_amount, igst_amount, total_tax, total_credit, currency,
            seller_snapshot_json, buyer_snapshot_json, items_snapshot_json, refund_snapshot_json,
            issued_at, created_at)
         VALUES
           (?, ?, ?, ?,
            ?, ?, ?,
            ?, ?,
            ?, ?,
            ?, ?, ?, ?, ?, ?, "INR",
            ?, ?, ?, ?,
            UTC_TIMESTAMP(), UTC_TIMESTAMP())'
    )->execute([
        $id,
        $alloc['number'],
        $fy,
        $alloc['sequence'],
        $refundId,
        (int)$refund['order_id'],
        (string)$refund['user_id'],
        $invoice ? $invoice['id'] : null,
        $invoice ? $invoice['invoice_number'] : null,
        $reason,
        $scope,
        $sumTaxable,
        $sumCgst,
        $sumSgst,
        $sumIgst,
        $totalTax,
        $totalCredit,
        json_encode($seller, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        json_encode($buyer,  JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        json_encode([
            'items'              => $itemsOut,
            'shipping_refunded'  => $shippingRefunded,
            'is_intra_state'     => $isIntraState,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        json_encode([
            'method'              => $refund['method'],
            'provider_reference'  => $refund['provider_reference'] ?? null,
            'provider_payment_id' => $refund['provider_payment_id'] ?? null,
            'processed_at'        => $refund['processed_at'] ?? null,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
    ]);

    $fetch = $db->prepare('SELECT * FROM credit_notes WHERE id = ?');
    $fetch->execute([$id]);
    return $fetch->fetch() ?: [];
}

/**
 * Shape a credit_note row into the API response structure.
 */
function credit_note_to_json(array $row): array
{
    $seller  = json_decode((string)$row['seller_snapshot_json'], true) ?: [];
    $buyer   = json_decode((string)$row['buyer_snapshot_json'],  true) ?: [];
    $items   = json_decode((string)$row['items_snapshot_json'],  true) ?: [];
    $refund  = json_decode((string)($row['refund_snapshot_json'] ?? 'null'), true) ?: [];

    return [
        'id'                     => $row['id'],
        'number'                 => $row['credit_note_number'],
        'financialYear'          => $row['financial_year'],
        'sequenceNumber'         => (int)$row['sequence_number'],
        'issuedAt'               => $row['issued_at'],
        'orderId'                => (int)$row['order_id'],
        'refundId'               => $row['refund_id'],
        'originalInvoiceNumber'  => $row['original_invoice_number'],
        'reason'                 => $row['reason'],
        'scope'                  => $row['cancellation_scope'],

        'seller' => $seller,
        'buyer'  => $buyer,
        'items'  => $items['items'] ?? [],
        'refund' => $refund,

        'totals' => [
            'taxableValue' => (float)$row['taxable_value'],
            'cgst'         => (float)$row['cgst_amount'],
            'sgst'         => (float)$row['sgst_amount'],
            'igst'         => (float)$row['igst_amount'],
            'totalTax'     => (float)$row['total_tax'],
            'totalCredit'  => (float)$row['total_credit'],
            'currency'     => $row['currency'],
            'amountInWords'=> invoice_amount_in_words((float)$row['total_credit']),
        ],
    ];
}