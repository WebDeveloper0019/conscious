<?php
declare(strict_types=1);

require_once __DIR__.'/../../../../api/lib/helpers.php';
$vendorSession = current_auth_session(false);
if (!$vendorSession || $vendorSession['principal_type'] !== 'vendor') {
    header('Location: auth/login.php');
    exit;
}

$vendorStatement = get_db()->prepare('SELECT id,owner_name,display_name,email,vendor_type,status,verification_status,onboarding_status FROM vendors WHERE id=:id LIMIT 1');
$vendorStatement->execute(['id' => $vendorSession['principal_id']]);
$vendorView = $vendorStatement->fetch();
if (!$vendorView || in_array($vendorView['status'], ['suspended','blocked','inactive'], true)
    || (APP_ENV !== 'local' && $vendorView['verification_status'] === 'demo_fixture')) {
    header('Location: auth/login.php');
    exit;
}

$vendorName = trim((string)$vendorView['display_name']);
$vendorOwner = trim((string)$vendorView['owner_name']);
$vendorInitials = '';
foreach (preg_split('/\s+/u', $vendorOwner) ?: [] as $part) {
    if ($part !== '') $vendorInitials .= mb_strtoupper(mb_substr($part, 0, 1));
    if (mb_strlen($vendorInitials) >= 2) break;
}
if ($vendorInitials === '') $vendorInitials = 'VD';
