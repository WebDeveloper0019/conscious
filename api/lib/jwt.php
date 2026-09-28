<?php
declare(strict_types=1);
function base64url_encode(string $data): string { return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); }
function base64url_decode(string $data): ?string {
    if (!preg_match('/^[A-Za-z0-9_-]+$/', $data)) return null;
    $data .= str_repeat('=', (4 - strlen($data) % 4) % 4);
    $decoded=base64_decode(strtr($data, '-_', '+/'), true); return $decoded===false?null:$decoded;
}
function jwt_encode(array $payload,string $secret): string {
    $header=base64url_encode((string)json_encode(['typ'=>'JWT','alg'=>'HS256']));
    $body=base64url_encode((string)json_encode($payload));
    return $header.'.'.$body.'.'.base64url_encode(hash_hmac('sha256',$header.'.'.$body,$secret,true));
}
function jwt_decode(?string $token,string $secret): ?array {
    if(!$token||strlen($token)>4096)return null; $parts=explode('.',$token); if(count($parts)!==3)return null;
    [$headerPart,$bodyPart,$signaturePart]=$parts; $headerJson=base64url_decode($headerPart); $bodyJson=base64url_decode($bodyPart); $signature=base64url_decode($signaturePart);
    if($headerJson===null||$bodyJson===null||$signature===null)return null;
    $header=json_decode($headerJson,true); if(!is_array($header)||($header['alg']??'')!=='HS256'||($header['typ']??'')!=='JWT')return null;
    $expected=hash_hmac('sha256',$headerPart.'.'.$bodyPart,$secret,true); if(!hash_equals($expected,$signature))return null;
    $payload=json_decode($bodyJson,true); if(!is_array($payload)||!isset($payload['sub'],$payload['exp'])||!is_string($payload['sub'])||!is_numeric($payload['exp'])||time()>=(int)$payload['exp'])return null;
    return $payload;
}
