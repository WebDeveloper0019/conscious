<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/invoice.php';

send_cors_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

$payload = require_auth();
$userId  = auth_user_id($payload);
$db      = get_db();

$invoiceNumber = trim((string)($_GET['invoice_number'] ?? ''));
$orderNumber   = trim((string)($_GET['orderNumber']   ?? ''));

if ($invoiceNumber === '' && $orderNumber === '') {
    json_response(422, ['success' => false, 'message' => 'Provide invoice_number or orderNumber.']);
}

try {
    if ($invoiceNumber !== '') {
        $stmt = $db->prepare('SELECT * FROM invoices WHERE invoice_number = :n AND user_id = :u LIMIT 1');
        $stmt->execute(['n' => $invoiceNumber, 'u' => $userId]);
    } else {
        $stmt = $db->prepare(
            'SELECT i.* FROM invoices i
             JOIN orders o ON o.id = i.order_id
             WHERE o.order_number = :on AND i.user_id = :u LIMIT 1'
        );
        $stmt->execute(['on' => $orderNumber, 'u' => $userId]);
    }

    $row = $stmt->fetch();

<<<<<<< HEAD
    // If no invoice yet, and this order belongs to the user and is paid/confirmed, generate it
=======
    // Generate lazily only after payment is complete.
>>>>>>> origin/main
    if (!$row && $orderNumber !== '') {
        $orderStmt = $db->prepare(
            'SELECT id, payment_status, status FROM orders
             WHERE order_number = :on AND user_id = :u LIMIT 1'
        );
        $orderStmt->execute(['on' => $orderNumber, 'u' => $userId]);
        $order = $orderStmt->fetch();

        if (!$order) {
            json_response(404, ['success' => false, 'message' => 'Order not found.']);
        }

<<<<<<< HEAD
        $paidOrConfirmed = in_array(strtolower((string)$order['payment_status']), ['paid', 'cod_pending'], true)
            || in_array(strtolower((string)$order['status']), ['confirmed', 'processing', 'shipped', 'delivered'], true);

        if (!$paidOrConfirmed) {
            json_response(409, ['success' => false, 'message' => 'Invoice is available once the order is confirmed.']);
=======
        if (strtolower((string)$order['payment_status']) !== 'paid') {
            json_response(409, ['success' => false, 'message' => 'Invoice is available once payment is complete.']);
>>>>>>> origin/main
        }

        $db->beginTransaction();
        try {
            $row = invoice_generate_for_order((int)$order['id']);
            $db->commit();
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }
    }

    if (!$row) {
        json_response(404, ['success' => false, 'message' => 'Invoice not found.']);
    }

    json_response(200, [
        'success' => true,
        'invoice' => invoice_to_json($row),
    ]);

} catch (Throwable $e) {
    error_log('Invoice API error: ' . $e->getMessage());
    json_response(500, ['success' => false, 'message' => 'Unable to retrieve invoice.']);
<<<<<<< HEAD
}
=======
}
>>>>>>> origin/main
