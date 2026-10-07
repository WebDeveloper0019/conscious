<?php
declare(strict_types=1);

function sync_group_from_packages_safe(string $groupId): void
{
    $db = get_db();
    $statusStmt = $db->prepare('SELECT status,COUNT(*) AS package_count FROM shipment_packages WHERE vendor_group_id=:status_group GROUP BY status');
    $statusStmt->execute(['status_group' => $groupId]);
    $statuses = [];
    foreach ($statusStmt->fetchAll() as $row) {
        $statuses[(string)$row['status']] = (int)$row['package_count'];
    }
    if (!$statuses) {
        return;
    }
    $next = null;
    if (count($statuses) === 1 && isset($statuses['delivered'])) {
        $next = 'delivered';
    } elseif (count($statuses) === 1 && isset($statuses['returned_to_vendor'])) {
        $next = 'returned_to_vendor';
    } elseif (isset($statuses['delivery_failed']) || isset($statuses['delivery_attempted'])) {
        $next = 'delivery_exception';
    } elseif (isset($statuses['in_transit']) || isset($statuses['at_destination']) || isset($statuses['out_for_delivery']) || isset($statuses['picked_up'])) {
        $next = 'in_transit';
    } elseif (isset($statuses['dispatched'])) {
        $next = count($statuses) === 1 ? 'dispatched' : 'partially_dispatched';
    }
    if ($next === null) {
        return;
    }

    $groupStmt = $db->prepare('SELECT status,order_id FROM vendor_order_groups WHERE id=:current_group FOR UPDATE');
    $groupStmt->execute(['current_group' => $groupId]);
    $current = $groupStmt->fetch();
    if (!$current) {
        return;
    }
    $db->prepare('UPDATE vendor_order_groups SET status=:new_group_status,updated_at=UTC_TIMESTAMP() WHERE id=:update_group')->execute([
        'new_group_status' => $next, 'update_group' => $groupId,
    ]);
    $db->prepare('UPDATE order_items SET status=:new_item_status WHERE vendor_group_id=:item_group AND status NOT IN ("cancelled","returned")')->execute([
        'new_item_status' => $next, 'item_group' => $groupId,
    ]);

    if ($next === 'delivered') {
        $db->prepare(
            'UPDATE vendor_ledger_entries le
             JOIN order_items oi ON le.reference_type="order_item" AND CAST(le.reference_id AS UNSIGNED)=oi.id
             SET le.balance_bucket="eligible",le.available_at=UTC_TIMESTAMP()
             WHERE oi.vendor_group_id=:eligible_group AND le.balance_bucket="pending"
               AND EXISTS (
                   SELECT 1 FROM orders o
                   JOIN vendor_order_groups g2 ON g2.order_id=o.id
                   LEFT JOIN cod_collections c ON c.vendor_group_id=g2.id
                   WHERE g2.id=oi.vendor_group_id AND (o.payment_status="paid" OR c.status="collected")
               )'
        )->execute(['eligible_group' => $groupId]);
    }

    $openStmt = $db->prepare(
        'SELECT COUNT(*) FROM vendor_order_groups
         WHERE order_id=:open_order AND status NOT IN ("delivered","cancelled","rejected","returned_to_vendor")'
    );
    $openStmt->execute(['open_order' => $current['order_id']]);
    if ((int)$openStmt->fetchColumn() !== 0) {
        return;
    }

    $failedStmt = $db->prepare('SELECT COUNT(*) FROM vendor_order_groups WHERE order_id=:failed_order AND status<>"delivered"');
    $failedStmt->execute(['failed_order' => $current['order_id']]);
    $orderStatus = (int)$failedStmt->fetchColumn() === 0 ? 'delivered' : 'completed_with_exceptions';
    $db->prepare('UPDATE orders SET status=:new_order_status,updated_at=UTC_TIMESTAMP() WHERE id=:order_id')->execute([
        'new_order_status' => $orderStatus, 'order_id' => $current['order_id'],
    ]);

    if ($orderStatus === 'delivered') {
        $paymentStmt = $db->prepare('SELECT payment_status FROM orders WHERE id=:payment_order');
        $paymentStmt->execute(['payment_order' => $current['order_id']]);
        if ($paymentStmt->fetchColumn() === 'paid') {
            $db->prepare('UPDATE influencer_conversions SET status="eligible",eligible_at=UTC_TIMESTAMP() WHERE order_id=:conversion_order AND status="pending"')->execute([
                'conversion_order' => $current['order_id'],
            ]);
            $db->prepare(
                'UPDATE influencer_ledger_entries le
                 JOIN influencer_conversions c ON le.reference_type="conversion" AND le.reference_id=c.id
                 SET le.status="eligible"
                 WHERE c.order_id=:influencer_order AND le.status="pending"'
            )->execute(['influencer_order' => $current['order_id']]);
        }
    }
}
