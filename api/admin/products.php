<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
require_once __DIR__.'/../lib/admin.php';
require_once __DIR__.'/../lib/notifications.php';
send_cors_headers();
$context = require_admin('products.review');
$db = get_db();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $id = trim((string)($_GET['id'] ?? ''));
    if ($id !== '') {
        $query = $db->prepare('SELECT p.*,v.display_name AS vendor_name FROM products p JOIN vendors v ON v.id=p.vendor_id WHERE p.id=:id');
        $query->execute(['id'=>$id]);
        $product = $query->fetch();
        if (!$product) json_response(404,['success'=>false,'message'=>'Product not found.']);
        $versions = $db->prepare('SELECT * FROM product_versions WHERE product_id=:id ORDER BY version_number DESC');
        $versions->execute(['id'=>$id]);
        $images = $db->prepare('SELECT pi.*,f.scan_status,f.mime_type,f.id AS file_id FROM product_images pi JOIN file_assets f ON f.id=pi.file_asset_id WHERE pi.product_id=:id AND f.deleted_at IS NULL ORDER BY pi.is_primary DESC,pi.sort_order');
        $images->execute(['id'=>$id]);
        json_response(200,['success'=>true,'product'=>$product,'versions'=>$versions->fetchAll(),'images'=>$images->fetchAll()]);
    }
    $status = trim((string)($_GET['status'] ?? 'pending_review'));
    $params = [];
    $where = '';
    if ($status !== '') {$where=' WHERE p.status=:status';$params['status']=$status;}
    $query = $db->prepare('SELECT p.id,p.name,p.sku,p.status,p.review_version,p.updated_at,v.display_name AS vendor_name FROM products p JOIN vendors v ON v.id=p.vendor_id'.$where.' ORDER BY p.updated_at ASC LIMIT 200');
    $query->execute($params);
    json_response(200,['success'=>true,'products'=>$query->fetchAll()]);
}

if ($method === 'POST') {
    $body = get_json_body();
    $id = trim((string)($body['product_id'] ?? ''));
    $decision = trim((string)($body['decision'] ?? ''));
    $notes = trim((string)($body['notes'] ?? ''));
    $nextByDecision = ['start_review'=>'under_review','approve'=>'approved','request_changes'=>'changes_requested','reject'=>'rejected','suspend'=>'suspended'];
    if (!isset($nextByDecision[$decision]) || ($decision !== 'start_review' && mb_strlen($notes)<10)) json_response(422,['success'=>false,'message'=>'Select a review decision and provide a note of at least 10 characters.']);
    $db->beginTransaction();
    try {
        $query = $db->prepare('SELECT * FROM products WHERE id=:id FOR UPDATE');
        $query->execute(['id'=>$id]);
        $product = $query->fetch();
        if (!$product) json_response(404,['success'=>false,'message'=>'Product not found.']);
        $next = $nextByDecision[$decision];
        $map = ['pending_review'=>['under_review'],'under_review'=>['approved','changes_requested','rejected'],'approved'=>['suspended'],'active'=>['suspended'],'paused'=>['suspended'],'out_of_stock'=>['suspended']];
        assert_transition($product['status'],$next,$map);
        if ($decision === 'approve') {
            $check = $db->prepare('SELECT COUNT(*) FROM product_images pi JOIN file_assets f ON f.id=pi.file_asset_id WHERE pi.product_id=:id AND f.scan_status="clean" AND f.deleted_at IS NULL');
            $check->execute(['id'=>$id]);
            if ((int)$check->fetchColumn()<1) json_response(422,['success'=>false,'message'=>'Product cannot be approved without a clean image.']);
            $db->prepare('UPDATE file_assets f JOIN product_images pi ON pi.file_asset_id=f.id SET f.visibility="public" WHERE pi.product_id=:id AND f.scan_status="clean"')->execute(['id'=>$id]);
        }
        $db->prepare('UPDATE products SET status=:status,rejection_reason=:notes,published_at=IF(:is_approved_at=1,COALESCE(published_at,UTC_TIMESTAMP()),published_at),published_version=IF(:is_approved_version=1,review_version,published_version),published_snapshot_json=IF(:is_approved_snapshot=1,(SELECT snapshot_json FROM product_versions WHERE product_id=products.id AND version_number=products.review_version),published_snapshot_json),updated_at=UTC_TIMESTAMP() WHERE id=:id')->execute(['status'=>$next,'notes'=>$decision==='approve'?null:$notes,'is_approved_at'=>$decision==='approve'?1:0,'is_approved_version'=>$decision==='approve'?1:0,'is_approved_snapshot'=>$decision==='approve'?1:0,'id'=>$id]);
        $db->prepare('UPDATE product_versions SET review_status=:status,reviewed_at=IF(:final=1,UTC_TIMESTAMP(),reviewed_at),reviewed_by=:admin,review_notes=:notes WHERE product_id=:id AND version_number=:version')->execute(['status'=>$next,'final'=>in_array($next,['approved','changes_requested','rejected'],true)?1:0,'admin'=>$context['admin']['id'],'notes'=>$notes?:null,'id'=>$id,'version'=>$product['review_version']]);
        record_status_change('product',$id,$product['status'],$next,$notes);
        create_notification('vendor',$product['vendor_id'],'product_review','Product review update',"{$product['name']} is now {$next}.",'../products.php',['product_id'=>$id]);
        audit_event('admin.product_'.$decision,'product',$id,['notes'=>$notes]);
        $db->commit();
    } catch (Throwable $error) {
        if ($db->inTransaction()) $db->rollBack();
        throw $error;
    }
    json_response(200,['success'=>true,'message'=>'Product review status updated.','status'=>$next]);
}
json_response(405,['success'=>false,'message'=>'Method not allowed.']);
