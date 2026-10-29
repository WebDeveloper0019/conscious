<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/invoice.php';
require_once __DIR__ . '/../lib/invoice-pdf.php';

// Prevent any accidental HTML output from including the renderer
if (ob_get_level()) ob_end_clean();
$principal = require_principal(['customer']); $userId = $principal['id'];
$db      = get_db();

$invoiceNumber = trim((string)($_GET['invoice_number'] ?? ''));
$orderNumber   = trim((string)($_GET['orderNumber']    ?? ''));
$mode          = strtolower(trim((string)($_GET['mode'] ?? 'download')));

if ($invoiceNumber === '' && $orderNumber === '') {
    header('Content-Type: application/json');
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Provide invoice_number or orderNumber.']);
    exit;
}

if (!in_array($mode, ['download', 'preview'], true)) $mode = 'download';

// ── Fetch invoice (with auto-generate fallback) ──
if ($invoiceNumber !== '') {
    $stmt = $db->prepare('SELECT * FROM invoices WHERE invoice_number = ? AND user_id = ? LIMIT 1');
    $stmt->execute([$invoiceNumber, $userId]);
} else {
    $stmt = $db->prepare(
        'SELECT i.* FROM invoices i
         JOIN orders o ON o.id = i.order_id
         WHERE o.order_number = ? AND i.user_id = ? LIMIT 1'
    );
    $stmt->execute([$orderNumber, $userId]);
}
$row = $stmt->fetch();

if (!$row && $orderNumber !== '') {
    // Order exists and belongs to user but invoice not yet generated
    $oStmt = $db->prepare('SELECT id, status, payment_status FROM orders WHERE order_number = ? AND user_id = ? LIMIT 1');
    $oStmt->execute([$orderNumber, $userId]);
    $order = $oStmt->fetch();

    if (!$order) {
        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Order not found.']);
        exit;
    }

    $paidOrConfirmed = in_array(strtolower((string)$order['payment_status']), ['paid', 'cod_pending'], true)
        || in_array(strtolower((string)$order['status']), ['confirmed','processing','shipped','delivered'], true);

    if (!$paidOrConfirmed) {
        header('Content-Type: application/json');
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Invoice available once order is confirmed.']);
        exit;
    }

    $db->beginTransaction();
    try {
        $row = invoice_generate_for_order((int)$order['id']);
        $db->commit();
    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        error_log('Invoice gen on demand failed: ' . $e->getMessage());
    }
}

if (!$row) {
    header('Content-Type: application/json');
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Invoice not found.']);
    exit;
}

// ── Render PDF ──
try {
    $inv = invoice_to_json($row);
    $pdf = invoice_render_pdf($inv);

    $filename = str_replace(['/', '\\'], '_', (string)$row['invoice_number']) . '.pdf';

    header('Content-Type: application/pdf');
    header('Content-Disposition: ' . ($mode === 'preview' ? 'inline' : 'attachment') . '; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($pdf));
    header('Cache-Control: private, max-age=0, no-store');
    header('X-Content-Type-Options: nosniff');

    echo $pdf;
    exit;

} catch (Throwable $e) {
    error_log('Invoice PDF render failed: ' . $e->getMessage());
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to generate PDF.']);
    exit;
}