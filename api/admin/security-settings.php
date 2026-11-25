<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/admin.php';
require_once __DIR__ . '/../lib/malware-scan.php';

send_cors_headers();
$context = require_admin('settings.manage');
$db = get_db();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

function security_setting_counts(PDO $db): array
{
    $stmt = $db->query(
        'SELECT f.scan_status, COUNT(*) AS total
         FROM file_assets f
         WHERE f.deleted_at IS NULL AND f.scan_status IN ("pending","bypassed")
         GROUP BY f.scan_status'
    );
    $counts = ['pending' => 0, 'bypassed' => 0];
    foreach ($stmt->fetchAll() as $row) {
        $counts[(string) $row['scan_status']] = (int) $row['total'];
    }
    return $counts;
}

if ($method === 'GET') {
    json_response(200, [
        'success' => true,
        'environment' => APP_ENV,
        'bypass_available' => malware_scan_bypass_available(),
        'bypass_enabled' => malware_scan_bypass_enabled($db),
        'counts' => security_setting_counts($db),
    ]);
}

if ($method !== 'POST') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

require_csrf();
$body = get_json_body();
$enabled = filter_var($body['malware_scan_bypass'] ?? false, FILTER_VALIDATE_BOOL);
if ($enabled && !malware_scan_bypass_available()) {
    json_response(403, [
        'success' => false,
        'message' => 'Malware scanning cannot be disabled outside the local development environment.',
    ]);
}

$db->beginTransaction();
try {
    $db->prepare(
        'INSERT INTO platform_settings (setting_key,value_json,is_public,updated_by,updated_at)
         VALUES (:setting_key,:value_json,0,:updated_by,UTC_TIMESTAMP())
         ON DUPLICATE KEY UPDATE value_json=VALUES(value_json),is_public=0,updated_by=VALUES(updated_by),updated_at=UTC_TIMESTAMP()'
    )->execute([
        'setting_key' => LOCAL_MALWARE_SCAN_BYPASS_KEY,
        'value_json' => json_encode(['value' => $enabled], JSON_THROW_ON_ERROR),
        'updated_by' => $context['admin']['id'],
    ]);

    if ($enabled) {
        $changed = $db->exec(
            'UPDATE file_assets f
             SET f.scan_status="bypassed"
             WHERE f.deleted_at IS NULL AND f.scan_status="pending"'
        );
    } else {
        $changed = $db->exec(
            'UPDATE file_assets f
             SET f.scan_status="pending"
             WHERE f.deleted_at IS NULL AND f.scan_status="bypassed"'
        );
    }

    audit_event('admin.local_malware_scan_bypass_updated', 'platform_settings', LOCAL_MALWARE_SCAN_BYPASS_KEY, [
        'enabled' => $enabled,
        'files_changed' => (int) $changed,
        'environment' => APP_ENV,
    ]);
    $db->commit();
} catch (Throwable $error) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    throw $error;
}

json_response(200, [
    'success' => true,
    'message' => $enabled
        ? 'Local scan bypass enabled. All uploaded files are explicitly marked unscanned.'
        : 'Local scan bypass disabled. Unscanned files are queued for ClamAV again.',
    'environment' => APP_ENV,
    'bypass_available' => malware_scan_bypass_available(),
    'bypass_enabled' => malware_scan_bypass_enabled($db),
    'counts' => security_setting_counts($db),
]);
