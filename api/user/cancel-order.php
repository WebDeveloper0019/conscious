
<?php

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';

send_cors_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    json_response(405, [
        'success' => false,
        'message' => 'Method not allowed.'
    ]);
}

$payload = require_auth();
$userId = auth_user_id($payload);

require_csrf();

if (rate_limit('cancel-order:' . $userId, 5, 300)) {
    json_response(429, [
        'success' => false,
        'message' => 'Too many cancellation requests. Please try again later.'
    ]);
}

$body = get_json_body();

$orderNumber = trim((string) ($body['orderNumber'] ?? ''));
$reason = trim((string) ($body['reason'] ?? ''));

if (
    $orderNumber === ''
    || strlen($orderNumber) > 100
) {
    json_response(422, [
        'success' => false,
        'message' => 'A valid order number is required.'
    ]);
}

if (strlen($reason) > 500) {
    json_response(422, [
        'success' => false,
        'message' => 'Reason must not exceed 500 characters.'
    ]);
}

$db = get_db();

try {
    $db->beginTransaction();

    // Lock and verify the order belongs to the authenticated customer.
    $stmt = $db->prepare(
        'SELECT id, order_number, user_id, status,
                payment_status, created_at, cancelled_at
         FROM orders
         WHERE order_number = :orderNumber
           AND user_id = :userId
         LIMIT 1
         FOR UPDATE'
    );

    $stmt->execute([
        'orderNumber' => $orderNumber,
        'userId' => $userId
    ]);

    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        $db->rollBack();

        json_response(404, [
            'success' => false,
            'message' => 'Order not found.'
        ]);
    }

    $status = strtolower(trim((string) $order['status']));

    if ($status === 'cancelled' || !empty($order['cancelled_at'])) {
        $db->rollBack();

        json_response(409, [
            'success' => false,
            'message' => 'This order has already been cancelled.'
        ]);
    }

    // Only known, cancellable order statuses are accepted.
    if (!in_array($status, ['pending', 'processing'], true)) {
        $db->rollBack();

        json_response(409, [
            'success' => false,
            'message' => 'This order is not eligible for normal cancellation.'
        ]);
    }

    $createdAt = strtotime((string) $order['created_at']);

    if ($createdAt === false || $createdAt > time()) {
        $db->rollBack();

        json_response(409, [
            'success' => false,
            'message' => 'The order placement time could not be verified.'
        ]);
    }

    $hoursElapsed = (time() - $createdAt) / 3600;

    if ($hoursElapsed <= 24) {
        // Cancel immediately within the initial 24-hour window.
        $update = $db->prepare(
            "UPDATE orders
             SET status = 'cancelled',
                 cancelled_at = UTC_TIMESTAMP()
             WHERE id = :orderId
               AND user_id = :userId
               AND status IN ('pending', 'processing')
               AND cancelled_at IS NULL"
        );

        $update->execute([
            'orderId' => $order['id'],
            'userId' => $userId
        ]);

        if ($update->rowCount() !== 1) {
            $db->rollBack();

            json_response(409, [
                'success' => false,
                'message' => 'The order status changed. Please refresh and try again.'
            ]);
        }

        $db->commit();

        json_response(200, [
            'success' => true,
            'message' => 'Order cancelled successfully.',
            'orderNumber' => $order['order_number'],
            'status' => 'cancelled',
            'refundStatus' => 'not_processed'
        ]);
    }

    // Late cancellation requests require a persistent request table.
    // Do not silently cancel the order or claim a request was recorded.
    $db->rollBack();

    json_response(501, [
        'success' => false,
        'message' => 'Late cancellation requests are not configured yet. Please contact customer support.'
    ]);

} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    error_log('Customer cancellation error: ' . $e->getMessage());

    json_response(500, [
        'success' => false,
        'message' => 'Unable to process the cancellation request.'
    ]);
}