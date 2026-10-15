<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';

send_cors_headers();

$payload = require_auth();
$userId  = auth_user_id($payload);
$db      = get_db();
$method  = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// ────────── GET: list ──────────
if ($method === 'GET') {
    $page   = max(1, (int)($_GET['page']  ?? 1));
    $limit  = min(50, max(1, (int)($_GET['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;
    $filter = strtolower(trim((string)($_GET['filter'] ?? 'all')));

    $where  = 'recipient_type = "customer" AND recipient_id = :uid AND deleted_at IS NULL';
    $params = ['uid' => $userId];

    if ($filter === 'unread') {
        $where .= ' AND read_at IS NULL';
    } elseif ($filter === 'read') {
        $where .= ' AND read_at IS NOT NULL';
    }

    $countStmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE $where");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    $stmt = $db->prepare(
        "SELECT id, type, title, message, action_url, metadata_json, read_at, created_at
         FROM notifications
         WHERE $where
         ORDER BY created_at DESC, id DESC
         LIMIT $limit OFFSET $offset"
    );
    $stmt->execute($params);

    $rows = [];
    foreach ($stmt->fetchAll() as $r) {
        $rows[] = [
            'id'        => $r['id'],
            'type'      => $r['type'],
            'title'     => $r['title'],
            'message'   => $r['message'],
            'actionUrl' => $r['action_url'],
            'metadata'  => $r['metadata_json'] ? json_decode((string)$r['metadata_json'], true) : null,
            'isRead'    => !empty($r['read_at']),
            'readAt'    => $r['read_at'],
            'createdAt' => $r['created_at'],
        ];
    }

    $unreadStmt = $db->prepare(
        'SELECT COUNT(*) FROM notifications
         WHERE recipient_type = "customer" AND recipient_id = ? AND read_at IS NULL AND deleted_at IS NULL'
    );
    $unreadStmt->execute([$userId]);
    $unreadCount = (int)$unreadStmt->fetchColumn();

    $totalPages = $total > 0 ? (int)ceil($total / $limit) : 0;

    json_response(200, [
        'success'       => true,
        'notifications' => $rows,
        'unreadCount'   => $unreadCount,
        'pagination'    => [
            'page' => $page, 'limit' => $limit, 'total' => $total,
            'totalPages' => $totalPages,
            'hasNext' => $page < $totalPages, 'hasPrev' => $page > 1,
        ],
    ]);
}

// ────────── All mutations require POST/DELETE + CSRF ──────────
if (!in_array($method, ['POST', 'DELETE'], true)) {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

require_csrf();

// ────────── DELETE: soft-delete single or bulk ──────────
if ($method === 'DELETE') {
    $id = trim((string)($_GET['id'] ?? ''));
    $all = !empty($_GET['all']);

    if ($all) {
        $db->prepare(
            'UPDATE notifications SET deleted_at = UTC_TIMESTAMP()
             WHERE recipient_type = "customer" AND recipient_id = ? AND deleted_at IS NULL'
        )->execute([$userId]);
        json_response(200, ['success' => true, 'message' => 'All notifications cleared.']);
    }

    if ($id === '') json_response(422, ['success' => false, 'message' => 'id or all=true required.']);

    $stmt = $db->prepare(
        'UPDATE notifications SET deleted_at = UTC_TIMESTAMP()
         WHERE id = ? AND recipient_type = "customer" AND recipient_id = ? AND deleted_at IS NULL'
    );
    $stmt->execute([$id, $userId]);

    if ($stmt->rowCount() === 0) {
        json_response(404, ['success' => false, 'message' => 'Notification not found.']);
    }

    json_response(200, ['success' => true, 'message' => 'Notification deleted.']);
}

// ────────── POST: bulk actions ──────────
$body   = get_json_body();
$action = strtolower(trim((string)($body['action'] ?? '')));
$ids    = $body['ids'] ?? [];

if (!is_array($ids)) $ids = [];
$ids = array_values(array_filter(array_map('strval', $ids)));

if (!in_array($action, ['mark_read', 'mark_unread'], true)) {
    json_response(422, ['success' => false, 'message' => 'Action must be mark_read or mark_unread.']);
}

if (empty($ids)) {
    // Bulk on all
    $setClause = $action === 'mark_read'
        ? 'read_at = UTC_TIMESTAMP()'
        : 'read_at = NULL';
    $db->prepare(
        "UPDATE notifications SET $setClause
         WHERE recipient_type = 'customer' AND recipient_id = ? AND deleted_at IS NULL"
    )->execute([$userId]);
    json_response(200, ['success' => true, 'message' => 'All notifications updated.']);
}

// Bulk by IDs
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$params = array_merge([$userId], $ids);

$setClause = $action === 'mark_read'
    ? 'read_at = UTC_TIMESTAMP()'
    : 'read_at = NULL';

$stmt = $db->prepare(
    "UPDATE notifications SET $setClause
     WHERE recipient_type = 'customer' AND recipient_id = ?
       AND id IN ($placeholders) AND deleted_at IS NULL"
);
$stmt->execute($params);

json_response(200, [
    'success' => true,
    'message' => $stmt->rowCount() . ' notification(s) updated.',
    'updated' => $stmt->rowCount(),
]);