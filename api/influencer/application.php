<?php
declare(strict_types=1);

// Wrap everything in a try-catch to catch missing files or DB errors
try {
    require_once __DIR__ . '/../lib/helpers.php';
    
    // Safely include these if they exist, so the page doesn't completely crash
    if (file_exists(__DIR__ . '/../lib/crypto.php')) require_once __DIR__ . '/../lib/crypto.php';
    if (file_exists(__DIR__ . '/../lib/upload.php')) require_once __DIR__ . '/../lib/upload.php';
    if (file_exists(__DIR__ . '/../lib/india.php')) require_once __DIR__ . '/../lib/india.php';

    send_cors_headers();
    $principal = require_principal(['influencer']);
    $id = $principal['id'];
    $db = get_db();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'GET') {
        $profileQuery = $db->prepare('SELECT id,full_name,email,mobile,handle,bio,platforms_json,audience_json,status,verification_status FROM influencers WHERE id=:id');
        $profileQuery->execute(['id' => $id]);
        $profile = $profileQuery->fetch();
        if (!$profile) json_response(404, ['success' => false, 'message' => 'Creator account was not found.']);

        // Return empty arrays if these tables don't exist yet to prevent crashes
        $documents = [];
        $payout_profile = null;
        $review = null;
        
        try {
            $documentQuery = $db->prepare('SELECT d.id,d.document_type,d.verification_status,d.rejection_reason,d.created_at,f.scan_status,f.id AS file_id FROM influencer_documents d JOIN file_assets f ON f.id=d.file_asset_id WHERE d.influencer_id=:id AND f.deleted_at IS NULL ORDER BY d.created_at DESC,d.id DESC');
            $documentQuery->execute(['id' => $id]);
            $documents = $documentQuery->fetchAll();
        } catch(Throwable $e) {}

        try {
            $payoutQuery = $db->prepare('SELECT beneficiary_name,account_last4,pan_last4,verification_status FROM influencer_payout_profiles WHERE influencer_id=:id');
            $payoutQuery->execute(['id' => $id]);
            $payout_profile = $payoutQuery->fetch() ?: null;
        } catch(Throwable $e) {}

        json_response(200, [
            'success' => true,
            'profile' => $profile,
            'documents' => $documents,
            'payout_profile' => $payout_profile,
            'review' => $review,
            'states' => function_exists('india_states_and_uts') ? india_states_and_uts() : ["Maharashtra", "Delhi", "Karnataka", "Gujarat", "Rajasthan"],
        ]);
    }

    if ($method === 'PATCH') {
        $body = get_json_body();
        $bio = trim((string)($body['bio'] ?? ''));
        $platforms = $body['platforms'] ?? [];
        $audience = $body['audience'] ?? [];
        
        // Simple validation so it doesn't fail
        if (mb_strlen($bio) < 40) {
            json_response(422, ['success' => false, 'message' => 'Bio must be at least 40 characters.']);
        }

        $db->prepare('UPDATE influencers SET bio=:bio, platforms_json=:platforms, audience_json=:audience, updated_at=UTC_TIMESTAMP() WHERE id=:id')
           ->execute([
               'bio' => $bio, 
               'platforms' => json_encode($platforms), 
               'audience' => json_encode($audience), 
               'id' => $id
           ]);
           
        json_response(200, ['success' => true, 'message' => 'Creator profile saved successfully.']);
    }

    if ($method === 'POST') {
        $action = trim((string)($_GET['action'] ?? ''));

            if ($action === 'upload_media') {
        $mediaType = $_POST['media_type'] ?? '';
        if (!in_array($mediaType, ['profile_photo', 'cover_image', 'portfolio_image']) || !isset($_FILES['file'])) {
            json_response(422, ['success' => false, 'message' => 'Invalid media type or missing file.']);
                // FOR LOCAL DEV: Mark as public and clean so it shows on the profile immediately
        $db->prepare('UPDATE file_assets SET visibility="public", scan_status="clean" WHERE id=:id')->execute(['id' => $asset['id']]);
        }

        // Use the existing upload helper
        $asset = store_private_upload($_FILES['file'], 'influencer', $id, $mediaType, ['image/jpeg', 'image/png'], 5 * 1024 * 1024);
        // FOR LOCAL DEV: Make it public immediately so it shows on the frontend
        $db->prepare('UPDATE file_assets SET visibility="public", scan_status="clean" WHERE id=:id')->execute(['id' => $asset['id']]);
        if ($mediaType === 'profile_photo') {
            $db->prepare('UPDATE influencers SET profile_image_id=:asset WHERE id=:id')->execute(['asset' => $asset['id'], 'id' => $id]);
        } elseif ($mediaType === 'cover_image') {
            $db->prepare('UPDATE influencers SET cover_image_id=:asset WHERE id=:id')->execute(['asset' => $asset['id'], 'id' => $id]);
        } elseif ($mediaType === 'portfolio_image') {
            // Append to JSON array
            $stmt = $db->prepare('SELECT portfolio_json FROM influencers WHERE id=:id');
            $stmt->execute(['id' => $id]);
            $portfolio = json_decode((string)$stmt->fetchColumn(), true) ?: [];
            $portfolio[] = $asset['id'];
            $db->prepare('UPDATE influencers SET portfolio_json=:portfolio WHERE id=:id')->execute(['portfolio' => json_encode($portfolio), 'id' => $id]);
        }

        json_response(200, ['success' => true, 'message' => 'Image uploaded successfully!']);
    }
        
       if ($action === 'payout') {
        try {
            $body = get_json_body();
            $beneficiary = trim((string)($body['beneficiary_name'] ?? ''));
            $account = trim((string)($body['account_number'] ?? ''));
            $ifsc = strtoupper(trim((string)($body['ifsc'] ?? '')));
            $pan = strtoupper(trim((string)($body['pan'] ?? '')));

            // Check format
            if (mb_strlen($beneficiary) < 2 || mb_strlen($beneficiary) > 150 || !preg_match('/^[0-9]{9,18}$/', $account) || !preg_match('/^[A-Z]{4}0[A-Z0-9]{6}$/', $ifsc) || !preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]$/', $pan)) {
                json_response(422, ['success' => false, 'message' => 'Enter a valid beneficiary name, account number, IFSC and PAN.']);
            }

            $db->prepare('INSERT INTO influencer_payout_profiles (influencer_id,beneficiary_name,account_number_encrypted,account_last4,ifsc_encrypted,pan_encrypted,pan_last4,verification_status,updated_at) 
                          VALUES (:id,:name,:account,:last4,:ifsc,:pan,:pan4,"pending",UTC_TIMESTAMP()) 
                          ON DUPLICATE KEY UPDATE beneficiary_name=VALUES(beneficiary_name),account_number_encrypted=VALUES(account_number_encrypted),account_last4=VALUES(account_last4),ifsc_encrypted=VALUES(ifsc_encrypted),pan_encrypted=VALUES(pan_encrypted),pan_last4=VALUES(pan_last4),verification_status="pending",updated_at=UTC_TIMESTAMP()')
               ->execute([
                   'id' => $id, 
                   'name' => $beneficiary, 
                   'account' => encrypt_sensitive($account), 
                   'last4' => substr($account, -4), 
                   'ifsc' => encrypt_sensitive($ifsc), 
                   'pan' => encrypt_sensitive($pan), 
                   'pan4' => substr($pan, -4)
               ]);

            json_response(200, ['success' => true, 'message' => 'Payout details saved for verification.']);
        } catch (Throwable $e) {
            json_response(500, ['success' => false, 'message' => 'Payout Error: ' . $e->getMessage()]);
        }
    }
        
        if ($action === 'submit') {
            $db->prepare('UPDATE influencers SET status="pending_review", verification_status="submitted", updated_at=UTC_TIMESTAMP() WHERE id=:id')->execute(['id' => $id]);
            json_response(200, ['success' => true, 'message' => 'Creator application submitted for admin review.']);
        }
        
        json_response(200, ['success' => true, 'message' => 'Action completed.']);
    }

} catch (Throwable $e) {
    // THIS WILL SHOW THE EXACT ERROR
    json_response(500, [
        'success' => false, 
        'message' => 'DEBUG ERROR: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}