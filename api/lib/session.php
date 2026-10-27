<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';

if (!function_exists('session_token')) {
    function session_token(): ?string {
        $token=(string)($_COOKIE[AUTH_COOKIE]??'');
        return preg_match('/^[A-Za-z0-9_-]{40,100}$/',$token)?$token:null;
    }
}

if (!function_exists('session_hash')) {
    function session_hash(string $value): string { return hash('sha256', $value); }
}

if (!function_exists('request_fingerprint')) {
    function request_fingerprint(string $value): ?string { return $value===''?null:hash('sha256', $value); }
}

if (!function_exists('set_session_cookies')) {
    function set_session_cookies(string $token, string $csrf, bool $remember): void {
        $expires=$remember?time()+SESSION_TTL_REMEMBER:0;
        $common=['expires'=>$expires,'path'=>'/','secure'=>false,'samesite'=>'Lax'];
        setcookie(AUTH_COOKIE,$token,$common+['httponly'=>true]);
        setcookie(CSRF_COOKIE,$csrf,$common+['httponly'=>false]);
    }
}

if (!function_exists('create_auth_session')) {
    function create_auth_session(string $principalType,string $principalId,bool $remember=false): array {
        $token=bin2hex(random_bytes(32)); $csrf=bin2hex(random_bytes(24));
        $ttl=$remember?SESSION_TTL_REMEMBER:SESSION_TTL; $id=uuid_string();
        $expires=gmdate('Y-m-d H:i:s',time()+$ttl);
        
        $stmt=get_db()->prepare(
            'INSERT INTO auth_sessions
               (id,token_hash,principal_type,principal_id,csrf_token,remember,ip,user_agent,expires_at,created_at)
             VALUES (:id,:token,:type,:principal,:csrf,:rem,:ip,:ua,:expires,UTC_TIMESTAMP())'
        );
        $stmt->execute([
            'id'=>$id,
            'token'=>hash('sha256', $token),
            'type'=>$principalType,
            'principal'=>$principalId,
            'csrf'=>$csrf,
            'rem'=>$remember?1:0,
            'ip'=>$_SERVER['REMOTE_ADDR'] ?? null,
            'ua'=>$_SERVER['HTTP_USER_AGENT'] ?? null,
            'expires'=>$expires,
        ]);
        set_session_cookies($token,$csrf,$remember);
        return ['session_id'=>$id,'csrf_token'=>$csrf,'expires_in'=>$ttl];
    }
}

if (!function_exists('current_auth_session')) {
    function current_auth_session(bool $required=true): ?array {
        if (PHP_SAPI === 'cli') return null;
        
        $token=session_token(); 
        if(!$token){
            if($required) json_response(401,['success'=>false,'message'=>'Your session has expired. Please sign in again.']);
            return null;
        }
        
        $stmt=get_db()->prepare('SELECT * FROM auth_sessions WHERE token_hash=:hash AND expires_at>UTC_TIMESTAMP() LIMIT 1');
        $stmt->execute(['hash'=>hash('sha256', $token)]); 
        $session=$stmt->fetch()?:null;
        
        if(!$session){
            if($required) json_response(401,['success'=>false,'message'=>'Your session has expired. Please sign in again.']);
            return null;
        }
        return $session;
    }
}

if (!function_exists('require_principal')) {
    function require_principal(array $allowedTypes=[]): array {
        $session=current_auth_session(true);
        if($allowedTypes && !in_array($session['principal_type'],$allowedTypes,true)) {
            json_response(403,['success'=>false,'message'=>'You do not have permission to access this resource.']);
        }
        
        if (!in_array($_SERVER['REQUEST_METHOD']??'GET', ['GET','HEAD','OPTIONS'], true)) {
            $sent = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            if (!hash_equals((string)$session['csrf_token'], $sent)) {
                json_response(403, ['success'=>false, 'message'=>'Invalid CSRF token.']);
            }
        }
        
        return ['session_id'=>$session['id'],'type'=>$session['principal_type'],'id'=>$session['principal_id']];
    }
}

if (!function_exists('current_csrf_token')) {
    function current_csrf_token(): string {
        $session=current_auth_session(true); 
        $csrf=(string)($_COOKIE[CSRF_COOKIE]??'');
        if($csrf==='' || !hash_equals((string)$session['csrf_token'], $csrf)) {
            json_response(419,['success'=>false,'message'=>'Your security token expired. Sign in again.']);
        }
        return $csrf;
    }
}