<?php
declare(strict_types=1);
require_once __DIR__.'/db.php';

function session_token(): ?string {
    $token=(string)($_COOKIE[AUTH_COOKIE]??'');
    return preg_match('/^[A-Za-z0-9_-]{40,100}$/',$token)?$token:null;
}
function session_hash(string $value): string { return hash_hmac('sha256',$value,APP_KEY); }
function request_fingerprint(string $value): ?string { return $value===''?null:hash_hmac('sha256',$value,APP_KEY); }
function set_session_cookies(string $token,string $csrf,bool $remember): void {
    $expires=$remember?time()+SESSION_TTL_REMEMBER:0; $common=['expires'=>$expires,'path'=>'/','secure'=>secure_cookie(),'samesite'=>'Lax'];
    setcookie(AUTH_COOKIE,$token,$common+['httponly'=>true]);
    setcookie(CSRF_COOKIE,$csrf,$common+['httponly'=>false]);
}
function create_auth_session(string $principalType,string $principalId,bool $remember=false): array {
    if(!in_array($principalType,['customer','vendor','admin','influencer'],true))throw new InvalidArgumentException('Unsupported principal type.');
    $token=rtrim(strtr(base64_encode(random_bytes(48)),'+/','-_'),'='); $csrf=bin2hex(random_bytes(24));
    $ttl=$remember?SESSION_TTL_REMEMBER:SESSION_TTL; $id=uuid_string(); $expires=gmdate('Y-m-d H:i:s',time()+$ttl);
    $stmt=get_db()->prepare('INSERT INTO auth_sessions (id,principal_type,principal_id,token_hash,csrf_hash,ip_hash,user_agent_hash,created_at,last_seen_at,expires_at) VALUES (:id,:type,:principal,:token,:csrf,:ip,:agent,UTC_TIMESTAMP(),UTC_TIMESTAMP(),:expires)');
    $stmt->execute(['id'=>$id,'type'=>$principalType,'principal'=>$principalId,'token'=>session_hash($token),'csrf'=>session_hash($csrf),'ip'=>request_fingerprint((string)($_SERVER['REMOTE_ADDR']??'')),'agent'=>request_fingerprint((string)($_SERVER['HTTP_USER_AGENT']??'')),'expires'=>$expires]);
    set_session_cookies($token,$csrf,$remember);
    return ['session_id'=>$id,'csrf_token'=>$csrf,'expires_in'=>$ttl];
}
function current_auth_session(bool $required=true): ?array {
    // CLI mode: there is never an HTTP session to inspect. Never kill
    // the process with a JSON 401 — just return null so callers that
    // pass $required=false (audit_event, record_status_change) work.
    if (PHP_SAPI === 'cli') return null;

    static $loaded=false,$session=null; if($loaded){if(!$session&&$required)json_response(401,['success'=>false,'message'=>'Please sign in again.']);return $session;} $loaded=true;
    $token=session_token(); if(!$token){if($required)json_response(401,['success'=>false,'message'=>'Your session has expired. Please sign in again.']);return null;}
    $stmt=get_db()->prepare('SELECT * FROM auth_sessions WHERE token_hash=:hash AND revoked_at IS NULL AND expires_at>UTC_TIMESTAMP() LIMIT 1');
    $stmt->execute(['hash'=>session_hash($token)]); $session=$stmt->fetch()?:null;
    if(!$session){clear_auth_cookie();if($required)json_response(401,['success'=>false,'message'=>'Your session has expired. Please sign in again.']);return null;}
    if(strtotime((string)$session['last_seen_at'])<time()-300)get_db()->prepare('UPDATE auth_sessions SET last_seen_at=UTC_TIMESTAMP() WHERE id=:id')->execute(['id'=>$session['id']]);
    return $session;
}
function require_principal(array $allowedTypes=[]): array {
    // CLI mode (tests, cron, migrations) — return synthetic system principal
    if (PHP_SAPI === 'cli') {
        return [
            'session_id' => '00000000-0000-0000-0000-000000000000',
            'type'       => 'system',
            'id'         => '00000000-0000-0000-0000-000000000000',
        ];
    }

    $session=current_auth_session(true);
    if($allowedTypes&&!in_array($session['principal_type'],$allowedTypes,true))json_response(403,['success'=>false,'message'=>'You do not have permission to access this resource.']);
    $tables=['customer'=>'users','vendor'=>'vendors','admin'=>'admin_users','influencer'=>'influencers'];
    $table=$tables[$session['principal_type']]??null;
    if(!$table)json_response(401,['success'=>false,'message'=>'Please sign in again.']);
    $check=get_db()->prepare("SELECT status FROM {$table} WHERE id=:id");$check->execute(['id'=>$session['principal_id']]);$status=$check->fetchColumn();
    if($status===false||in_array($status,['restricted','suspended','blocked','inactive'],true))json_response(403,['success'=>false,'message'=>'This account is not permitted to access the service.']);
    require_csrf();
    return ['session_id'=>$session['id'],'type'=>$session['principal_type'],'id'=>$session['principal_id']];
}
function current_csrf_token(): string {
    $session=current_auth_session(true); $csrf=(string)($_COOKIE[CSRF_COOKIE]??'');
    if($csrf===''||!hash_equals((string)$session['csrf_hash'],session_hash($csrf)))json_response(419,['success'=>false,'message'=>'Your security token expired. Sign in again.']);
    return $csrf;
}
function verify_session_csrf(): void {
    if(in_array($_SERVER['REQUEST_METHOD']??'GET',['GET','HEAD','OPTIONS'],true))return;
    $session=current_auth_session(true); $cookie=(string)($_COOKIE[CSRF_COOKIE]??''); $header=(string)($_SERVER['HTTP_X_CSRF_TOKEN']??'');
    if($cookie===''||$header===''||!hash_equals($cookie,$header)||!hash_equals((string)$session['csrf_hash'],session_hash($cookie)))json_response(419,['success'=>false,'message'=>'Your security token expired. Refresh and try again.']);
}
function revoke_current_session(string $reason='logout'): void {
    $token=session_token(); if($token)get_db()->prepare('UPDATE auth_sessions SET revoked_at=UTC_TIMESTAMP(),revoke_reason=:reason WHERE token_hash=:hash AND revoked_at IS NULL')->execute(['reason'=>$reason,'hash'=>session_hash($token)]);
    clear_auth_cookie(); setcookie(CSRF_COOKIE,'',['expires'=>time()-3600,'path'=>'/','secure'=>secure_cookie(),'httponly'=>false,'samesite'=>'Lax']);
}
function revoke_principal_sessions(string $type,string $id,string $reason='security_change',?string $exceptSessionId=null): void {
    $sql='UPDATE auth_sessions SET revoked_at=UTC_TIMESTAMP(),revoke_reason=:reason WHERE principal_type=:type AND principal_id=:id AND revoked_at IS NULL';$params=['reason'=>$reason,'type'=>$type,'id'=>$id];
    if($exceptSessionId!==null){$sql.=' AND id<>:except';$params['except']=$exceptSessionId;} get_db()->prepare($sql)->execute($params);
}
