<?php
declare(strict_types=1);

require_once __DIR__ . '/../api/lib/helpers.php';

if (APP_ENV !== 'local' || !preg_match('/^conscious_backend_merge_[0-9_]+$/', DB_NAME)) {
    fwrite(STDERR, "Use a local isolated conscious_backend_merge_* database.\n");
    exit(2);
}
if (MAIL_TRANSPORT !== 'log' || !extension_loaded('curl')) {
    fwrite(STDERR, "Local mail log and PHP cURL are required.\n");
    exit(2);
}

$base = rtrim(getenv('TEST_BASE_URL') ?: 'http://127.0.0.1:8113/conscious', '/');
$cookies = [];
$passes = 0;
$suffix = bin2hex(random_bytes(4));
$email = 'creator-e2e-' . $suffix . '@test.local';
$handle = 'creator-e2e-' . $suffix;
$phone = '8' . str_pad((string)(hexdec($suffix) % 1000000000), 9, '0', STR_PAD_LEFT);
$password = 'CreatorStart#' . $suffix . 'A1';
$newPassword = 'CreatorReset#' . $suffix . 'B2';
// The isolated local suite performs repeated registrations while developing;
// clear only its loopback registration throttle before a run.
@unlink(__DIR__ . '/../api/data/rate-limit/' . hash('sha256', 'influencer-register|127.0.0.1') . '.json');

function call_api(string $path, string $method = 'GET', ?array $body = null, string $csrf = '', bool $multipart = false): array {
    global $base, $cookies;
    $handle = curl_init($base . $path);
    $headers = ['Accept: application/json'];
    if ($csrf !== '') $headers[] = 'X-CSRF-Token: ' . $csrf;
    if (!$multipart && $body !== null) $headers[] = 'Content-Type: application/json';
    curl_setopt_array($handle, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT => 20,
    ]);
    if ($cookies) curl_setopt($handle, CURLOPT_COOKIE, implode('; ', array_map(static fn($name, $value) => $name . '=' . $value, array_keys($cookies), $cookies)));
    if ($body !== null) curl_setopt($handle, CURLOPT_POSTFIELDS, $multipart ? $body : json_encode($body, JSON_THROW_ON_ERROR));
    $raw = curl_exec($handle);
    if ($raw === false) throw new RuntimeException('HTTP request failed: ' . curl_error($handle));
    $status = (int)curl_getinfo($handle, CURLINFO_HTTP_CODE);
    $headerLength = (int)curl_getinfo($handle, CURLINFO_HEADER_SIZE);
    $headersText = substr($raw, 0, $headerLength);
    $content = substr($raw, $headerLength);
    preg_match_all('/^Set-Cookie:\\s*([^=;\\s]+)=([^;\\r\\n]*)/mi', $headersText, $setCookies, PREG_SET_ORDER);
    foreach ($setCookies as $cookie) {
        if ($cookie[2] === '') unset($cookies[$cookie[1]]);
        else $cookies[$cookie[1]] = $cookie[2];
    }
    curl_close($handle);
    return [$status, json_decode($content, true), $headersText, $content];
}

function check_http(string $label, int $expected, array $result): array {
    global $passes;
    if ($result[0] !== $expected) {
        throw new RuntimeException($label . ': expected HTTP ' . $expected . ', got ' . $result[0] . ' (' . substr((string)$result[3], 0, 300) . ')');
    }
    $passes++;
    fwrite(STDOUT, "PASS {$label}\n");
    return is_array($result[1]) ? $result[1] : [];
}

function latest_mail(string $email, string $subjectFragment): string {
    $files = glob(__DIR__ . '/../api/data/mail-outbox/*.json') ?: [];
    rsort($files, SORT_STRING);
    foreach ($files as $file) {
        $mail = json_decode((string)file_get_contents($file), true);
        if (($mail['to'] ?? '') === $email && str_contains((string)($mail['subject'] ?? ''), $subjectFragment)) {
            return (string)($mail['html'] ?? '');
        }
    }
    throw new RuntimeException('Expected local test mail was not found.');
}

try {
    check_http('registration page', 200, call_api('/portals/modules/influencer/auth/register.php'));
    check_http('login page', 200, call_api('/portals/modules/influencer/auth/login.php'));
    check_http('verification page', 200, call_api('/portals/modules/influencer/auth/verify.php'));
    check_http('forgot password page', 200, call_api('/portals/modules/influencer/auth/forgot-password.php'));
    check_http('reset password page', 200, call_api('/portals/modules/influencer/auth/reset-password.php'));
    check_http('dashboard redirects guests', 302, call_api('/portals/modules/influencer/index.php'));
    check_http('weak password rejected', 422, call_api('/api/influencer/auth/register.php', 'POST', [
        'full_name' => 'Creator E2E', 'email' => $email, 'phone' => $phone, 'handle' => $handle, 'password' => 'weak', 'terms' => true,
    ]));
    check_http('creator registered', 201, call_api('/api/influencer/auth/register.php', 'POST', [
        'full_name' => 'Creator E2E', 'email' => $email, 'phone' => $phone, 'handle' => $handle, 'password' => $password, 'terms' => true,
    ]));
    check_http('duplicate account rejected', 409, call_api('/api/influencer/auth/register.php', 'POST', [
        'full_name' => 'Creator E2E', 'email' => $email, 'phone' => $phone, 'handle' => $handle, 'password' => $password, 'terms' => true,
    ]));
    check_http('unverified login blocked', 403, call_api('/api/influencer/auth/login.php', 'POST', ['email' => $email, 'password' => $password]));
    check_http('resend cooldown enforced', 429, call_api('/api/influencer/auth/resend-code.php', 'POST', ['email' => $email]));
    $mail = latest_mail($email, 'Verify your Conscious India creator');
    if (!preg_match('/>\s*([0-9]{6})\s*</', $mail, $match)) throw new RuntimeException('Verification mail has no code.');
    check_http('wrong verification code rejected', 400, call_api('/api/influencer/auth/verify.php', 'POST', ['email' => $email, 'code' => '000000']));
    $verifyResponse = call_api('/api/influencer/auth/verify.php', 'POST', ['email' => $email, 'code' => $match[1]]);
    $verified = check_http('email verified', 200, $verifyResponse);
    $csrf = (string)($verified['csrf_token'] ?? '');
    if ($csrf === '') throw new RuntimeException('Verification did not create an authenticated session.');
    if (!str_contains($verifyResponse[2], 'ci_session=')) throw new RuntimeException('Verification did not set the session cookie.');
    if (!isset($cookies['ci_session'], $cookies['ci_csrf'])) throw new RuntimeException('Verification did not provide both auth cookies.');
    check_http('creator session works', 200, call_api('/api/influencer/auth/me.php'));
    check_http('dashboard loads', 200, call_api('/portals/modules/influencer/index.php'));
    check_http('CSRF is enforced', 419, call_api('/api/influencer/application.php', 'PATCH', ['bio' => 'Invalid']));
    check_http('application draft loads', 200, call_api('/api/influencer/application.php'));
    $bio = 'I tell authentic stories about sustainable Indian craft, makers and mindful living for my engaged audience.';
    check_http('insecure social URL rejected', 422, call_api('/api/influencer/application.php', 'PATCH', [
        'bio' => $bio, 'platforms' => [['name' => 'Instagram', 'url' => 'http://example.com/creator', 'followers' => 100]], 'audience' => ['region' => 'India', 'city' => 'Mumbai', 'state' => 'Maharashtra', 'niches' => ['Sustainable living'], 'languages' => ['Hindi']],
    ], $csrf));
    check_http('unknown Indian state rejected', 422, call_api('/api/influencer/application.php', 'PATCH', [
        'bio' => $bio, 'platforms' => [['name' => 'Instagram', 'url' => 'https://instagram.com/creator-e2e', 'followers' => 100]], 'audience' => ['region' => 'India', 'city' => 'Mumbai', 'state' => 'Unknown State', 'niches' => ['Sustainable living'], 'languages' => ['Hindi']],
    ], $csrf));
    check_http('profile saved', 200, call_api('/api/influencer/application.php', 'PATCH', [
        'bio' => $bio, 'platforms' => [['name' => 'Instagram', 'url' => 'https://instagram.com/creator-e2e', 'followers' => 100]], 'audience' => ['region' => 'India', 'city' => 'Mumbai', 'state' => 'Maharashtra', 'niches' => ['Sustainable living'], 'languages' => ['Hindi', 'English']],
    ], $csrf));
    check_http('submission requires documents', 422, call_api('/api/influencer/application.php?action=submit', 'POST', [], $csrf));
    $image = __DIR__ . '/../assets/images/user.png';
    foreach (['identity', 'pan', 'social_ownership'] as $type) {
        check_http($type . ' uploaded', 201, call_api('/api/influencer/application.php?action=upload', 'POST', [
            'document_type' => $type,
            'document' => new CURLFile($image, 'image/png', $type . '.png'),
        ], $csrf, true));
    }
    check_http('pending scan blocks submission', 422, call_api('/api/influencer/application.php?action=submit', 'POST', [], $csrf));
    $creatorQuery = get_db()->prepare('SELECT id FROM influencers WHERE email=:email');
    $creatorQuery->execute(['email' => $email]);
    $creatorId = (string)$creatorQuery->fetchColumn();
    if ($creatorId === '') throw new RuntimeException('Test creator missing in isolated database.');
    // Simulate a successful scanner result only in this isolated test database.
    get_db()->prepare('UPDATE file_assets SET scan_status="clean" WHERE owner_type="influencer" AND owner_id=:id AND purpose LIKE "influencer_%"')
        ->execute(['id' => $creatorId]);
    check_http('application submitted', 200, call_api('/api/influencer/application.php?action=submit', 'POST', [], $csrf));
    check_http('duplicate submission blocked', 409, call_api('/api/influencer/application.php?action=submit', 'POST', [], $csrf));
    $edited = check_http('submitted profile edit accepted', 200, call_api('/api/influencer/application.php', 'PATCH', [
        'bio' => $bio . ' I also document local workshops.', 'platforms' => [['name' => 'Instagram', 'url' => 'https://instagram.com/creator-e2e', 'followers' => 150]], 'audience' => ['region' => 'All India', 'city' => 'Mumbai', 'state' => 'Maharashtra', 'niches' => ['Sustainable living', 'Indian crafts'], 'languages' => ['Hindi', 'English']],
    ], $csrf));
    if (($edited['status'] ?? '') !== 'application_draft') throw new RuntimeException('Edited submission did not return to draft.');
    check_http('edited application resubmitted', 200, call_api('/api/influencer/application.php?action=submit', 'POST', [], $csrf));
    check_http('invalid payout account rejected', 422, call_api('/api/influencer/application.php?action=payout', 'POST', [
        'beneficiary_name' => 'Creator E2E', 'account_number' => 'letters123456789', 'ifsc' => 'HDFC0001234', 'pan' => 'ABCDE1234F',
    ], $csrf));
    check_http('payout details saved', 200, call_api('/api/influencer/application.php?action=payout', 'POST', [
        'beneficiary_name' => 'Creator E2E', 'account_number' => '123456789012', 'ifsc' => 'HDFC0001234', 'pan' => 'ABCDE1234F',
    ], $csrf));
    $application = check_http('masked payout details returned', 200, call_api('/api/influencer/application.php'));
    if (isset($application['payout_profile']['account_number_encrypted']) || ($application['payout_profile']['account_last4'] ?? '') !== '9012') {
        throw new RuntimeException('Payout response did not mask account details.');
    }
    $creatorCookies = $cookies;
    $adminId = uuid_string();
    $adminEmail = 'creator-review-' . $suffix . '@test.local';
    $adminPassword = 'AdminReview#' . $suffix . 'C3';
    get_db()->prepare('INSERT INTO admin_users (id,full_name,email,password_hash,status,created_at) VALUES (:id,"E2E Creator Reviewer",:email,:hash,"active",UTC_TIMESTAMP())')
        ->execute(['id' => $adminId, 'email' => $adminEmail, 'hash' => password_hash($adminPassword, PASSWORD_DEFAULT)]);
    get_db()->prepare('INSERT INTO principal_roles (principal_type,principal_id,role_id,granted_at) SELECT "admin",:id,id,UTC_TIMESTAMP() FROM roles WHERE code="admin_super"')
        ->execute(['id' => $adminId]);
    $cookies = [];
    $adminLogin = check_http('reviewer signs in', 200, call_api('/api/admin/auth/login.php', 'POST', ['email' => $adminEmail, 'password' => $adminPassword]));
    $adminCsrf = (string)($adminLogin['csrf_token'] ?? '');
    check_http('unreviewed documents block approval', 409, call_api('/api/admin/influencers.php', 'POST', [
        'influencer_id' => $creatorId, 'action' => 'approve',
    ], $adminCsrf));
    $adminDocs = check_http('reviewer sees private document records', 200, call_api('/api/admin/influencer-documents.php?influencer_id=' . rawurlencode($creatorId)));
    foreach ($adminDocs['documents'] as $document) {
        check_http($document['document_type'] . ' approved by reviewer', 200, call_api('/api/admin/influencer-documents.php', 'POST', [
            'document_id' => $document['id'], 'decision' => 'approve',
        ], $adminCsrf));
    }
    check_http('reviewer approves creator', 200, call_api('/api/admin/influencers.php', 'POST', [
        'influencer_id' => $creatorId, 'action' => 'approve',
    ], $adminCsrf));
    $adminCookies = $cookies;
    $cookies = $creatorCookies;
    check_http('approved creator replacement upload', 201, call_api('/api/influencer/application.php?action=upload', 'POST', [
        'document_type' => 'identity',
        'document' => new CURLFile($image, 'image/png', 'identity-replacement.png'),
    ], $csrf, true));
    $cookies = $adminCookies;
    check_http('old approved document cannot approve replacement', 409, call_api('/api/admin/influencers.php', 'POST', [
        'influencer_id' => $creatorId, 'action' => 'approve',
    ], $adminCsrf));
    check_http('reviewer requests changes', 200, call_api('/api/admin/influencers.php', 'POST', [
        'influencer_id' => $creatorId, 'action' => 'request_changes', 'reason' => 'Please upload a clearer identity document.',
    ], $adminCsrf));
    $cookies = $creatorCookies;
    $feedback = check_http('creator sees review feedback', 200, call_api('/api/influencer/application.php'));
    if (($feedback['review']['reason'] ?? '') !== 'Please upload a clearer identity document.') {
        throw new RuntimeException('Admin feedback was not returned to the creator.');
    }
    check_http('forgot password accepted', 200, call_api('/api/auth/forgot-password.php', 'POST', ['email' => $email, 'account_type' => 'influencer']));
    $resetMail = html_entity_decode(latest_mail($email, 'Reset your Conscious India password'), ENT_QUOTES, 'UTF-8');
    if (!preg_match('~reset-password\.php\?token=([^&"<>]+)~', $resetMail, $resetMatch)) throw new RuntimeException('Password reset mail has no token.');
    $token = rawurldecode($resetMatch[1]);
    check_http('password reset works', 200, call_api('/api/auth/reset-password.php', 'POST', [
        'token' => $token, 'password' => $newPassword, 'password_confirmation' => $newPassword,
    ]));
    check_http('reset token cannot be reused', 410, call_api('/api/auth/reset-password.php', 'POST', [
        'token' => $token, 'password' => $newPassword, 'password_confirmation' => $newPassword,
    ]));
    check_http('old session revoked', 401, call_api('/api/influencer/auth/me.php'));
    check_http('old password rejected', 401, call_api('/api/influencer/auth/login.php', 'POST', ['email' => $email, 'password' => $password]));
    $login = check_http('new password signs in', 200, call_api('/api/influencer/auth/login.php', 'POST', ['email' => $email, 'password' => $newPassword, 'remember' => true]));
    check_http('logout works', 200, call_api('/api/influencer/auth/logout.php', 'POST', [], (string)($login['csrf_token'] ?? '')));
    check_http('logout revokes session', 401, call_api('/api/influencer/auth/me.php'));
    fwrite(STDOUT, "INFLUENCER_AUTH_ONBOARDING_OK {$passes} checks\n");
} catch (Throwable $error) {
    fwrite(STDERR, "FAIL: {$error->getMessage()}\n");
    exit(1);
}
