<?php
declare(strict_types=1);
require_once __DIR__.'/authorization.php';

if (!function_exists('public_admin')) {
    function public_admin(array $admin): array {
        return [
            'id'=>$admin['id'],
            'full_name'=>$admin['full_name'],
            'email'=>$admin['email'],
            'status'=>$admin['status'],
            'last_login_at'=>$admin['last_login_at']
        ];
    }
}

if (!function_exists('require_admin')) {
    function require_admin(?string $permission=null): array {
        $principal = $permission ? require_permission($permission,['admin']) : require_principal(['admin']);
        $stmt = get_db()->prepare('SELECT * FROM admin_users WHERE id=:id AND status="active" LIMIT 1');
        $stmt->execute(['id'=>$principal['id']]);
        $admin = $stmt->fetch();
        if(!$admin) json_response(403,['success'=>false,'message'=>'Administrator access is not active.']);
        return ['principal'=>$principal,'admin'=>$admin];
    }
}

if (!function_exists('base32_decode_secret')) {
    function base32_decode_secret(string $secret): string {
        $alphabet='ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret=strtoupper(preg_replace('/[^A-Z2-7]/','',$secret));
        $bits='';
        foreach(str_split($secret) as $char){
            $pos=strpos($alphabet,$char);
            if($pos===false)return '';
            $bits.=str_pad(decbin($pos),5,'0',STR_PAD_LEFT);
        }
        $out='';
        for($i=0;$i+8<=strlen($bits);$i+=8)$out.=chr(bindec(substr($bits,$i,8)));
        return $out;
    }
}

if (!function_exists('verify_totp')) {
    function verify_totp(string $secret,string $code,int $window=1): bool {
        $key=base32_decode_secret($secret);
        if($key===''||!preg_match('/^[0-9]{6}$/',$code))return false;
        $counter=(int)floor(time()/30);
        for($offset=-$window;$offset<=$window;$offset++){
            $value=$counter+$offset;
            $binary=pack('N*',0).pack('N*',$value);
            $hash=hash_hmac('sha1',$binary,$key,true);
            $index=ord(substr($hash,-1))&0x0f;
            $number=((ord($hash[$index])&0x7f)<<24)|((ord($hash[$index+1])&0xff)<<16)|((ord($hash[$index+2])&0xff)<<8)|(ord($hash[$index+3])&0xff);
            $expected=str_pad((string)($number%1000000),6,'0',STR_PAD_LEFT);
            if(hash_equals($expected,$code))return true;
        }
        return false;
    }
}