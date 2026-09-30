<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/fulfilment-safe.php';

function vendor_shipments_v2(): void
{
    send_cors_headers();
    $context = require_vendor(true);
    $vendorId = $context['vendor']['id'];
    $db = get_db();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'GET') {
        $groupId = trim((string)($_GET['group_id'] ?? ''));
        if ($groupId === '') {
            json_response(422, ['success' => false, 'message' => 'A vendor order group is required.']);
        }
        vendor_group($groupId, $vendorId);
        $packagesStmt = $db->prepare('SELECT * FROM shipment_packages WHERE vendor_group_id=:packages_group ORDER BY created_at');
        $packagesStmt->execute(['packages_group' => $groupId]);
        $result = [];
        foreach ($packagesStmt->fetchAll() as $package) {
            $eventsStmt = $db->prepare('SELECT * FROM shipment_events WHERE package_id=:events_package ORDER BY event_at DESC');
            $eventsStmt->execute(['events_package' => $package['id']]);
            $itemsStmt = $db->prepare(
                'SELECT spi.order_item_id,spi.quantity,oi.product_name,oi.sku
                 FROM shipment_package_items spi JOIN order_items oi ON oi.id=spi.order_item_id
                 WHERE spi.package_id=:items_package'
            );
            $itemsStmt->execute(['items_package' => $package['id']]);
            $package['events'] = $eventsStmt->fetchAll();
            $package['items'] = $itemsStmt->fetchAll();
            $result[] = $package;
        }
        json_response(200, ['success' => true, 'packages' => $result]);
    }

    if ($method === 'POST') {
        $body = get_json_body();
        $groupId = trim((string)($body['group_id'] ?? ''));
        $items = (array)($body['items'] ?? []);
        $courier = trim((string)($body['courier_name'] ?? ''));
        $tracking = trim((string)($body['tracking_reference'] ?? ''));
        if ($groupId === '' || !$items || mb_strlen($courier) < 2 || mb_strlen($tracking) < 2) {
            json_response(422, ['success' => false, 'message' => 'Package items, courier name and tracking reference are required.']);
        }
        $idempotency = begin_idempotent_request('vendor.shipment.create', ['type' => 'vendor', 'id' => $vendorId], $body);
        $db->beginTransaction();
        try {
            $group = vendor_group($groupId, $vendorId, true);
            if (!in_array($group['status'], ['packing', 'ready_to_dispatch', 'partially_dispatched'], true)) {
                json_response(409, ['success' => false, 'message' => 'Order is not ready for package creation.']);
            }
            $packageId = uuid_string();
            $packageNumber = $group['group_number'] . '-P' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
            $db->prepare(
                'INSERT INTO shipment_packages
                 (id,vendor_group_id,package_number,status,courier_name,tracking_reference,tracking_url,vendor_shipping_cost,weight_grams,length_cm,width_cm,height_cm,expected_delivery_date,created_at,updated_at)
                 VALUES (:package_id,:package_group,:package_number,"ready_to_dispatch",:package_courier,:package_tracking,:package_url,:package_cost,:package_weight,:package_length,:package_width,:package_height,:package_expected,UTC_TIMESTAMP(),UTC_TIMESTAMP())'
            )->execute([
                'package_id' => $packageId, 'package_group' => $groupId, 'package_number' => $packageNumber,
                'package_courier' => $courier, 'package_tracking' => $tracking,
                'package_url' => trim((string)($body['tracking_url'] ?? '')) ?: null,
                'package_cost' => isset($body['vendor_shipping_cost']) ? max(0, (float)$body['vendor_shipping_cost']) : null,
                'package_weight' => isset($body['weight_grams']) ? max(1, (int)$body['weight_grams']) : null,
                'package_length' => $body['length_cm'] ?? null, 'package_width' => $body['width_cm'] ?? null,
                'package_height' => $body['height_cm'] ?? null, 'package_expected' => $body['expected_delivery_date'] ?? null,
            ]);
            foreach ($items as $entry) {
                $itemId = (int)($entry['order_item_id'] ?? 0);
                $quantity = (int)($entry['quantity'] ?? 0);
                $check = $db->prepare(
                    'SELECT oi.quantity,
                            COALESCE((SELECT SUM(spi.quantity) FROM shipment_package_items spi WHERE spi.order_item_id=oi.id),0) AS packed
                     FROM order_items oi WHERE oi.id=:check_item AND oi.vendor_group_id=:check_group FOR UPDATE'
                );
                $check->execute(['check_item' => $itemId, 'check_group' => $groupId]);
                $row = $check->fetch();
                if (!$row || $quantity < 1 || $quantity > (int)$row['quantity'] - (int)$row['packed']) {
                    json_response(422, ['success' => false, 'message' => 'A package item quantity is invalid.']);
                }
                $db->prepare(
                    'INSERT INTO shipment_package_items (package_id,order_item_id,quantity)
                     VALUES (:package_item_package,:package_item_order,:package_item_quantity)'
                )->execute([
                    'package_item_package' => $packageId, 'package_item_order' => $itemId, 'package_item_quantity' => $quantity,
                ]);
            }
            $db->prepare(
                'INSERT INTO shipment_events (id,package_id,status,event_at,note,actor_type,actor_id,created_at)
                 VALUES (:event_id,:event_package,"ready_to_dispatch",UTC_TIMESTAMP(),"Package created by vendor","vendor",:event_actor,UTC_TIMESTAMP())'
            )->execute(['event_id' => uuid_string(), 'event_package' => $packageId, 'event_actor' => $vendorId]);
            $response = [
                'success' => true, 'message' => 'Package created. Add each delivery milestone manually.',
                'package_id' => $packageId, 'package_number' => $packageNumber,
            ];
            finish_idempotent_request((int)$idempotency['id'], 201, $response);
            $db->commit();
            audit_event('vendor.package_created', 'shipment_package', $packageId, ['group_id' => $groupId]);
            json_response(201, $response);
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    if ($method === 'PATCH') {
        $body = get_json_body();
        $packageId = trim((string)($body['package_id'] ?? ''));
        $nextStatus = trim((string)($body['status'] ?? ''));
        $eventAtInput = trim((string)($body['event_at'] ?? ''));
        $note = trim((string)($body['note'] ?? ''));
        $location = trim((string)($body['location'] ?? ''));
        $eventTime = $eventAtInput !== '' ? strtotime($eventAtInput) : time();
        if ($packageId === '' || $eventTime === false || $eventTime > time() + 300) {
            json_response(422, ['success' => false, 'message' => 'Package and milestone time are required.']);
        }
        $idempotency = begin_idempotent_request('vendor.shipment.update', ['type' => 'vendor', 'id' => $vendorId], $body);
        $db->beginTransaction();
        try {
            $stmt = $db->prepare(
                'SELECT sp.*,g.vendor_id,o.user_id,o.order_number,g.order_id
                 FROM shipment_packages sp
                 JOIN vendor_order_groups g ON g.id=sp.vendor_group_id
                 JOIN orders o ON o.id=g.order_id
                 WHERE sp.id=:package_id AND g.vendor_id=:package_vendor FOR UPDATE'
            );
            $stmt->execute(['package_id' => $packageId, 'package_vendor' => $vendorId]);
            $package = $stmt->fetch();
            if (!$package) {
                json_response(404, ['success' => false, 'message' => 'Package not found.']);
            }
            assert_transition($package['status'], $nextStatus, package_transition_map());
            $lastStmt = $db->prepare('SELECT MAX(event_at) FROM shipment_events WHERE package_id=:last_package');
            $lastStmt->execute(['last_package' => $packageId]);
            $lastAt = $lastStmt->fetchColumn();
            if ($lastAt && $eventTime < strtotime((string)$lastAt)) {
                json_response(422, ['success' => false, 'message' => 'A milestone cannot be earlier than the previous milestone.']);
            }
            if (in_array($nextStatus, ['delivery_attempted', 'delivery_failed', 'return_in_transit', 'returned_to_vendor'], true) && mb_strlen($note) < 10) {
                json_response(422, ['success' => false, 'message' => 'A detailed note is required for this milestone.']);
            }
            $eventTimestamp = gmdate('Y-m-d H:i:s', $eventTime);
            $db->prepare(
                'INSERT INTO shipment_events (id,package_id,status,event_at,location,note,actor_type,actor_id,created_at)
                 VALUES (:event_id,:event_package,:event_status,:event_at,:event_location,:event_note,"vendor",:event_actor,UTC_TIMESTAMP())'
            )->execute([
                'event_id' => uuid_string(), 'event_package' => $packageId, 'event_status' => $nextStatus,
                'event_at' => $eventTimestamp, 'event_location' => $location ?: null, 'event_note' => $note ?: null,
                'event_actor' => $vendorId,
            ]);
            $db->prepare(
                'UPDATE shipment_packages
                 SET status=:package_status,
                     dispatched_at=IF(:dispatched_status="dispatched",COALESCE(dispatched_at,:package_event_at),dispatched_at),
                     delivered_at=IF(:delivered_status="delivered",:delivered_event_at,delivered_at),updated_at=UTC_TIMESTAMP()
                 WHERE id=:update_package'
            )->execute([
                'package_status' => $nextStatus, 'dispatched_status' => $nextStatus, 'package_event_at' => $eventTimestamp,
                'delivered_status' => $nextStatus, 'delivered_event_at' => $eventTimestamp, 'update_package' => $packageId,
            ]);
            sync_group_from_packages_safe($package['vendor_group_id']);
            record_status_change('shipment_package', $packageId, $package['status'], $nextStatus, $note ?: null, ['location' => $location]);
            create_notification('customer', $package['user_id'], 'shipment_status', 'Delivery update', 'Package ' . $package['package_number'] . ' is now ' . str_replace('_', ' ', $nextStatus) . '.', '../tracking.php?order_id=' . $package['order_id'], ['package_id' => $packageId]);
            audit_event('vendor.shipment_status_changed', 'shipment_package', $packageId, ['from' => $package['status'], 'to' => $nextStatus, 'event_at' => gmdate(DATE_ATOM, $eventTime)]);
            $response = ['success' => true, 'message' => 'Manual delivery milestone recorded.', 'status' => $nextStatus];
            finish_idempotent_request((int)$idempotency['id'], 200, $response);
            $db->commit();
            json_response(200, $response);
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}
