<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/admin.php';
require_once __DIR__ . '/../lib/db.php';

send_cors_headers();

require_admin('support.manage');
$db = get_db();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

$minLevel = max(0, (int)($_GET['min_level'] ?? 1));
$limit    = min(200, max(1, (int)($_GET['limit'] ?? 50)));

// Tickets currently in SLA breach
$stmt = $db->prepare(
    'SELECT t.id, t.ticket_number, t.subject, t.priority, t.status,
            t.assigned_admin_id, t.created_at,
            t.first_response_due_at, t.first_response_at, t.first_response_breached_at,
            t.resolution_due_at, t.resolution_breached_at,
            t.sla_breach_level,
            TIMESTAMPDIFF(MINUTE, t.first_response_due_at, UTC_TIMESTAMP()) AS first_overdue_min,
            TIMESTAMPDIFF(MINUTE, t.resolution_due_at, UTC_TIMESTAMP()) AS resolution_overdue_min,
            u.display_name AS assignee_name
     FROM support_tickets t
     LEFT JOIN admin_users u ON u.id = t.assigned_admin_id
     WHERE t.sla_breach_level >= ?
       AND t.status NOT IN ("resolved", "closed")
     ORDER BY t.sla_breach_level DESC, t.priority ASC,
              COALESCE(t.first_response_due_at, t.resolution_due_at) ASC
     LIMIT ' . $limit
);
$stmt->execute([$minLevel]);
$tickets = $stmt->fetchAll();

$out = [];
foreach ($tickets as $t) {
    $breaches = [];
    if ((int)$t['first_overdue_min'] > 0 && $t['first_response_at'] === null) {
        $breaches[] = [
            'type'           => 'first_response',
            'overdueMinutes' => (int)$t['first_overdue_min'],
            'breachedAt'     => $t['first_response_breached_at'],
        ];
    }
    if ((int)$t['resolution_overdue_min'] > 0) {
        $breaches[] = [
            'type'           => 'resolution',
            'overdueMinutes' => (int)$t['resolution_overdue_min'],
            'breachedAt'     => $t['resolution_breached_at'],
        ];
    }

    $out[] = [
        'ticketId'      => $t['id'],
        'ticketNumber'  => $t['ticket_number'],
        'subject'       => $t['subject'],
        'priority'      => $t['priority'],
        'status'        => $t['status'],
        'breachLevel'   => (int)$t['sla_breach_level'],
        'assigneeName'  => $t['assignee_name'],
        'createdAt'     => $t['created_at'],
        'breaches'      => $breaches,
    ];
}

// Counts by level for dashboard badges
$countsStmt = $db->query(
    'SELECT sla_breach_level AS lvl, COUNT(*) AS c
     FROM support_tickets
     WHERE sla_breach_level > 0 AND status NOT IN ("resolved", "closed")
     GROUP BY sla_breach_level'
);
$counts = [1 => 0, 2 => 0, 3 => 0];
foreach ($countsStmt->fetchAll() as $row) {
    $counts[(int)$row['lvl']] = (int)$row['c'];
}

json_response(200, [
    'success' => true,
    'tickets' => $out,
    'counts'  => [
        'total'    => $counts[1] + $counts[2] + $counts[3],
        'level1'   => $counts[1],
        'level2'   => $counts[2],
        'level3'   => $counts[3],
    ],
]);