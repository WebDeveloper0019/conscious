<?php
declare(strict_types=1);
require_once __DIR__ . '/helpers.php';

/**
 * Read a platform setting stored as {"value": ...}
 */
function invoice_setting(string $key, $default = null)
{
    static $cache = [];
    if (array_key_exists($key, $cache)) return $cache[$key];

    $stmt = get_db()->prepare('SELECT value_json FROM platform_settings WHERE setting_key = :k');
    $stmt->execute(['k' => $key]);
    $raw = $stmt->fetchColumn();
    if ($raw === false) return $cache[$key] = $default;

    $decoded = json_decode((string)$raw, true);
    return $cache[$key] = $decoded['value'] ?? $default;
}

/** Returns financial year like "2026-27" for a given date. Indian FY = Apr 1 – Mar 31. */
function invoice_financial_year(?DateTimeImmutable $when = null): string
{
    $when = $when ?? new DateTimeImmutable('now', new DateTimeZone('UTC'));
    $y = (int)$when->format('Y');
    $m = (int)$when->format('n');
    if ($m < 4) { $start = $y - 1; } else { $start = $y; }
    $end = $start + 1;
    return $start . '-' . substr((string)$end, -2);
}

/** Atomically allocate the next invoice number for this FY. */
function invoice_next_number(PDO $db, string $fy): array
{
    $prefix = (string)invoice_setting('invoice_prefix', 'INV');

    // Ensure row exists
    $db->prepare(
        'INSERT INTO invoice_sequences (fy, next_number, updated_at)
         VALUES (:fy, 1, UTC_TIMESTAMP())
         ON DUPLICATE KEY UPDATE fy = fy'
    )->execute(['fy' => $fy]);

    // Lock row
    $stmt = $db->prepare('SELECT next_number FROM invoice_sequences WHERE fy = :fy FOR UPDATE');
    $stmt->execute(['fy' => $fy]);
    $seq = (int)$stmt->fetchColumn();

    $db->prepare('UPDATE invoice_sequences SET next_number = next_number + 1, updated_at = UTC_TIMESTAMP() WHERE fy = :fy')
       ->execute(['fy' => $fy]);

    $formatted = sprintf('%s/%s/%06d', $prefix, $fy, $seq);
    return ['number' => $formatted, 'sequence' => $seq];
}

/** Convert number to Indian-style words for the invoice total. */
function invoice_amount_in_words(float $amount): string
{
    $amount = round($amount, 2);
    $rupees = (int)floor($amount);
    $paise  = (int)round(($amount - $rupees) * 100);

    $units = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
              'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
              'Seventeen', 'Eighteen', 'Nineteen'];
    $tens  = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

    $two = function (int $n) use ($units, $tens): string {
        if ($n < 20) return $units[$n];
        $t = (int)($n / 10); $u = $n % 10;
        return $tens[$t] . ($u ? ' ' . $units[$u] : '');
    };

    $three = function (int $n) use ($two): string {
        $h = (int)($n / 100); $r = $n % 100;
        $out = '';
        if ($h) $out .= $two($h) . ' Hundred';
        if ($r) $out .= ($out ? ' ' : '') . $two($r);
        return $out;
    };

    $out = '';
    if ($rupees === 0) { $out = 'Zero'; }
    else {
        $crore = (int)($rupees / 10000000); $rupees %= 10000000;
        $lakh  = (int)($rupees / 100000);   $rupees %= 100000;
        $thou  = (int)($rupees / 1000);     $rupees %= 1000;
        if ($crore) $out .= $three($crore) . ' Crore ';
        if ($lakh)  $out .= $three($lakh)  . ' Lakh ';
        if ($thou)  $out .= $three($thou)  . ' Thousand ';
        if ($rupees) $out .= $three($rupees);
        $out = trim($out);
    }

    $words = $out . ' Rupees';
    if ($paise) $words .= ' and ' . $two($paise) . ' Paise';
    return $words . ' Only';
}

/**
 * Generate and persist an invoice for an order. Idempotent — if the
 * order already has an invoice, returns it.
 *
 * MUST be called inside a transaction.
 */
function invoice_generate_for_order(int $orderId): array
{
    $db = get_db();

    // Already exists?
    $existing = $db->prepare('SELECT * FROM invoices WHERE order_id = :oid LIMIT 1');
    $existing->execute(['oid' => $orderId]);
    $row = $existing->fetch();
    if ($row) return $row;

    // Load order + user + items + financials
    $oStmt = $db->prepare(
        'SELECT o.*, u.first_name, u.last_name, u.email, u.mobile
         FROM orders o JOIN users u ON u.id = o.user_id
         WHERE o.id = :oid LIMIT 1 FOR UPDATE'
    );
    $oStmt->execute(['oid' => $orderId]);
    $order = $oStmt->fetch();
    if (!$order) throw new RuntimeException('Order not found for invoice.');

    $iStmt = $db->prepare(
        'SELECT oi.*, f.commission_percent, f.commission_amount, f.vendor_net_amount
         FROM order_items oi
         LEFT JOIN order_item_financials f ON f.order_item_id = oi.id
         WHERE oi.order_id = :oid
         ORDER BY oi.id'
    );
    $iStmt->execute(['oid' => $orderId]);
    $items = $iStmt->fetchAll();

    // Seller details
    $seller = [
        'name'       => (string)invoice_setting('seller_legal_name', 'Conscious Indi'),
        'gstin'      => (string)invoice_setting('seller_gstin', ''),
        'pan'        => (string)invoice_setting('seller_pan', ''),
        'address'    => (string)invoice_setting('seller_address', ''),
        'state'      => (string)invoice_setting('seller_state', ''),
        'state_code' => (string)invoice_setting('seller_state_code', ''),
        'email'      => (string)invoice_setting('seller_email', ''),
        'phone'      => (string)invoice_setting('seller_phone', ''),
    ];

    // Buyer details
    $addressSnapshot = json_decode((string)($order['address_snapshot_json'] ?? '{}'), true) ?: [];
    $buyerState      = (string)($addressSnapshot['state'] ?? '');
    $buyer = [
        'name'    => trim(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? '')),
        'email'   => (string)$order['email'],
        'phone'   => (string)$order['mobile'],
        'billing' => $addressSnapshot,
        'state'   => $buyerState,
    ];

    // Determine tax split
    $isIntraState = $buyerState !== '' && strcasecmp($buyerState, $seller['state']) === 0;

    // Build line items + tax totals
    $lineItems  = [];
    $sumTaxable = 0.0;
    $sumCgst    = 0.0;
    $sumSgst    = 0.0;
    $sumIgst    = 0.0;

    foreach ($items as $it) {
        // GST from product_snapshot_json if present, else default
        $snap = json_decode((string)($it['product_snapshot_json'] ?? '{}'), true) ?: [];
        $gstPct = (float)($snap['gst_percent'] ?? invoice_setting('default_gst_percent', 5));

        // line_total is already tax-inclusive based on checkout-place logic
        $lineTotal = (float)$it['line_total'];
        $taxAmount = round($lineTotal * $gstPct / (100 + $gstPct), 2);
        $taxable   = round($lineTotal - $taxAmount, 2);

        $cgst = $sgst = $igst = 0.0;
        if ($isIntraState) {
            $cgst = round($taxAmount / 2, 2);
            $sgst = round($taxAmount - $cgst, 2);
        } else {
            $igst = $taxAmount;
        }

        $sumTaxable += $taxable;
        $sumCgst    += $cgst;
        $sumSgst    += $sgst;
        $sumIgst    += $igst;

        $lineItems[] = [
            'name'         => (string)$it['product_name'],
            'sku'          => (string)($it['sku'] ?? ''),
            'hsn'          => (string)($snap['hsn'] ?? ''),
            'quantity'     => (int)$it['quantity'],
            'unitPrice'    => (float)$it['unit_price'],
            'mrp'          => (float)($it['mrp'] ?? 0),
            'discount'     => (float)$it['discount_amount'],
            'taxableValue' => $taxable,
            'gstPercent'   => $gstPct,
            'cgst'         => $cgst,
            'sgst'         => $sgst,
            'igst'         => $igst,
            'lineTotal'    => $lineTotal,
        ];
    }

    $sumTaxable = round($sumTaxable, 2);
    $sumCgst    = round($sumCgst, 2);
    $sumSgst    = round($sumSgst, 2);
    $sumIgst    = round($sumIgst, 2);
    $totalTax   = round($sumCgst + $sumSgst + $sumIgst, 2);

    // Allocate invoice number
    $issuedAt = new DateTimeImmutable('now', new DateTimeZone('UTC'));
    $fy = invoice_financial_year($issuedAt);
    $alloc = invoice_next_number($db, $fy);

    // Payment snapshot
    $payStmt = $db->prepare('SELECT provider, status, provider_payment_id, amount, currency, created_at FROM payment_attempts WHERE order_id = :oid ORDER BY created_at DESC LIMIT 1');
    $payStmt->execute(['oid' => $orderId]);
    $payment = $payStmt->fetch() ?: null;

    $invoiceId = uuid_string();

    $db->prepare(
        'INSERT INTO invoices
           (id, invoice_number, order_id, user_id, financial_year, sequence_number,
            subtotal, shipping_amount, discount_amount,
            taxable_value, cgst_amount, sgst_amount, igst_amount, total_tax, grand_total, currency,
            seller_snapshot_json, buyer_snapshot_json, items_snapshot_json, payment_snapshot_json,
            issued_at, created_at)
         VALUES
           (:id, :num, :oid, :uid, :fy, :seq,
            :subtotal, :ship, :disc,
            :taxable, :cgst, :sgst, :igst, :tax, :grand, :cur,
            :seller, :buyer, :items, :payment,
            UTC_TIMESTAMP(), UTC_TIMESTAMP())'
    )->execute([
        'id'         => $invoiceId,
        'num'        => $alloc['number'],
        'oid'        => $orderId,
        'uid'        => (string)$order['user_id'],
        'fy'         => $fy,
        'seq'        => $alloc['sequence'],
        'subtotal'   => (float)$order['subtotal'],
        'ship'       => (float)$order['shipping_amount'],
        'disc'       => (float)$order['discount_amount'],
        'taxable'    => $sumTaxable,
        'cgst'       => $sumCgst,
        'sgst'       => $sumSgst,
        'igst'       => $sumIgst,
        'tax'        => $totalTax,
        'grand'      => (float)$order['total_amount'],
        'cur'        => 'INR',
        'seller'     => json_encode($seller, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        'buyer'      => json_encode($buyer,  JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        'items'      => json_encode($lineItems, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        'payment'    => $payment ? json_encode($payment, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null,
    ]);

    // Return fresh row
    $fetch = $db->prepare('SELECT * FROM invoices WHERE id = :id');
    $fetch->execute(['id' => $invoiceId]);
    return $fetch->fetch() ?: [];
}

/** Convert an invoices DB row into the JSON shape the frontend expects. */
function invoice_to_json(array $row): array
{
    $seller   = json_decode((string)$row['seller_snapshot_json'], true) ?: [];
    $buyer    = json_decode((string)$row['buyer_snapshot_json'],  true) ?: [];
    $items    = json_decode((string)$row['items_snapshot_json'],  true) ?: [];
    $payment  = json_decode((string)($row['payment_snapshot_json'] ?? 'null'), true);

    return [
        'id'             => $row['id'],
        'number'         => $row['invoice_number'],
        'orderId'        => (int)$row['order_id'],
        'financialYear'  => $row['financial_year'],
        'sequenceNumber' => (int)$row['sequence_number'],
        'issuedAt'       => $row['issued_at'],

        'seller' => $seller,
        'buyer'  => $buyer,
        'items'  => $items,
        'payment'=> $payment,

        'totals' => [
            'subtotal'       => (float)$row['subtotal'],
            'shipping'       => (float)$row['shipping_amount'],
            'discount'       => (float)$row['discount_amount'],
            'taxableValue'   => (float)$row['taxable_value'],
            'cgst'           => (float)$row['cgst_amount'],
            'sgst'           => (float)$row['sgst_amount'],
            'igst'           => (float)$row['igst_amount'],
            'totalTax'       => (float)$row['total_tax'],
            'grandTotal'     => (float)$row['grand_total'],
            'currency'       => $row['currency'],
            'amountInWords'  => invoice_amount_in_words((float)$row['grand_total']),
        ],
    ];
}