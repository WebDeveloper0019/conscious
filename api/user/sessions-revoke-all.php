<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/audit.php';

send_cors_headers();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Allow: POST');
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

$principal = require_principal(['customer']);

revoke_principal_sessions('customer', $principal['id'], 'customer_logout_all');
revoke_current_session('customer_logout_all');
audit_event('customer.sessions_revoked_all', 'user', $principal['id']);

json_response(200, [
    'success' => true,
    'message' => 'You have been signed out on all devices.',
]);
