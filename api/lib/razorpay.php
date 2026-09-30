<?php
declare(strict_types=1);
require_once __DIR__.'/helpers.php';
function razorpay_is_configured(): bool { return PAYMENT_GATEWAY==='razorpay' && RAZORPAY_KEY_ID!=='' && RAZORPAY_KEY_SECRET!==''; }
function razorpay_request(string $method,string $path,array $payload=[]): array {
    if(!razorpay_is_configured())throw new RuntimeException('Razorpay is not configured.');
    if(!function_exists('curl_init'))throw new RuntimeException('PHP cURL is required for Razorpay.');
    $ch=curl_init('https://api.razorpay.com/v1/'.ltrim($path,'/'));$options=[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CUSTOMREQUEST=>strtoupper($method),CURLOPT_USERPWD=>RAZORPAY_KEY_ID.':'.RAZORPAY_KEY_SECRET,CURLOPT_HTTPAUTH=>CURLAUTH_BASIC,CURLOPT_TIMEOUT=>20,CURLOPT_HTTPHEADER=>['Content-Type: application/json','Accept: application/json']];
    if($payload!==[])$options[CURLOPT_POSTFIELDS]=json_encode($payload,JSON_UNESCAPED_SLASHES);curl_setopt_array($ch,$options);$response=curl_exec($ch);$error=curl_error($ch);$status=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);if($response===false)throw new RuntimeException('Razorpay connection failed: '.$error);$data=json_decode($response,true);if($status<200||$status>=300)throw new RuntimeException((string)($data['error']['description']??'Razorpay returned an error.'));return is_array($data)?$data:[];
}
function razorpay_signature(string $orderId,string $paymentId): string { return hash_hmac('sha256',$orderId.'|'.$paymentId,RAZORPAY_KEY_SECRET); }
