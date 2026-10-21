<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

/**
 * A fixed code is permitted only for this machine's local HTTP test site.
 * Public/production requests always receive a random code.
 */
function customer_dummy_otp_enabled(): bool {
    if (APP_ENV !== 'local' || MAIL_TRANSPORT !== 'log') return false;
    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
    if (!in_array($ip, ['127.0.0.1', '::1'], true)) return false;
    $host = strtolower((string)parse_url('http://' . (string)($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST));
    return in_array($host, ['localhost', '127.0.0.1', '::1'], true);
}

function customer_verification_code(): string {
    return customer_dummy_otp_enabled() ? '123456' : (string)random_int(100000, 999999);
}
