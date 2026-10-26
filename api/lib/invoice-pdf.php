<?php
declare(strict_types=1);
require_once __DIR__ . '/helpers.php';

/**
 * Render an invoice (in `invoice_to_json()` shape) as a PDF binary string.
 * Uses TCPDF — no external calls, no network.
 */
function invoice_render_pdf(array $inv): string
{
    $autoload=__DIR__.'/../dependencies/autoload.php';
    if(!is_file($autoload))throw new RuntimeException('PDF runtime is unavailable. Install the backend Composer dependencies.');
    require_once $autoload;

    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('Conscious India');
    $pdf->SetAuthor($inv['seller']['name'] ?? 'Conscious India');
    $pdf->SetTitle('Invoice ' . ($inv['number'] ?? ''));
    $pdf->SetSubject('Tax Invoice');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(true);
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(true, 20);
    $pdf->SetFont('dejavusans', '', 9);
    $pdf->AddPage();

    $M = 15;      // left/right margin
    $W = 210;     // page width
    $contentW = $W - 2 * $M;

    // ── Title ──
    $pdf->SetFont('dejavusans', 'B', 18);
    $pdf->Cell($contentW, 10, 'TAX INVOICE', 0, 1, 'C');
    $pdf->Ln(2);

    // ── Seller info (left) + Invoice meta (right) ──
    $seller = $inv['seller'] ?? [];
    $meta   = [
        'Invoice No:' => (string)($inv['number'] ?? ''),
        'Order No:'   => (string)($inv['orderNumber'] ?? ('#' . ($inv['orderId'] ?? ''))),
        'Date:'       => date('d M Y, H:i', strtotime((string)($inv['issuedAt'] ?? 'now'))),
    ];

    $yStart = $pdf->GetY();
    $pdf->SetFont('dejavusans', 'B', 10);
    $pdf->Cell($contentW / 2, 6, (string)($seller['name'] ?? ''), 0, 1, 'L');
    $pdf->SetFont('dejavusans', '', 9);
    if (!empty($seller['address'])) $pdf->MultiCell($contentW / 2, 5, (string)$seller['address'], 0, 'L', false, 1);
    if (!empty($seller['gstin']))   $pdf->Cell($contentW / 2, 5, 'GSTIN: ' . $seller['gstin'], 0, 1, 'L');
    if (!empty($seller['email']))   $pdf->Cell($contentW / 2, 5, (string)$seller['email'], 0, 1, 'L');
    $yEnd = $pdf->GetY();

    // Right-aligned meta block
    $pdf->SetY($yStart);
    $pdf->SetX($M + $contentW / 2);
    $pdf->SetFont('dejavusans', 'B', 9);
    foreach ($meta as $label => $value) {
        $pdf->Cell(30, 5, $label, 0, 0, 'R');
        $pdf->SetFont('dejavusans', '', 9);
        $pdf->Cell($contentW / 2 - 30, 5, $value, 0, 1, 'R');
        $pdf->SetX($M + $contentW / 2);
        $pdf->SetFont('dejavusans', 'B', 9);
    }
    $pdf->SetY(max($yEnd, $pdf->GetY()) + 3);
    $pdf->SetX($M);

    $pdf->SetDrawColor(180, 180, 180);
    $pdf->Line($M, $pdf->GetY(), $W - $M, $pdf->GetY());
    $pdf->Ln(3);

    // ── Bill To ──
    $pdf->SetFont('dejavusans', 'B', 10);
    $pdf->Cell(0, 6, 'Bill To:', 0, 1);
    $pdf->SetFont('dejavusans', '', 9);

    $buyer   = $inv['buyer'] ?? [];
    $billing = $buyer['billing'] ?? [];
    $billLines = array_filter([
        (string)($buyer['name'] ?? ''),
        (string)($billing['address_line1'] ?? ''),
        (string)($billing['address_line2'] ?? ''),
        trim(implode(', ', array_filter([$billing['city'] ?? null, $billing['state'] ?? null, $billing['pincode'] ?? null]))),
        !empty($buyer['phone']) ? 'Ph: ' . $buyer['phone'] : null,
        (string)($buyer['email'] ?? ''),
    ]);
    foreach ($billLines as $line) {
        if ($line === '') continue;
        $pdf->Cell(0, 5, $line, 0, 1);
    }
    $pdf->Ln(3);

    // ── Items table header ──
    $colWidths = [8, 62, 12, 22, 24, 12, 22, 25]; // #, item, qty, rate, taxable, GST%, tax, total
    $headers   = ['#', 'Item', 'Qty', 'Rate', 'Taxable', 'GST%', 'Tax', 'Total'];

    $pdf->SetFillColor(240, 240, 240);
    $pdf->SetFont('dejavusans', 'B', 8);
    for ($i = 0; $i < count($headers); $i++) {
        $align = $i < 2 ? 'L' : 'R';
        $pdf->Cell($colWidths[$i], 7, $headers[$i], 1, 0, $align, true);
    }
    $pdf->Ln();

    // ── Items rows ──
    $pdf->SetFont('dejavusans', '', 8);
    $items = $inv['items'] ?? [];
    foreach ($items as $idx => $it) {
        $tax = (float)($it['cgst'] ?? 0) + (float)($it['sgst'] ?? 0) + (float)($it['igst'] ?? 0);
        $row = [
            (string)($idx + 1),
            mb_substr((string)($it['name'] ?? ''), 0, 40),
            (string)(int)($it['quantity'] ?? 0),
            'Rs.' . number_format((float)($it['unitPrice'] ?? 0), 2),
            'Rs.' . number_format((float)($it['taxableValue'] ?? 0), 2),
            number_format((float)($it['gstPercent'] ?? 0), 2) . '%',
            'Rs.' . number_format($tax, 2),
            'Rs.' . number_format((float)($it['lineTotal'] ?? 0), 2),
        ];
        for ($i = 0; $i < count($row); $i++) {
            $align = $i < 2 ? 'L' : 'R';
            $pdf->Cell($colWidths[$i], 6, $row[$i], 1, 0, $align);
        }
        $pdf->Ln();
    }

    $pdf->Ln(3);

    // ── Totals (right column) ──
    $t = $inv['totals'] ?? [];
    $totalsRows = [
        ['Subtotal',      (float)($t['subtotal']     ?? 0)],
        ['Shipping',      (float)($t['shipping']     ?? 0)],
        ['Discount',     -(float)($t['discount']     ?? 0)],
        ['Taxable Value', (float)($t['taxableValue'] ?? 0)],
        ['CGST',          (float)($t['cgst']         ?? 0)],
        ['SGST',          (float)($t['sgst']         ?? 0)],
        ['IGST',          (float)($t['igst']         ?? 0)],
    ];

    $totalsWidth = 80;
    $totalsX     = $W - $M - $totalsWidth;

    $pdf->SetFont('dejavusans', '', 9);
    foreach ($totalsRows as $row) {
        $pdf->SetX($totalsX);
        $pdf->Cell($totalsWidth / 2, 5, $row[0], 0, 0, 'L');
        $pdf->Cell($totalsWidth / 2, 5, 'Rs.' . number_format($row[1], 2), 0, 1, 'R');
    }

    $pdf->SetX($totalsX);
    $pdf->SetFont('dejavusans', 'B', 11);
    $pdf->Cell($totalsWidth / 2, 7, 'Grand Total', 0, 0, 'L');
    $pdf->Cell($totalsWidth / 2, 7, 'Rs.' . number_format((float)($t['grandTotal'] ?? 0), 2), 0, 1, 'R');

    $pdf->Ln(2);
    $pdf->SetFont('dejavusans', 'I', 9);
    $pdf->MultiCell($contentW, 5, 'Amount in words: ' . (string)($t['amountInWords'] ?? ''), 0, 'L');
    $pdf->Ln(2);

    // ── Payment info ──
    if (!empty($inv['payment'])) {
        $pdf->SetFont('dejavusans', '', 9);
        $p = $inv['payment'];
        $pdf->Cell(0, 5, 'Payment Method: ' . (string)($p['provider'] ?? '—'), 0, 1);
        $pdf->Cell(0, 5, 'Payment Status: ' . (string)($p['status'] ?? '—'), 0, 1);
        if (!empty($p['provider_payment_id'])) {
            $pdf->Cell(0, 5, 'Txn ID: ' . (string)$p['provider_payment_id'], 0, 1);
        }
    }

    // ── Footer (printed on every page) ──
    $pdf->SetY(-15);
    $pdf->SetFont('dejavusans', 'I', 8);
    $pdf->Cell(0, 5, 'This is a computer-generated invoice. No signature required.', 0, 0, 'C');

    return (string)$pdf->Output('', 'S');
}
