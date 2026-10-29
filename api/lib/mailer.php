<?php
// Simple .env loader for XAMPP local development
if (file_exists(__DIR__ . '/../../.env')) {
    $lines = file(__DIR__ . '/../../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim($value));
    }
}

if (!function_exists('send_email')) {
    function send_email(string $to, string $subject, string $htmlContent): bool {
        $apiKey = getenv('RESEND_API_KEY');
        $fromEmail = getenv('RESEND_FROM');
        $fromName = getenv('RESEND_FROM_NAME') ?: 'Conscious India';

        if (!$apiKey || !$fromEmail) {
            error_log("Email failed: Missing Resend API key or From address.");
            return false;
        }

        $payload = [
            'from' => "{$fromName} <{$fromEmail}>",
            'to' => [$to],
            'subject' => $subject,
            'html' => $htmlContent
        ];

        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return true;
        } else {
            error_log("Resend API Error: " . $response);
            return false;
        }
    }
}