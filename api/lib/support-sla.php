<?php
declare(strict_types=1);
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/admin-notify.php';

function sla_config(string $key, $default = null)
{
    static $cache = [];
    if (array_key_exists($key, $cache)) return $cache[$key];
    $stmt = get_db()->prepare('SELECT value_json FROM platform_settings WHERE setting_key = ?');
    $stmt->execute([$key]);
    $raw = $stmt->fetchColumn();
    if ($raw === false) return $cache[$key] = $default;
    $decoded = json_decode((string)$raw, true);
    return $cache[$key] = ($decoded['value'] ?? $default);
}

/**
 * Compute escalation level (1/2/3) from overdue minutes + breach type.
 * Returns 0 if not yet crossed warning threshold.
 */
function sla_compute_level(string $breachType, int $overdueMinutes): int
{
    $prefix = $breachType === 'first_response' ? 'support_sla_first_' : 'support_sla_resolution_';
    $warn     = (int)sla_config($prefix . 'warning_minutes',  15);
    $urgent   = (int)sla_config($prefix . 'urgent_minutes',   60);
    $critical = (int)sla_config($prefix . 'critical_minutes', 240);

    if ($overdueMinutes >= $critical) return 3;
    if ($overdueMinutes >= $urgent)   return 2;
    if ($overdueMinutes >= $warn)     return 1;
    return 0;
}

/**
 * Idempotent escalation for one (ticket, breach_type, level).
 * Returns ['escalated' => bool, 'alert_id' => ..., 'task_id' => ..., 'reason' => ?string].
 */
function sla_escalate_ticket(array $ticket, string $breachType, int $level, int $overdueMinutes): array
{
    $db = get_db();

    // Skip level 0 (below warning)
    if ($level < 1) {
        return ['escalated' => false, 'reason' => 'below_threshold'];
    }

    // Idempotency check
    $chk = $db->prepare(
        'SELECT id, alert_id, task_id FROM support_sla_escalations
         WHERE ticket_id = ? AND breach_type = ? AND level = ? LIMIT 1'
    );
    $chk->execute([$ticket['id'], $breachType, $level]);
    if ($existing = $chk->fetch()) {
        return [
            'escalated' => false,
            'reason'    => 'already_escalated',
            'alert_id'  => $existing['alert_id'],
            'task_id'   => $existing['task_id'],
        ];
    }

    $db->beginTransaction();
    try {
        $severity = ['1' => 'warning', '2' => 'critical', '3' => 'critical'][(string)$level] ?? 'warning';
        $label    = ['1' => 'warning', '2' => 'urgent', '3' => 'critical'][(string)$level];
        $scope    = $breachType === 'first_response' ? 'first response' : 'resolution';
        $hours    = round($overdueMinutes / 60, 1);

        $title   = strtoupper($label) . ' — Support SLA breach: ' . $ticket['ticket_number'];
        $message = sprintf(
            'Ticket "%s" (%s priority) is %.1fh overdue on %s SLA.',
            mb_substr((string)$ticket['subject'], 0, 80),
            $ticket['priority'],
            $hours,
            $scope
        );

        // Create admin alert
        $alertId = admin_create_alert(
            'support_sla_' . $breachType . '_level' . $level,
            $title,
            $message,
            $severity,
            'support_ticket',
            $ticket['id'],
            [
                'ticket_number'   => $ticket['ticket_number'],
                'breach_type'     => $breachType,
                'level'           => $level,
                'overdue_minutes' => $overdueMinutes,
                'priority'        => $ticket['priority'],
                'assigned_admin'  => $ticket['assigned_admin_id'] ?? null,
            ]
        );

        // Create admin task only for level >= 2
        $taskId = null;
        if ($level >= 2) {
            $taskId = admin_create_task(
                'support_sla_breach',
                'Follow up: ' . $ticket['ticket_number'] . ' — ' . $scope . ' overdue',
                "Ticket: {$ticket['ticket_number']}\n"
                  . "Subject: {$ticket['subject']}\n"
                  . "Priority: {$ticket['priority']}\n"
                  . "Breach: {$scope} overdue by " . round($overdueMinutes / 60, 1) . " hours.\n"
                  . "Level: {$label}\n\n"
                  . "Action: Respond or escalate to manager immediately.",
                $level === 3 ? 'urgent' : 'high',
                'support_ticket',
                $ticket['id'],
                ['breach_type' => $breachType, 'level' => $level]
            );
        }

        // Log escalation
        $db->prepare(
            'INSERT INTO support_sla_escalations
               (id, ticket_id, breach_type, level, overdue_minutes,
                notified_admin_id, alert_id, task_id, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, UTC_TIMESTAMP())'
        )->execute([
            uuid_string(),
            $ticket['id'],
            $breachType,
            $level,
            $overdueMinutes,
            $ticket['assigned_admin_id'] ?? null,
            $alertId,
            $taskId,
        ]);

        // Update ticket's max breach level + timestamp
        $breachCol = $breachType === 'first_response' ? 'first_response_breached_at' : 'resolution_breached_at';
        $db->prepare(
            "UPDATE support_tickets
             SET sla_breach_level = GREATEST(sla_breach_level, ?),
                 {$breachCol} = COALESCE({$breachCol}, UTC_TIMESTAMP()),
                 updated_at = UTC_TIMESTAMP()
             WHERE id = ?"
        )->execute([$level, $ticket['id']]);

        $db->commit();

        return ['escalated' => true, 'alert_id' => $alertId, 'task_id' => $taskId, 'level' => $level];

    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        error_log("SLA escalation failed for ticket {$ticket['id']}: " . $e->getMessage());
        return ['escalated' => false, 'reason' => 'error', 'message' => $e->getMessage()];
    }
}