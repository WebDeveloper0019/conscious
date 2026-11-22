<?php
declare(strict_types=1);

require_once __DIR__.'/../lib/helpers.php';
require_once __DIR__.'/../lib/vendor.php';
require_once __DIR__.'/../lib/upload.php';

send_cors_headers();
$context = require_vendor(false);
$vendor = $context['vendor'];
$application = current_vendor_application($vendor['id']);
$afterApproval = $application['status'] === 'approved';

if (!in_array($application['status'], ['draft', 'changes_requested', 'approved'], true)) {
    json_response(409, ['success' => false, 'message' => 'Documents are locked while the application is under review.']);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$allowedCommon = ['pan_file', 'gst_certificate', 'bank_proof', 'store_logo', 'store_banner', 'product_sample', 'impact_evidence'];
$allowedType = $vendor['vendor_type'] === 'artisan'
    ? ['artisan_identity', 'artisan_craft_proof', 'artisan_address_proof', 'artisan_gi_proof']
    : ['brand_registration_proof', 'brand_identity', 'brand_authorization', 'brand_trademark'];
$allowed = array_merge($allowedCommon, $allowedType);

if ($method === 'POST') {
    $type = trim((string) ($_POST['document_type'] ?? ''));
    if (!in_array($type, $afterApproval ? ['bank_proof'] : $allowed, true)) {
        json_response(422, ['success' => false, 'message' => 'This document type is not allowed at this stage.']);
    }

    $imageOnly = in_array($type, ['store_logo', 'store_banner', 'product_sample'], true);
    $provider = $imageOnly ? 'local' : DOCUMENT_STORAGE_DRIVER;
    $asset = store_private_upload(
        $_FILES['file'] ?? [],
        'vendor',
        $vendor['id'],
        $type,
        $imageOnly ? ['image/jpeg', 'image/png', 'image/webp'] : ['image/jpeg', 'image/png', 'application/pdf'],
        $type === 'store_logo' ? 3145728 : 5242880,
        $provider
    );

    $db = get_db();
    $singleDocument = !in_array($type, ['product_sample', 'impact_evidence'], true);
    $replacedAssets = [];
    $documentId = uuid_string();
    $db->beginTransaction();
    try {
        if ($singleDocument) {
            $existing = $db->prepare(
                'SELECT f.* FROM vendor_documents d JOIN file_assets f ON f.id=d.file_asset_id
                 WHERE d.vendor_id=:vendor AND d.application_id=:application
                   AND d.document_type=:type AND f.deleted_at IS NULL FOR UPDATE'
            );
            $existing->execute([
                'vendor' => $vendor['id'],
                'application' => $application['id'],
                'type' => $type,
            ]);
            $replacedAssets = $existing->fetchAll();
            if ($replacedAssets) {
                $assetIds = array_column($replacedAssets, 'id');
                $placeholders = implode(',', array_fill(0, count($assetIds), '?'));
                $db->prepare("UPDATE file_assets SET deleted_at=UTC_TIMESTAMP() WHERE id IN ($placeholders)")->execute($assetIds);
            }
        }

        $db->prepare(
            'INSERT INTO vendor_documents
             (id,vendor_id,application_id,document_type,file_asset_id,verification_status,created_at)
             VALUES (:id,:vendor,:application,:type,:file,"pending",UTC_TIMESTAMP())'
        )->execute([
            'id' => $documentId,
            'vendor' => $vendor['id'],
            'application' => $application['id'],
            'type' => $type,
            'file' => $asset['id'],
        ]);
        $db->commit();
    } catch (Throwable $error) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        $db->prepare('UPDATE file_assets SET deleted_at=UTC_TIMESTAMP() WHERE id=:id')->execute(['id' => $asset['id']]);
        throw $error;
    }

    foreach ($replacedAssets as $replacedAsset) {
        try {
            file_asset_delete_object($replacedAsset);
        } catch (Throwable $error) {
            error_log('Replaced vendor document object cleanup failed: '.$error->getMessage());
        }
    }

    audit_event(
        $replacedAssets ? 'vendor.document_replaced' : 'vendor.document_uploaded',
        'vendor_document',
        $documentId,
        [
            'document_type' => $type,
            'storage_provider' => $asset['storage_provider'],
            'replaced_count' => count($replacedAssets),
        ]
    );
    json_response(201, [
        'success' => true,
        'message' => $replacedAssets
            ? 'Document replaced securely and queued for security scanning.'
            : 'Document uploaded securely and queued for security scanning.',
        'document' => [
            'id' => $documentId,
            'document_type' => $type,
            'verification_status' => 'pending',
            'scan_status' => $asset['scan_status'],
        ],
    ]);
}

if ($method === 'DELETE') {
    $id = trim((string) ($_GET['id'] ?? ''));
    $assetStmt = get_db()->prepare(
        'SELECT f.* FROM file_assets f JOIN vendor_documents d ON d.file_asset_id=f.id
         WHERE d.id=:id AND d.vendor_id=:vendor
           AND d.verification_status IN ("pending","rejected")
           AND f.deleted_at IS NULL LIMIT 1'
    );
    $assetStmt->execute(['id' => $id, 'vendor' => $vendor['id']]);
    $storedAsset = $assetStmt->fetch();
    if (!$storedAsset) {
        json_response(404, ['success' => false, 'message' => 'Document was not found or cannot be removed.']);
    }
    get_db()->prepare('UPDATE file_assets SET deleted_at=UTC_TIMESTAMP() WHERE id=:file AND deleted_at IS NULL')
        ->execute(['file' => $storedAsset['id']]);
    try {
        file_asset_delete_object($storedAsset);
    } catch (Throwable $error) {
        error_log('Deleted vendor document object cleanup failed: '.$error->getMessage());
    }
    audit_event('vendor.document_removed', 'vendor_document', $id);
    json_response(200, ['success' => true, 'message' => 'Document removed.']);
}

json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
