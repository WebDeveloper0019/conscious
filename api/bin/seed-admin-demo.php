<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__.'/../lib/helpers.php';

if (APP_ENV !== 'local') {
    fwrite(STDERR, "Administrator demo seeding is local-only.\n");
    exit(1);
}

$email = 'admin.demo@conscious.test';
$password = 'DemoAdmin#2026!';
$db = get_db();
$find = $db->prepare('SELECT id FROM admin_users WHERE email=:email LIMIT 1');
$find->execute(['email' => $email]);
$existing = $find->fetch();

$db->beginTransaction();
try {
    if ($existing) {
        $id = $existing['id'];
        $db->prepare('UPDATE admin_users SET full_name="Demo Administrator",password_hash=:password,status="active",updated_at=UTC_TIMESTAMP() WHERE id=:id')
            ->execute(['id' => $id, 'password' => password_hash($password, PASSWORD_DEFAULT)]);
    } else {
        $id = uuid_string();
        $db->prepare('INSERT INTO admin_users (id,full_name,email,password_hash,status,created_at) VALUES (:id,"Demo Administrator",:email,:password,"active",UTC_TIMESTAMP())')
            ->execute(['id' => $id, 'email' => $email, 'password' => password_hash($password, PASSWORD_DEFAULT)]);
    }
    $db->prepare('INSERT IGNORE INTO principal_roles (principal_type,principal_id,role_id,granted_at) SELECT "admin",:id,id,UTC_TIMESTAMP() FROM roles WHERE code="admin_super"')
        ->execute(['id' => $id]);
    $db->commit();
} catch (Throwable $error) {
    if ($db->inTransaction()) $db->rollBack();
    fwrite(STDERR, "Administrator demo account could not be seeded.\n");
    exit(1);
}

fwrite(STDOUT, "Local demo administrator: {$email}\nPassword: {$password}\n");
