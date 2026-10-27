<?php
declare(strict_types=1);

/**
 * OTP email. In local / test mode just logs to a file. In production,
 * swap the body of this function for a Resend API call.
 */
function send_influencer_otp_email(string $email, string $name, string $otp): array {
    $log = __DIR__ . '/../storage/otp.log';
    @mkdir(dirname($log), 0775, true);
    @file_put_contents($log,
        sprintf("[%s] to=%s name=%s otp=%s\n", date('c'), $email, $name, $otp),
        FILE_APPEND | LOCK_EX
    );

    /* Production sample (uncomment + set RESEND_API_KEY):
    $payload = json_encode([
        'from'    => 'Conscious Indi <hello@consciousindi.com>',
        'to'      => [$email],
        'subject' => 'Verify your creator account',
        'html'    => "<p>Hi $name, your verification code is <strong>$otp</strong>.</p>",
    ]);
    $ch = curl_init('https://api.resend.com/emails');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . getenv('RESEND_API_KEY'),
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS     => $payload,
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$code >= 200 && $code < 300, $code >= 200 && $code < 300 ? null : $resp];
    */

    return [true, null];
}