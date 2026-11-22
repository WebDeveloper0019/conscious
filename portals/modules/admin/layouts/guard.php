<?php
declare(strict_types=1);
require_once __DIR__.'/../../../../api/lib/helpers.php';
$adminSession = current_auth_session(false);
if (!$adminSession || $adminSession['principal_type'] !== 'admin') {
    header('Location: /conscious/admin/login');
    exit;
}
$adminStatus = get_db()->prepare('SELECT full_name,status FROM admin_users WHERE id=:id LIMIT 1');
$adminStatus->execute(['id' => $adminSession['principal_id']]);
$adminUser = $adminStatus->fetch();
if (!$adminUser || $adminUser['status'] !== 'active') {
    header('Location: /conscious/admin/login');
    exit;
}
$adminDisplayName = trim((string) ($adminUser['full_name'] ?? '')) ?: 'Administrator';
$adminNameParts = preg_split('/\s+/', $adminDisplayName) ?: [];
$adminInitials = '';
foreach (array_slice($adminNameParts, 0, 2) as $adminNamePart) {
    $adminInitials .= function_exists('mb_substr') ? mb_substr($adminNamePart, 0, 1) : substr($adminNamePart, 0, 1);
}
$adminInitials = strtoupper($adminInitials ?: 'AD');
