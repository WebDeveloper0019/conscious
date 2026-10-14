<?php
declare(strict_types=1);
require_once __DIR__ . '/helpers.php';

/**
 * Create an admin task. Idempotent via (task_type + subject_type + subject_id + status=open)
 * to prevent duplicate tasks for the same incident.
 */
function admin_create_task(
    string  $taskType,
    string  $title,
    string  $description = '',
    string  $priority = 'normal',
    ?string $subjectType = null,
    ?string $subjectId = null,
    array   $payload = []
): ?string {
    $db = get_db();

    // Idempotent check — one open task per (type, subject)
    if ($subjectType !== null && $subjectId !== null) {
        $chk = $db->prepare(
            'SELECT id FROM admin_tasks
             WHERE task_type = ? AND subject_type = ? AND subject_id = ?
               AND status IN ("open", "in_progress")
             LIMIT 1'
        );
        $chk->execute([$taskType, $subjectType, $subjectId]);
        $existing = $chk->fetchColumn();
        if ($existing !== false) return (string)$existing;
    }

    $id = uuid_string();
    try {
        $db->prepare(
            'INSERT INTO admin_tasks
               (id, task_type, priority, title, description,
                subject_type, subject_id, payload_json,
                status, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, "open", UTC_TIMESTAMP(), UTC_TIMESTAMP())'
        )->execute([
            $id,
            $taskType,
            $priority,
            mb_substr($title, 0, 255),
            $description,
            $subjectType,
            $subjectId,
            $payload ? json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null,
        ]);
        return $id;
    } catch (Throwable $e) {
        error_log('admin_create_task failed: ' . $e->getMessage());
        return null;
    }
}

/**
 * Create an admin alert. Idempotent via (alert_type + subject_type + subject_id + status=unread).
 */
function admin_create_alert(
    string  $alertType,
    string  $title,
    string  $message,
    string  $severity = 'warning',
    ?string $subjectType = null,
    ?string $subjectId = null,
    array   $payload = []
): ?string {
    $db = get_db();

    // Idempotent check
    if ($subjectType !== null && $subjectId !== null) {
        $chk = $db->prepare(
            'SELECT id FROM admin_alerts
             WHERE alert_type = ? AND subject_type = ? AND subject_id = ?
               AND status = "unread"
             LIMIT 1'
        );
        $chk->execute([$alertType, $subjectType, $subjectId]);
        $existing = $chk->fetchColumn();
        if ($existing !== false) return (string)$existing;
    }

    $id = uuid_string();
    try {
        $db->prepare(
            'INSERT INTO admin_alerts
               (id, alert_type, severity, title, message,
                subject_type, subject_id, payload_json, status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, "unread", UTC_TIMESTAMP())'
        )->execute([
            $id,
            $alertType,
            $severity,
            mb_substr($title, 0, 255),
            mb_substr($message, 0, 500),
            $subjectType,
            $subjectId,
            $payload ? json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null,
        ]);
        return $id;
    } catch (Throwable $e) {
        error_log('admin_create_alert failed: ' . $e->getMessage());
        return null;
    }
}