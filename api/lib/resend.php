<?php
require_once __DIR__ . '/../config.php';

function resend_send_email(string $to, string $subject, string $html): array {
    if (MAIL_TRANSPORT === 'log' && APP_ENV === 'local') {
        $directory=__DIR__.'/../data/mail-outbox';if(!is_dir($directory)&&!mkdir($directory,0775,true)&&!is_dir($directory))return[false,'Local mail outbox could not be created.'];
        $id=gmdate('YmdHis').'-'.bin2hex(random_bytes(6));$payload=['id'=>$id,'to'=>$to,'subject'=>$subject,'html'=>$html,'created_at'=>gmdate(DATE_ATOM)];
        if(file_put_contents($directory.'/'.$id.'.json',json_encode($payload,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES))===false)return[false,'Local mail could not be written.'];return[true,$id];
    }
    if (RESEND_API_KEY === '') return [false, 'RESEND_API_KEY is not configured.'];
    $payload = json_encode([
        'from' => RESEND_FROM_NAME . ' <' . RESEND_FROM . '>',
        'to' => [$to],
        'subject' => $subject,
        'html' => $html,
    ]);
    if (!function_exists('curl_init')) return [false, 'PHP cURL extension is not enabled.'];
    $ch = curl_init('https://api.resend.com/emails');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . RESEND_API_KEY,
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_TIMEOUT => 20,
    ]);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($response === false) return [false, 'Resend connection failed: ' . $error];
    $data = json_decode($response, true);
    if ($status < 200 || $status >= 300) {
        return [false, $data['message'] ?? ('Resend returned HTTP ' . $status)];
    }
    return [true, $data['id'] ?? ''];
}

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

function send_vendor_otp_email(string $to,string $name,string $otp): array {
    $safeName=htmlspecialchars($name,ENT_QUOTES,'UTF-8');
    $html='<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:32px">'
        .'<h2>Verify your Conscious India vendor account</h2><p>Hello '.$safeName.',</p>'
        .'<p>Your six-digit verification code is:</p><div style="font-size:32px;font-weight:700;letter-spacing:8px;padding:18px 0">'.$otp.'</div>'
        .'<p>This code expires in 10 minutes. Conscious India will never ask for your password or banking PIN.</p></div>';
    return resend_send_email($to,'Verify your Conscious India vendor account',$html);
}

function send_vendor_application_review_email(string $to,string $name,string $reference,string $status,string $reason=''): array {
    $safeName=htmlspecialchars($name,ENT_QUOTES,'UTF-8');
    $safeReference=htmlspecialchars($reference,ENT_QUOTES,'UTF-8');
    $safeReason=nl2br(htmlspecialchars($reason,ENT_QUOTES,'UTF-8'));
    $labels=[
        'changes_requested'=>'Changes are needed in your vendor application',
        'rejected'=>'Your vendor application was not approved',
        'approved'=>'Your vendor application was approved',
    ];
    $title=$labels[$status]??'Your vendor application was updated';
    $action=$status==='changes_requested'
        ? '<p>Sign in, open your application, replace the rejected document or correct the requested information, then submit it again for verification.</p>'
        : '';
    $reasonBlock=$reason!==''?'<div style="margin:20px 0;padding:16px;border-left:4px solid #A63D24;background:#fff4ef"><strong>Review reason</strong><p style="margin:8px 0 0">'.$safeReason.'</p></div>':'';
    $base=APP_URL!==''?APP_URL:'/conscious';
    $url=htmlspecialchars(rtrim($base,'/').'/partner/onboarding.php',ENT_QUOTES,'UTF-8');
    $html='<div style="font-family:Arial,sans-serif;max-width:600px;margin:auto;padding:32px;color:#20352a">'
        .'<h2>'.$title.'</h2><p>Hello '.$safeName.',</p>'
        .'<p>Application reference: <strong>'.$safeReference.'</strong></p>'
        .$reasonBlock.$action
        .($status==='changes_requested'?'<p><a href="'.$url.'" style="display:inline-block;padding:12px 18px;background:#A63D24;color:#fff;text-decoration:none;border-radius:8px">Correct and resubmit</a></p>':'')
        .'<p>Conscious India will never ask for your password, OTP, or banking PIN during review.</p></div>';
    return resend_send_email($to,$title.' — '.$safeReference,$html);
}

function send_influencer_otp_email(string $to,string $name,string $otp): array {
    $safeName=htmlspecialchars($name,ENT_QUOTES,'UTF-8');
    $html='<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;padding:32px"><h2>Verify your Conscious India creator account</h2><p>Hello '.$safeName.',</p><p>Your six-digit verification code is:</p><div style="font-size:32px;font-weight:700;letter-spacing:8px;padding:18px 0">'.$otp.'</div><p>This code expires in 10 minutes. Never share your password or banking PIN.</p></div>';
    return resend_send_email($to,'Verify your Conscious India creator account',$html);
}
