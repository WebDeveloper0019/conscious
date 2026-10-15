<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/helpers.php';
require_once __DIR__ . '/lib/admin.php';
require_once __DIR__ . '/lib/notifications.php';
require_once __DIR__ . '/lib/audit.php';

send_cors_headers();

$principal = require_principal(['customer', 'vendor', 'admin', 'influencer']);
$db        = get_db();
$method    = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$isAdmin   = $principal['type'] === 'admin';

if ($isAdmin) require_permission('support.manage', ['admin']);

$ALLOWED_CATEGORIES = ['order', 'payment', 'product', 'account', 'other'];
$ALLOWED_STATUSES   = ['open', 'waiting_customer', 'waiting_vendor', 'resolved', 'closed'];
$ALLOWED_PRIORITIES = ['low', 'normal', 'high', 'urgent'];

// ────────── Ownership scope ──────────
$scope  = $isAdmin ? '1=1' : 'opened_by_type = :type AND opened_by_id = :owner';
$scopeP = $isAdmin ? []    : ['type' => $principal['type'], 'owner' => $principal['id']];

// ════════════════════════════════════════════════
//  GET
// ════════════════════════════════════════════════
if ($method === 'GET') {

    // ─── Stats ───
    if (isset($_GET['stats'])) {
        $stmt = $db->prepare(
            "SELECT
                SUM(status = 'open') AS open_count,
                SUM(status IN ('waiting_customer','waiting_vendor')) AS in_progress_count,
                SUM(status = 'resolved') AS resolved_count,
                SUM(status = 'closed') AS closed_count,
                COUNT(*) AS total
             FROM support_tickets
             WHERE $scope"
        );
        $stmt->execute($scopeP);
        $stats = $stmt->fetch() ?: [];

        json_response(200, [
            'success' => true,
            'stats'   => [
                'open'       => (int)($stats['open_count'] ?? 0),
                'inProgress' => (int)($stats['in_progress_count'] ?? 0),
                'resolved'   => (int)($stats['resolved_count'] ?? 0),
                'closed'     => (int)($stats['closed_count'] ?? 0),
                'total'      => (int)($stats['total'] ?? 0),
            ],
        ]);
    }

    // ─── Single ticket ───
    $id = trim((string)($_GET['id'] ?? ''));
    if ($id !== '') {
        $stmt = $db->prepare("SELECT * FROM support_tickets WHERE id = :id AND ($scope)");
        $stmt->execute(['id' => $id] + $scopeP);
        $ticket = $stmt->fetch();

        if (!$ticket) json_response(404, ['success' => false, 'message' => 'Support ticket not found.']);

                $msgStmt = $db->prepare(
            'SELECT id, sender_type, sender_id, message, is_internal, created_at
             FROM support_messages
             WHERE ticket_id = :ticket AND (is_internal = 0 OR :admin = 1)
             ORDER BY created_at ASC'
        );
        $msgStmt->execute(['ticket' => $id, 'admin' => $isAdmin ? 1 : 0]);

        // Fetch opener's name
        $nameMap = [
            'customer'   => ['table' => 'users',         'name' => "CONCAT(first_name,' ',last_name)"],
            'vendor'     => ['table' => 'vendors',       'name' => 'display_name'],
            'admin'      => ['table' => 'admin_users',   'name' => 'full_name'],
            'influencer' => ['table' => 'influencers',   'name' => 'display_name'],
        ];

           $messages = [];
        foreach ($msgStmt->fetchAll() as $m) {
            $senderName = '';
            $map = $nameMap[$m['sender_type']] ?? null;
            if ($map) {
                try {
                    $n = $db->prepare("SELECT {$map['name']} FROM {$map['table']} WHERE id = ? LIMIT 1");
                    $n->execute([$m['sender_id']]);
                    $senderName = (string)($n->fetchColumn() ?: '');
                    $n->closeCursor();
                } catch (Throwable $e) {}
            }

            // Attachments for this message
            $attStmt = $db->prepare(
                'SELECT sa.file_asset_id, f.original_name, f.mime_type, f.size_bytes, f.created_at
                 FROM support_attachments sa
                 JOIN file_assets f ON f.id = sa.file_asset_id
                 WHERE sa.message_id = ? AND f.deleted_at IS NULL
                 ORDER BY f.created_at ASC'
            );
            $attStmt->execute([$m['id']]);
            $atts = [];
            foreach ($attStmt->fetchAll() as $a) {
                $atts[] = [
                    'fileId'       => $a['file_asset_id'],
                    'url'          => '../files.php?id=' . urlencode((string)$a['file_asset_id']),
                    'originalName' => $a['original_name'],
                    'mimeType'     => $a['mime_type'],
                    'sizeBytes'    => (int)$a['size_bytes'],
                    'uploadedAt'   => $a['created_at'],
                ];
            }

            $messages[] = [
                'id'          => $m['id'],
                'senderType'  => $m['sender_type'],
                'senderName'  => $senderName ?: ucfirst($m['sender_type']),
                'message'     => $m['message'],
                'isInternal'  => (bool)$m['is_internal'],
                'createdAt'   => $m['created_at'],
                'attachments' => $atts,
            ];
        }

        $ticket['messages'] = $messages;
        json_response(200, ['success' => true, 'ticket' => $ticket]);
               // Fetch attachments for this ticket
        $attStmt = $db->prepare(
            'SELECT sa.id, sa.file_asset_id, sa.uploaded_by_type, sa.created_at,
                    f.original_name, f.mime_type, f.size_bytes
             FROM support_attachments sa
             JOIN file_assets f ON f.id = sa.file_asset_id
             WHERE sa.ticket_id = ? AND f.deleted_at IS NULL
             ORDER BY sa.created_at ASC'
        );
        $attStmt->execute([$id]);

        $attachments = [];
        foreach ($attStmt->fetchAll() as $a) {
            $attachments[] = [
                'id'           => $a['id'],
                'fileId'       => $a['file_asset_id'],
                'url'          => '../files.php?id=' . urlencode((string)$a['file_asset_id']),
                'originalName' => $a['original_name'],
                'mimeType'     => $a['mime_type'],
                'sizeBytes'    => (int)$a['size_bytes'],
                'uploadedBy'   => $a['uploaded_by_type'],
                'uploadedAt'   => $a['created_at'],
            ];
        }

        $ticket['messages']    = $messages;
        $ticket['attachments'] = $attachments;
        json_response(200, ['success' => true, 'ticket' => $ticket]);
    }

    // ─── List ───
    $page   = max(1, (int)($_GET['page']  ?? 1));
    $limit  = min(100, max(1, (int)($_GET['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;

    $search = trim((string)($_GET['search'] ?? ''));
    $status = strtolower(trim((string)($_GET['status'] ?? '')));

    $where  = $scope;
    $params = $scopeP;

    if ($status !== '' && $status !== 'all') {
        if (!in_array($status, $ALLOWED_STATUSES, true)) {
            json_response(422, ['success' => false, 'message' => 'Invalid status filter.']);
        }
        $where .= ' AND status = :status';
        $params['status'] = $status;
    }

    if ($search !== '') {
        if (strlen($search) > 100) json_response(422, ['success' => false, 'message' => 'Search too long.']);
        $where .= ' AND (subject LIKE :s OR ticket_number LIKE :s)';
        $params['s'] = '%' . $search . '%';
    }

    $cnt = $db->prepare("SELECT COUNT(*) FROM support_tickets WHERE $where");
    $cnt->execute($params);
    $total = (int)$cnt->fetchColumn();
    $cnt->closeCursor();

    $stmt = $db->prepare(
        "SELECT * FROM support_tickets
         WHERE $where
         ORDER BY FIELD(status,'open','waiting_customer','waiting_vendor','resolved','closed'),
                  updated_at DESC
         LIMIT $limit OFFSET $offset"
    );
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    // Enrich each with last message + message count
    foreach ($rows as &$r) {
        $last = $db->prepare('SELECT message, created_at FROM support_messages WHERE ticket_id = ? AND is_internal = 0 ORDER BY created_at DESC LIMIT 1');
        $last->execute([$r['id']]);
        $lastMsg = $last->fetch();
        $r['lastMessage'] = $lastMsg ? $lastMsg['message'] : null;
        $r['lastMessageAt'] = $lastMsg ? $lastMsg['created_at'] : null;
        $last->closeCursor();

        $c = $db->prepare('SELECT COUNT(*) FROM support_messages WHERE ticket_id = ? AND is_internal = 0');
        $c->execute([$r['id']]);
        $r['messageCount'] = (int)$c->fetchColumn();
        $c->closeCursor();
    }
    unset($r);

    $totalPages = $total > 0 ? (int)ceil($total / $limit) : 0;

    json_response(200, [
        'success' => true,
        'tickets' => $rows,
        'pagination' => [
            'page' => $page, 'limit' => $limit, 'total' => $total,
            'totalPages' => $totalPages,
            'hasNext' => $page < $totalPages, 'hasPrev' => $page > 1,
        ],
    ]);
}

// ════════════════════════════════════════════════
//  POST — create ticket OR reply to existing
// ════════════════════════════════════════════════
if ($method === 'POST') {
    $body     = get_json_body();
    $ticketId = trim((string)($body['ticket_id'] ?? ''));
    $message  = trim((string)($body['message']  ?? ''));

    if (mb_strlen($message) < 10 || mb_strlen($message) > 5000) {
        json_response(422, ['success' => false, 'message' => 'Message must be between 10 and 5000 characters.']);
    }

    // ─── Create new ticket ───
    if ($ticketId === '') {
        $subject  = trim((string)($body['subject']  ?? ''));
        $category = trim((string)($body['category'] ?? ''));

        if (mb_strlen($subject) < 5 || mb_strlen($subject) > 180) {
            json_response(422, ['success' => false, 'message' => 'Subject must be 5-180 characters.']);
        }
        if (!in_array($category, $ALLOWED_CATEGORIES, true)) {
            json_response(422, ['success' => false, 'message' => 'Invalid category.']);
        }

        $priority = in_array($body['priority'] ?? 'normal', $ALLOWED_PRIORITIES, true)
            ? $body['priority'] : 'normal';

        $ticketId = uuid_string();
        $number   = 'SUP-' . gmdate('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

        $db->beginTransaction();
        try {
            $db->prepare(
                'INSERT INTO support_tickets
                   (id, ticket_number, opened_by_type, opened_by_id,
                    order_id, vendor_id, category, subject, priority, status,
                    first_response_due_at, resolution_due_at, created_at, updated_at)
                 VALUES
                   (:id, :number, :type, :owner,
                    :order, :vendor, :category, :subject, :priority, "open",
                    DATE_ADD(UTC_TIMESTAMP(), INTERVAL 8 HOUR),
                    DATE_ADD(UTC_TIMESTAMP(), INTERVAL 3 DAY),
                    UTC_TIMESTAMP(), UTC_TIMESTAMP())'
            )->execute([
                'id'       => $ticketId,
                'number'   => $number,
                'type'     => $principal['type'],
                'owner'    => $principal['id'],
                'order'    => isset($body['order_id'])  ? (int)$body['order_id'] : null,
                'vendor'   => $body['vendor_id'] ?? null,
                'category' => $category,
                'subject'  => $subject,
                'priority' => $priority,
            ]);
            $firstMessageId = uuid_string();
            $db->prepare(
                'INSERT INTO support_messages (id, ticket_id, sender_type, sender_id, message, is_internal, created_at)
                 VALUES (:id, :ticket, :type, :owner, :message, 0, UTC_TIMESTAMP())'
            )->execute([
                'id'      => $firstMessageId,
                'ticket'  => $ticketId,
                'type'    => $principal['type'],
                'owner'   => $principal['id'],
                'message' => $message,
            ]);

            audit_event('support.ticket_opened', 'support_ticket', $ticketId, [
                'category' => $category,
                'priority' => $priority,
            ]);

            $db->commit();

            json_response(201, [
                'success'       => true,
                'message'       => 'Support ticket created.',
                'ticket_id'     => $ticketId,
                'ticket_number' => $number,
                                'message_id'    => $firstMessageId,

            ]);
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }
    }

    // ─── Reply to existing ticket ───
    $scope  = $isAdmin ? '1=1' : 'opened_by_type = :type AND opened_by_id = :owner';
    $params = ['id' => $ticketId] + ($isAdmin ? [] : ['type' => $principal['type'], 'owner' => $principal['id']]);

    $stmt = $db->prepare("SELECT * FROM support_tickets WHERE id = :id AND ($scope)");
    $stmt->execute($params);
    $ticket = $stmt->fetch();

    if (!$ticket) json_response(404, ['success' => false, 'message' => 'Support ticket not found.']);
    if (in_array((string)$ticket['status'], ['closed', 'resolved'], true) && !$isAdmin) {
        json_response(409, ['success' => false, 'message' => 'This ticket is closed. Open a new one for further help.']);
    }

    $internal = ($isAdmin && !empty($body['is_internal'])) ? 1 : 0;
    $replyMessageId = uuid_string();

    $db->prepare(
        'INSERT INTO support_messages (id, ticket_id, sender_type, sender_id, message, is_internal, created_at)
         VALUES (:id, :ticket, :type, :owner, :message, :internal, UTC_TIMESTAMP())'
    )->execute([
        'id'       => uuid_string(),
        'ticket'   => $ticketId,
        'type'     => $principal['type'],
        'owner'    => $principal['id'],
        'message'  => $message,
        'internal' => $internal,
    ]);

    // Update status
    $newStatus = $isAdmin
        ? ($internal ? $ticket['status'] : 'waiting_customer')
        : 'open';

    // Set first_response_at if admin replies and it's null
    $extra = '';
    if ($isAdmin && !$internal && empty($ticket['first_response_at'])) {
        $extra = ', first_response_at = UTC_TIMESTAMP()';
    }

    $db->prepare("UPDATE support_tickets SET status = :status{$extra}, updated_at = UTC_TIMESTAMP() WHERE id = :id")
       ->execute(['status' => $newStatus, 'id' => $ticketId]);

    if (!$internal && !$isAdmin && !empty($ticket['assigned_admin_id'])) {
        create_notification(
            'admin',
            $ticket['assigned_admin_id'],
            'support_reply',
            'Support ticket reply',
            "{$ticket['ticket_number']} received a reply.",
            '../support.php',
            ['ticket_id' => $ticketId]
        );
    }

    json_response(201, ['success' => true, 'message' => 'Reply added.', 'message_id' => $replyMessageId,]);
}

// ════════════════════════════════════════════════
//  PATCH — admin only: update status/priority/assign
// ════════════════════════════════════════════════
if ($method === 'PATCH' && $isAdmin) {
    $body   = get_json_body();
    $id     = trim((string)($body['ticket_id'] ?? ''));
    $status = trim((string)($body['status']    ?? ''));

    if ($id === '') json_response(422, ['success' => false, 'message' => 'ticket_id required.']);
    if (!in_array($status, $ALLOWED_STATUSES, true)) {
        json_response(422, ['success' => false, 'message' => 'Invalid ticket status.']);
    }

    $priority = in_array($body['priority'] ?? '', $ALLOWED_PRIORITIES, true) ? $body['priority'] : null;
    $assigned = !empty($body['assigned_admin_id']) ? $body['assigned_admin_id'] : $principal['id'];

    $db->prepare(
        'UPDATE support_tickets
         SET status = :status,
             priority = COALESCE(:priority, priority),
             assigned_admin_id = COALESCE(:admin, assigned_admin_id),
             closed_at = IF(:status = "closed", UTC_TIMESTAMP(), closed_at),
             updated_at = UTC_TIMESTAMP()
         WHERE id = :id'
    )->execute([
        'status'   => $status,
        'priority' => $priority,
        'admin'    => $assigned,
        'id'       => $id,
    ]);

    audit_event('admin.support_ticket_updated', 'support_ticket', $id, ['status' => $status]);

    json_response(200, ['success' => true, 'message' => 'Support ticket updated.']);
}

json_response(405, ['success' => false, 'message' => 'Method not allowed.']);