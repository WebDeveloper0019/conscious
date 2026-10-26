<?php
declare(strict_types=1);
require_once __DIR__.'/../../../../api/lib/helpers.php';
$customerSession = current_auth_session(false);
if (!$customerSession || $customerSession['principal_type'] !== 'customer') {
    header('Location: auth/login.php');
    exit;
}
$customerCheck = get_db()->prepare('SELECT status,email_verified FROM users WHERE id=:id LIMIT 1');
$customerCheck->execute(['id'=>$customerSession['principal_id']]);
$customerView = $customerCheck->fetch();
if (!$customerView || $customerView['status'] !== 'active' || !(int)$customerView['email_verified']) {
    header('Location: auth/login.php');
    exit;
}
