<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/invoice-settings.php';
require_once __DIR__ . '/../lib/audit.php';

send_cors_headers();

$principal = require_principal(['admin']);
$db        = get_db();
$method    = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// ────────── GET: read settings + validation status ──────────
if ($method === 'GET') {
    $settings = invoice_settings_all();
    $issues   = invoice_settings_issues();

    json_response(200, [
        'success'  => true,
        'settings' => $settings,
        'schema'   => invoice_settings_schema(),
        'issues'   => $issues,
        'ready'    => empty($issues),
    ]);
}

// ────────── PUT: update settings ──────────
if ($method === 'PUT' || $method === 'POST') {
    require_csrf();

    $body = get_json_body();
    if (empty($body) || !is_array($body)) {
        json_response(422, ['success' => false, 'message' => 'Request body must contain settings to update.']);
    }

    // Reject attempts to write non-schema keys
    $schema = invoice_settings_schema();
    $unknown = array_diff(array_keys($body), array_keys($schema));
    if ($unknown) {
        json_response(422, [
            'success' => false,
            'message' => 'Unknown settings: ' . implode(', ', $unknown),
        ]);
    }

    try {
        [$count, $errors] = invoice_settings_update($body, (string)$principal['id']);

        if ($errors) {
            json_response(422, [
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $errors,
            ]);
        }

        audit_event('admin.invoice_settings_updated', 'platform_settings', null, [
            'keys'    => array_keys($body),
            'count'   => $count,
        ]);

        // Return the fresh state
        $settings = invoice_settings_all();
        $issues   = invoice_settings_issues();

        json_response(200, [
            'success'  => true,
            'message'  => "{$count} setting(s) updated.",
            'settings' => $settings,
            'issues'   => $issues,
            'ready'    => empty($issues),
        ]);
    } catch (Throwable $e) {
        error_log('invoice-settings update failed: ' . $e->getMessage());
        json_response(500, ['success' => false, 'message' => 'Unable to update settings.']);
    }
}

json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
