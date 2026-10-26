<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';

send_cors_headers();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

$databaseAvailable = false;
try {
    $databaseAvailable = (string)get_db()->query('SELECT 1')->fetchColumn() === '1';
} catch (Throwable) {
    $databaseAvailable = false;
}

$storageAvailable = is_dir(PRIVATE_STORAGE_PATH) && is_writable(PRIVATE_STORAGE_PATH);
$healthy = $databaseAvailable && $storageAvailable;

json_response($healthy ? 200 : 503, [
    'success' => $healthy,
    'status' => $healthy ? 'ok' : 'degraded',
    'checks' => [
        'database' => $databaseAvailable ? 'ok' : 'unavailable',
        'private_storage' => $storageAvailable ? 'ok' : 'unavailable',
    ],
    'time' => gmdate(DATE_ATOM),
]);
