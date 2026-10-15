<?php
declare(strict_types=1);
require_once __DIR__.'/helpers.php';
require_once __DIR__.'/invoice.php';


function confirm_online_order(int $orderId, string $paymentReference): void {
    $db = get_db();

    $order = $db->prepare('SELECT * FROM orders WHERE id=:id FOR UPDATE');
    $order->execute(['id' => $orderId]);
    $row = $order->fetch();
    if (!$row) throw new RuntimeException('Order not found.');
    if ($row['payment_status'] === 'paid') return;
    if ($row['payment_status'] !== 'pending') throw new RuntimeException('Order is not awaiting payment.');

    // Consume reservations → real stock
    $reservations = $db->prepare('SELECT r.* FROM inventory_reservations r WHERE r.quote_id=:quote AND r.status="reserved" FOR UPDATE');
    $reservations->execute(['quote' => $row['checkout_quote_id']]);
    $reserved = $reservations->fetchAll();
    if (!$reserved) throw new RuntimeException('Order stock reservation expired.');

    foreach ($reserved as $reservation) {
        $update = $db->prepare('UPDATE inventory_levels SET stock_quantity=stock_quantity-:qty_sale,reserved_quantity=reserved_quantity-:qty_reserve,updated_at=UTC_TIMESTAMP() WHERE variant_id=:variant AND stock_quantity>=:qty_stock AND reserved_quantity>=:qty_reserved');
        $update->execute([
            'qty_sale'      => $reservation['quantity'],
            'qty_reserve'   => $reservation['quantity'],
            'qty_stock'     => $reservation['quantity'],
            'qty_reserved'  => $reservation['quantity'],
            'variant'       => $reservation['variant_id'],
        ]);
        if (!$update->rowCount()) throw new RuntimeException('Reserved stock cannot be converted.');
        $db->prepare('UPDATE inventory_reservations SET status="converted",released_at=UTC_TIMESTAMP() WHERE id=:id')
           ->execute(['id' => $reservation['id']]);
    }

    // Vendor ledger + inventory movements
    $items = $db->prepare('SELECT oi.id,oi.vendor_id,oi.variant_id,oi.quantity,f.vendor_net_amount FROM order_items oi JOIN order_item_financials f ON f.order_item_id=oi.id WHERE oi.order_id=:order');
    $items->execute(['order' => $orderId]);
    foreach ($items->fetchAll() as $item) {
        $level = $db->prepare('SELECT stock_quantity,reserved_quantity FROM inventory_levels WHERE variant_id=:variant');
        $level->execute(['variant' => $item['variant_id']]);
        $after = $level->fetch();

        $db->prepare('INSERT INTO inventory_movements (variant_id,vendor_id,movement_type,quantity_delta,reserved_delta,stock_after,reserved_after,reference_type,reference_id,note,actor_type,actor_id,created_at) VALUES (:variant_move,:vendor,"sale",-:qty_move,-:qty_reserved,:stock,:reserved,"order_item",:reference,"Online payment confirmed","system",:system,UTC_TIMESTAMP())')
           ->execute([
               'variant_move' => $item['variant_id'],
               'vendor'       => $item['vendor_id'],
               'qty_move'     => $item['quantity'],
               'qty_reserved' => $item['quantity'],
               'stock'        => $after['stock_quantity'],
               'reserved'     => $after['reserved_quantity'],
               'reference'    => (string)$item['id'],
               'system'       => '00000000-0000-0000-0000-000000000000',
           ]);

        $db->prepare('INSERT INTO vendor_ledger_entries (id,vendor_id,entry_type,amount,currency,balance_bucket,reference_type,reference_id,description,available_at,created_at) VALUES (:id,:vendor,"order_earning",:amount,"INR","pending","order_item",:reference,"Order earning pending delivery",DATE_ADD(UTC_TIMESTAMP(),INTERVAL 7 DAY),UTC_TIMESTAMP())')
           ->execute([
               'id'        => uuid_string(),
               'vendor'    => $item['vendor_id'],
               'amount'    => $item['vendor_net_amount'],
               'reference' => (string)$item['id'],
           ]);
    }

    // Mark order paid
    $db->prepare('UPDATE orders SET status="confirmed",payment_status="paid",updated_at=UTC_TIMESTAMP() WHERE id=:id')
       ->execute(['id' => $orderId]);

    // ⚠️ SINGLE coupon redemption block (the duplicate was removed)
    $couponStmt = $db->prepare('SELECT coupon_id, discount_amount FROM checkout_quotes WHERE id = :quote_id');
    $couponStmt->execute(['quote_id' => $row['checkout_quote_id']]);
    $coupon = $couponStmt->fetch();

    if ($coupon && $coupon['coupon_id'] !== null) {
        $db->prepare('INSERT INTO coupon_redemptions (id, coupon_id, user_id, order_id, discount_amount, created_at) VALUES (:id, :coupon_id, :user_id, :order_id, :discount_amount, UTC_TIMESTAMP())')
           ->execute([
               'id'              => uuid_string(),
               'coupon_id'       => $coupon['coupon_id'],
               'user_id'         => $row['user_id'],
               'order_id'        => $orderId,
               'discount_amount' => $coupon['discount_amount'],
           ]);
    }

    // Payment attempt + status history
    $db->prepare('UPDATE payment_attempts SET status="paid",provider_payment_id=:reference,updated_at=UTC_TIMESTAMP() WHERE order_id=:order AND status="created"')
       ->execute(['reference' => $paymentReference, 'order' => $orderId]);
    // ── Generate invoice ──
    try {
        invoice_generate_for_order($orderId);
    } catch (Throwable $e) {
        error_log('Invoice generation failed for order ' . $orderId . ': ' . $e->getMessage());
    }

     record_status_change('order', (string)$orderId, $row['status'], 'confirmed', 'Online payment confirmed');

    // ⭐ Referral reward — fires on buyer's first paid order only
    if (!empty($row['user_id'])) {
        require_once __DIR__ . '/referral.php';
        try {
            referral_reward_on_first_order((string)$row['user_id']);
        } catch (Throwable $e) {
            error_log('Referral reward failed for user ' . $row['user_id'] . ': ' . $e->getMessage());
        }
    }

function confirm_online_order(int $orderId,string $paymentReference): void {
    $db=get_db();$order=$db->prepare('SELECT * FROM orders WHERE id=:id FOR UPDATE');$order->execute(['id'=>$orderId]);$row=$order->fetch();if(!$row)throw new RuntimeException('Order not found.');if($row['payment_status']==='paid')return;if($row['payment_status']!=='pending')throw new RuntimeException('Order is not awaiting payment.');$reservations=$db->prepare('SELECT r.* FROM inventory_reservations r WHERE r.quote_id=:quote AND r.status="reserved" FOR UPDATE');$reservations->execute(['quote'=>$row['checkout_quote_id']]);$reserved=$reservations->fetchAll();if(!$reserved)throw new RuntimeException('Order stock reservation expired.');foreach($reserved as $reservation){$update=$db->prepare('UPDATE inventory_levels SET stock_quantity=stock_quantity-:qty_sale,reserved_quantity=reserved_quantity-:qty_reserve,updated_at=UTC_TIMESTAMP() WHERE variant_id=:variant AND stock_quantity>=:qty_stock AND reserved_quantity>=:qty_reserved');$update->execute(['qty_sale'=>$reservation['quantity'],'qty_reserve'=>$reservation['quantity'],'qty_stock'=>$reservation['quantity'],'qty_reserved'=>$reservation['quantity'],'variant'=>$reservation['variant_id']]);if(!$update->rowCount())throw new RuntimeException('Reserved stock cannot be converted.');$db->prepare('UPDATE inventory_reservations SET status="converted",released_at=UTC_TIMESTAMP() WHERE id=:id')->execute(['id'=>$reservation['id']]);}$items=$db->prepare('SELECT oi.id,oi.vendor_id,oi.variant_id,oi.quantity,f.vendor_net_amount FROM order_items oi JOIN order_item_financials f ON f.order_item_id=oi.id WHERE oi.order_id=:order');$items->execute(['order'=>$orderId]);foreach($items->fetchAll() as $item){$level=$db->prepare('SELECT stock_quantity,reserved_quantity FROM inventory_levels WHERE variant_id=:variant');$level->execute(['variant'=>$item['variant_id']]);$after=$level->fetch();$db->prepare('INSERT INTO inventory_movements (variant_id,vendor_id,movement_type,quantity_delta,reserved_delta,stock_after,reserved_after,reference_type,reference_id,note,actor_type,actor_id,created_at) VALUES (:variant_move,:vendor,"sale",-:qty_move,-:qty_reserved,:stock,:reserved,"order_item",:reference,"Online payment confirmed","system",:system,UTC_TIMESTAMP())')->execute(['variant_move'=>$item['variant_id'],'vendor'=>$item['vendor_id'],'qty_move'=>$item['quantity'],'qty_reserved'=>$item['quantity'],'stock'=>$after['stock_quantity'],'reserved'=>$after['reserved_quantity'],'reference'=>(string)$item['id'],'system'=>'00000000-0000-0000-0000-000000000000']);$db->prepare('INSERT INTO vendor_ledger_entries (id,vendor_id,entry_type,amount,currency,balance_bucket,reference_type,reference_id,description,available_at,created_at) VALUES (:id,:vendor,"order_earning",:amount,"INR","pending","order_item",:reference,"Order earning pending delivery",DATE_ADD(UTC_TIMESTAMP(),INTERVAL 7 DAY),UTC_TIMESTAMP())')->execute(['id'=>uuid_string(),'vendor'=>$item['vendor_id'],'amount'=>$item['vendor_net_amount'],'reference'=>(string)$item['id']]);}$db->prepare('UPDATE orders SET status="confirmed",payment_status="paid",updated_at=UTC_TIMESTAMP() WHERE id=:id')->execute(['id'=>$orderId]);


$couponStmt = $db->prepare(
    'SELECT q.coupon_id,q.discount_amount,c.status,c.starts_at,c.ends_at,c.usage_limit,c.per_customer_limit
     FROM checkout_quotes q
     LEFT JOIN coupons c ON c.id=q.coupon_id
     WHERE q.id=:quote_id
     FOR UPDATE'
);
$couponStmt->execute(['quote_id' => $row['checkout_quote_id']]);
$coupon = $couponStmt->fetch();

if ($coupon && $coupon['coupon_id'] !== null) {
    if ($coupon['status'] !== 'active'
        || strtotime((string)$coupon['starts_at']) > time()
        || strtotime((string)$coupon['ends_at']) < time()) {
        throw new RuntimeException('The applied coupon is no longer active.');
    }
    $usageStmt = $db->prepare(
        'SELECT COUNT(*) AS total_usage,
                SUM(user_id=:usage_user) AS customer_usage
         FROM coupon_redemptions WHERE coupon_id=:usage_coupon'
    );
    $usageStmt->execute(['usage_user' => $row['user_id'], 'usage_coupon' => $coupon['coupon_id']]);
    $usage = $usageStmt->fetch() ?: ['total_usage' => 0, 'customer_usage' => 0];
    if (($coupon['usage_limit'] !== null && (int)$usage['total_usage'] >= (int)$coupon['usage_limit'])
        || (int)$usage['customer_usage'] >= (int)$coupon['per_customer_limit']) {
        throw new RuntimeException('The applied coupon usage limit has been reached.');
    }
    $db->prepare(
        'INSERT INTO coupon_redemptions
         (id, coupon_id, user_id, order_id, discount_amount, created_at)
         VALUES (:id, :coupon_id, :user_id, :order_id, :discount_amount, UTC_TIMESTAMP())'
    )->execute([
        'id' => uuid_string(),
        'coupon_id' => $coupon['coupon_id'],
        'user_id' => $row['user_id'],
        'order_id' => $orderId,
        'discount_amount' => $coupon['discount_amount'],
    ]);
}


    $db->prepare('UPDATE payment_attempts SET status="paid",provider_payment_id=:reference,updated_at=UTC_TIMESTAMP() WHERE order_id=:order AND status="created"')->execute(['reference'=>$paymentReference,'order'=>$orderId]);record_status_change('order',(string)$orderId,$row['status'],'confirmed','Online payment confirmed');
}

function fail_online_order(int $orderId, string $message): void {
    $db = get_db();
    $stmt = $db->prepare('SELECT * FROM orders WHERE id=:id FOR UPDATE');
    $stmt->execute(['id' => $orderId]);
    $order = $stmt->fetch();
    if (!$order || $order['payment_status'] !== 'pending') return;
    release_quote_reservations($order['checkout_quote_id']);
    $db->prepare('UPDATE orders SET status="payment_failed",payment_status="failed",updated_at=UTC_TIMESTAMP() WHERE id=:id')
       ->execute(['id' => $orderId]);
    $db->prepare('UPDATE payment_attempts SET status="failed",failure_message=:message,updated_at=UTC_TIMESTAMP() WHERE order_id=:order AND status="created"')
       ->execute(['message' => mb_substr($message, 0, 500), 'order' => $orderId]);
       record_status_change('order', (string)$orderId, $row['status'], 'confirmed', 'Online payment confirmed');

    // ⭐ Referral reward — fires on buyer's first paid order only
    if (!empty($row['user_id'])) {
        require_once __DIR__ . '/referral.php';
        try {
            referral_reward_on_first_order((string)$row['user_id']);
        } catch (Throwable $e) {
            error_log('Referral reward failed for user ' . $row['user_id'] . ': ' . $e->getMessage());
        }
    }
}