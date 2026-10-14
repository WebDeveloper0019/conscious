<?php
declare(strict_types=1);
require_once __DIR__ . '/helpers.php';

function credit_note_render_pdf(array $cn): string
{
    require_once __DIR__ . '/tcpdf/tcpdf.php';

    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('Conscious Indi');
    $pdf->SetAuthor($cn['seller']['name'] ?? 'Conscious Indi');
    $pdf->SetTitle('Credit Note ' . ($cn['number'] ?? ''));
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(true);
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(true, 20);
    $pdf->SetFont('dejavusans', '', 9);
    $pdf->AddPage();

    $M = 15; $W = 210; $contentW = $W - 2 * $M;

    $pdf->SetFont('dejavusans', 'B', 18);
    $pdf->SetTextColor(200, 40, 40);
    $pdf->Cell($contentW, 10, 'CREDIT NOTE', 0, 1, 'C');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(2);

    $seller = $cn['seller'] ?? [];
    $meta = [
        'Credit Note No:' => (string)($cn['number'] ?? ''),
        'Original Invoice:' => (string)($cn['originalInvoiceNumber'] ?? 'N/A'),
        'Order No:'       => (string)($cn['orderNumber'] ?? ('#' . ($cn['orderId'] ?? ''))),
        'Date:'           => date('d M Y, H:i', strtotime((string)($cn['issuedAt'] ?? 'now'))),
    ];

    $yStart = $pdf->GetY();
    $pdf->SetFont('dejavusans', 'B', 10);
    $pdf->Cell($contentW / 2, 6, (string)($seller['name'] ?? ''), 0, 1, 'L');
    $pdf->SetFont('dejavusans', '', 9);
    if (!empty($seller['address'])) $pdf->MultiCell($contentW / 2, 5, (string)$seller['address'], 0, 'L', false, 1);
    if (!empty($seller['gstin']))   $pdf->Cell($contentW / 2, 5, 'GSTIN: ' . $seller['gstin'], 0, 1, 'L');
    $yEnd = $pdf->GetY();

    $pdf->SetY($yStart);
    $pdf->SetX($M + $contentW / 2);
    $pdf->SetFont('dejavusans', 'B', 9);
    foreach ($meta as $label => $value) {
        $pdf->Cell(38, 5, $label, 0, 0, 'R');
        $pdf->SetFont('dejavusans', '', 9);
        $pdf->Cell($contentW / 2 - 38, 5, $value, 0, 1, 'R');
        $pdf->SetX($M + $contentW / 2);
        $pdf->SetFont('dejavusans', 'B', 9);
    }
    $pdf->SetY(max($yEnd, $pdf->GetY()) + 3);
    $pdf->SetX($M);

    $pdf->SetDrawColor(180, 180, 180);
    $pdf->Line($M, $pdf->GetY(), $W - $M, $pdf->GetY());
    $pdf->Ln(3);

    $buyer = $cn['buyer'] ?? [];
    $billing = $buyer['billing'] ?? [];
    $pdf->SetFont('dejavusans', 'B', 10);
    $pdf->Cell(0, 6, 'Credit To:', 0, 1);
    $pdf->SetFont('dejavusans', '', 9);
    foreach (array_filter([
        (string)($buyer['name'] ?? ''),
        (string)($billing['address_line1'] ?? ''),
        (string)($billing['address_line2'] ?? ''),
        trim(implode(', ', array_filter([$billing['city'] ?? null, $billing['state'] ?? null, $billing['pincode'] ?? null]))),
        (string)($buyer['email'] ?? ''),
    ]) as $line) {
        if ($line === '') continue;
        $pdf->Cell(0, 5, $line, 0, 1);
    }
    $pdf->Ln(2);

    $pdf->SetFont('dejavusans', 'B', 9);
    $pdf->Cell(0, 5, 'Reason: ', 0, 0);
    $pdf->SetFont('dejavusans', '', 9);
    $pdf->MultiCell(0, 5, (string)($cn['reason'] ?? 'Customer cancellation'), 0, 'L');
    $pdf->Ln(3);

    // Items
    $colWidths = [8, 72, 12, 25, 25, 15, 25];
    $headers   = ['#', 'Description', 'Qty', 'Taxable', 'GST%', 'GST Amt', 'Amount'];
    $pdf->SetFillColor(250, 235, 235);
    $pdf->SetFont('dejavusans', 'B', 8);
    for ($i = 0; $i < count($headers); $i++) {
        $align = $i < 2 ? 'L' : 'R';
        $pdf->Cell($colWidths[$i], 7, $headers[$i], 1, 0, $align, true);
    }
    $pdf->Ln();

    $pdf->SetFont('dejavusans', '', 8);
    foreach (($cn['items'] ?? []) as $idx => $it) {
        $gst = (float)($it['cgst'] ?? 0) + (float)($it['sgst'] ?? 0) + (float)($it['igst'] ?? 0);
        $row = [
            (string)($idx + 1),
            mb_substr((string)($it['name'] ?? ''), 0, 45),
            (string)(int)($it['quantity'] ?? 0),
            'Rs.' . number_format((float)($it['taxable'] ?? 0), 2),
            number_format((float)($it['gstPercent'] ?? 0), 2) . '%',
            'Rs.' . number_format($gst, 2),
            'Rs.' . number_format((float)($it['amount'] ?? 0), 2),
        ];
        for ($i = 0; $i < count($row); $i++) {
            $align = $i < 2 ? 'L' : 'R';
            $pdf->Cell($colWidths[$i], 6, $row[$i], 1, 0, $align);
        }
        $pdf->Ln();
    }
    $pdf->Ln(3);

    $t = $cn['totals'] ?? [];
    $totalsRows = [
        ['Taxable Value', (float)($t['taxableValue'] ?? 0)],
        ['CGST',          (float)($t['cgst'] ?? 0)],
        ['SGST',          (float)($t['sgst'] ?? 0)],
        ['IGST',          (float)($t['igst'] ?? 0)],
    ];
    $totalsWidth = 80; $totalsX = $W - $M - $totalsWidth;
    $pdf->SetFont('dejavusans', '', 9);
    foreach ($totalsRows as $row) {
        $pdf->SetX($totalsX);
        $pdf->Cell($totalsWidth / 2, 5, $row[0], 0, 0, 'L');
        $pdf->Cell($totalsWidth / 2, 5, 'Rs.' . number_format($row[1], 2), 0, 1, 'R');
    }
    $pdf->SetX($totalsX);
    $pdf->SetFont('dejavusans', 'B', 11);
    $pdf->Cell($totalsWidth / 2, 7, 'Total Credit', 0, 0, 'L');
    $pdf->Cell($totalsWidth / 2, 7, 'Rs.' . number_format((float)($t['totalCredit'] ?? 0), 2), 0, 1, 'R');

    $pdf->Ln(2);
    $pdf->SetFont('dejavusans', 'I', 9);
    $pdf->MultiCell($contentW, 5, 'Amount in words: ' . (string)($t['amountInWords'] ?? ''), 0, 'L');

    $pdf->Ln(4);
    $pdf->SetFont('dejavusans', '', 9);
    $pdf->Cell(0, 5, 'Refund Method: ' . (string)($cn['refund']['method'] ?? '—'), 0, 1);
    if (!empty($cn['refund']['provider_reference'])) {
        $pdf->Cell(0, 5, 'Refund Ref: ' . $cn['refund']['provider_reference'], 0, 1);
    }

    $pdf->SetY(-15);
    $pdf->SetFont('dejavusans', 'I', 8);
    $pdf->Cell(0, 5, 'This is a computer-generated credit note. No signature required.', 0, 0, 'C');

    return (string)$pdf->Output('', 'S');
}