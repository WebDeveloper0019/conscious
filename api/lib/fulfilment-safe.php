<?php
declare(strict_types=1);

<<<<<<< HEAD

require_once __DIR__ . '/rewards.php';


=======
require_once __DIR__ . '/rewards.php';

>>>>>>> origin/main
function sync_group_from_packages_safe(string $groupId): void
{
    $db = get_db();
    $statusStmt = $db->prepare('SELECT status,COUNT(*) AS package_count FROM shipment_packages WHERE vendor_group_id=:group_id GROUP BY status');
    $statusStmt->execute(['group_id' => $groupId]);
    $statuses = [];
    foreach ($statusStmt->fetchAll() as $row) $statuses[(string)$row['status']] = (int)$row['package_count'];
    if (!$statuses) return;

    $next = null;
    if (count($statuses) === 1 && isset($statuses['delivered'])) $next = 'delivered';
    elseif (count($statuses) === 1 && isset($statuses['returned_to_vendor'])) $next = 'returned_to_vendor';
    elseif (isset($statuses['delivery_failed']) || isset($statuses['delivery_attempted'])) $next = 'delivery_exception';
    elseif (isset($statuses['in_transit']) || isset($statuses['at_destination']) || isset($statuses['out_for_delivery']) || isset($statuses['picked_up'])) $next = 'in_transit';
    elseif (isset($statuses['dispatched'])) $next = count($statuses) === 1 ? 'dispatched' : 'partially_dispatched';
    if ($next === null) return;

    $groupStmt = $db->prepare('SELECT status,order_id FROM vendor_order_groups WHERE id=:group_id FOR UPDATE');
    $groupStmt->execute(['group_id' => $groupId]);
    $current = $groupStmt->fetch();
    if (!$current) return;

    $db->prepare('UPDATE vendor_order_groups SET status=:status,updated_at=UTC_TIMESTAMP() WHERE id=:group_id')
       ->execute(['status' => $next, 'group_id' => $groupId]);
    $db->prepare('UPDATE order_items SET status=:status WHERE vendor_group_id=:group_id AND status NOT IN ("cancelled","returned")')
       ->execute(['status' => $next, 'group_id' => $groupId]);

        if ($next === 'delivered') {
        $db->prepare(
            'UPDATE vendor_ledger_entries le
             JOIN order_items oi ON le.reference_type="order_item" AND CAST(le.reference_id AS UNSIGNED)=oi.id
             SET le.balance_bucket="eligible",le.available_at=UTC_TIMESTAMP()
             WHERE oi.vendor_group_id=:group_id AND le.balance_bucket="pending"
               AND EXISTS (
                   SELECT 1 FROM orders o
                   JOIN vendor_order_groups g2 ON g2.order_id=o.id
                   LEFT JOIN cod_collections c ON c.vendor_group_id=g2.id
                   WHERE g2.id=oi.vendor_group_id AND (o.payment_status="paid" OR c.status="collected")
               )'
<<<<<<< HEAD
        )->execute(['eligible_group' => $groupId]);

        // ── Award reward points to customer ──
        $ordStmt = $db->prepare('SELECT user_id, total_amount, order_number FROM orders WHERE id = :oid');
        $ordStmt->execute(['oid' => $current['order_id']]);
        $ord = $ordStmt->fetch();
        if ($ord) {
            $awarded = reward_earn_for_order(
                (string)$ord['user_id'],
                (int)$current['order_id'],
                (float)$ord['total_amount'],
                (string)$ord['order_number']
            );
            if ($awarded > 0) {
                create_notification(
                    'customer',
                    (string)$ord['user_id'],
                    'reward_earned',
                    'You earned reward points!',
                    $awarded . ' points added to your account.',
                    null,
                    ['order_id' => $current['order_id'], 'points' => $awarded]
                );
            }
        }
=======
        )->execute(['group_id' => $groupId]);
>>>>>>> origin/main
    }

    $openStmt = $db->prepare(
        'SELECT COUNT(*) FROM vendor_order_groups
         WHERE order_id=:order_id AND status NOT IN ("delivered","cancelled","rejected","returned_to_vendor")'
    );
    $openStmt->execute(['order_id' => $current['order_id']]);
    if ((int)$openStmt->fetchColumn() !== 0) return;

    $failedStmt = $db->prepare('SELECT COUNT(*) FROM vendor_order_groups WHERE order_id=:order_id AND status<>"delivered"');
    $failedStmt->execute(['order_id' => $current['order_id']]);
    $orderStatus = (int)$failedStmt->fetchColumn() === 0 ? 'delivered' : 'completed_with_exceptions';
    $db->prepare('UPDATE orders SET status=:status,updated_at=UTC_TIMESTAMP() WHERE id=:order_id')
       ->execute(['status' => $orderStatus, 'order_id' => $current['order_id']]);

    if ($orderStatus !== 'delivered') return;
    $paymentStmt = $db->prepare('SELECT user_id,payment_status FROM orders WHERE id=:order_id');
    $paymentStmt->execute(['order_id' => $current['order_id']]);
    $payment = $paymentStmt->fetch();
    if (!$payment || $payment['payment_status'] !== 'paid') return;

    $awarded = reward_award_if_eligible((int)$current['order_id']);
    if ($awarded > 0) {
        create_notification('customer', (string)$payment['user_id'], 'reward_earned',
            'You earned reward points!', $awarded . ' points added to your account.', null,
            ['order_id' => $current['order_id'], 'points' => $awarded]);
    }
    $db->prepare('UPDATE influencer_conversions SET status="eligible",eligible_at=UTC_TIMESTAMP() WHERE order_id=:order_id AND status="pending"')
       ->execute(['order_id' => $current['order_id']]);
    $db->prepare(
        'UPDATE influencer_ledger_entries le
         JOIN influencer_conversions c ON le.reference_type="conversion" AND le.reference_id=c.id
         SET le.status="eligible" WHERE c.order_id=:order_id AND le.status="pending"'
    )->execute(['order_id' => $current['order_id']]);
}
