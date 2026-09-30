<?php
declare(strict_types=1);
require_once __DIR__.'/session.php';

function principal_permissions(array $principal): array {
    static $cache=[]; $key=$principal['type'].':'.$principal['id']; if(isset($cache[$key]))return $cache[$key];
    $stmt=get_db()->prepare('SELECT DISTINCT p.code FROM principal_roles pr JOIN role_permissions rp ON rp.role_id=pr.role_id JOIN permissions p ON p.id=rp.permission_id WHERE pr.principal_type=:type AND pr.principal_id=:id');
    $stmt->execute(['type'=>$principal['type'],'id'=>$principal['id']]); return $cache[$key]=array_column($stmt->fetchAll(),'code');
}
function require_permission(string $permission,array $allowedTypes=['admin']): array {
    $principal=require_principal($allowedTypes); if(!in_array($permission,principal_permissions($principal),true))json_response(403,['success'=>false,'message'=>'You do not have permission to perform this action.']); return $principal;
}
function require_owned_record(string $table,string $idColumn,string $id,string $ownerColumn,string $ownerId): array {
    $allow=['vendors','vendor_applications','vendor_profiles','vendor_addresses','products','product_variants','orders','order_items','support_tickets','events','influencers'];
    if(!in_array($table,$allow,true)||!preg_match('/^[a-z_]+$/',$idColumn.$ownerColumn))throw new InvalidArgumentException('Ownership query is not allowlisted.');
    $stmt=get_db()->prepare("SELECT * FROM {$table} WHERE {$idColumn}=:id AND {$ownerColumn}=:owner LIMIT 1");$stmt->execute(['id'=>$id,'owner'=>$ownerId]);$row=$stmt->fetch();
    if(!$row)json_response(404,['success'=>false,'message'=>'Resource not found.']); return $row;
}
