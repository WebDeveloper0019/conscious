<?php
declare(strict_types=1);
require_once __DIR__.'/../../../../api/lib/helpers.php';
$adminSession = current_auth_session(false);
if (!$adminSession || $adminSession['principal_type'] !== 'admin') {
    header('Location: auth/login.php');
    exit;
}
$adminStatus = get_db()->prepare('SELECT status FROM admin_users WHERE id=:id LIMIT 1');
$adminStatus->execute(['id' => $adminSession['principal_id']]);
if ($adminStatus->fetchColumn() !== 'active') {
    header('Location: auth/login.php');
    exit;
}
