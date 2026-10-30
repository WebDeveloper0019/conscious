<?php
// api/lib/resend.php
require_once __DIR__ . '/../config.php';

function resend_send_email(string $to, string $subject, string $html): array {
    // NO MORE LOG MODE. It will ALWAYS hit the API now.
    
    $apiKey = RESEND_API_KEY;
    $fromEmail = RESEND_FROM;
    $fromName = RESEND_FROM_NAME;

    if (empty($apiKey)) {
        return [false, 'RESEND_API_KEY is not configured.'];
    }

    $payload = json_encode([
        'from' => $fromName . ' <' . $fromEmail . '>',
        'to' => [$to],
        'subject' => $subject,
        'html' => $html,
    ]);

    if (!function_exists('curl_init')) {
        return [false, 'PHP cURL extension is not enabled.'];
    }
    
    $ch = curl_init('https://api.resend.com/emails');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_TIMEOUT => 20,
    ]);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($response === false) {
        return [false, 'Resend connection failed: ' . $error];
    }
    
    $data = json_decode($response, true);
    
    if ($status < 200 || $status >= 300) {
        return [false, $data['message'] ?? ('Resend returned HTTP ' . $status)];
    }
    
    return [true, $data['id'] ?? ''];
}

// ────────── EMAIL TEMPLATES ──────────

function send_otp_email(string $to, string $firstName, string $otp): array {
    $safeName = htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8');
    $html = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:32px">'
        . '<h2 style="margin-bottom:8px">Verify your Conscious India account</h2>'
        . '<p>Hello ' . $safeName . ',</p>'
        . '<p>Your email verification OTP is:</p>'
        . '<div style="font-size:32px;font-weight:700;letter-spacing:8px;padding:18px 0">' . $otp . '</div>'
        . '<p>This OTP expires in 10 minutes. If you did not create this account, you can ignore this email.</p>'
        . '</div>';
    return resend_send_email($to, 'Your Conscious India verification OTP', $html);
}

function send_vendor_otp_email(string $to, string $name, string $otp): array {
    $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $html = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:32px">'
        . '<h2>Verify your Conscious India vendor account</h2><p>Hello ' . $safeName . ',</p>'
        . '<p>Your six-digit verification code is:</p><div style="font-size:32px;font-weight:700;letter-spacing:8px;padding:18px 0">' . $otp . '</div>'
        . '<p>This code expires in 10 minutes. Conscious India will never ask for your password or banking PIN.</p></div>';
    return resend_send_email($to, 'Verify your Conscious India vendor account', $html);
}

function send_influencer_otp_email(string $to, string $name, string $otp): array {
    $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $html = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:32px"><h2>Verify your Conscious India creator account</h2><p>Hello ' . $safeName . ',</p><p>Your six-digit verification code is:</p><div style="font-size:32px;font-weight:700;letter-spacing:8px;padding:18px 0">' . $otp . '</div><p>This code expires in 10 minutes. Never share your password or banking PIN.</p></div>';
    return resend_send_email($to, 'Verify your Conscious India creator account', $html);
}