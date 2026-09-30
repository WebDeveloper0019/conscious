<?php
declare(strict_types=1);
function encryption_key(): string { return hash('sha256',APP_KEY,true); }
function encrypt_sensitive(string $plaintext): string {
    $iv=random_bytes(12);$tag='';$cipher=openssl_encrypt($plaintext,'aes-256-gcm',encryption_key(),OPENSSL_RAW_DATA,$iv,$tag);
    if($cipher===false)throw new RuntimeException('Encryption failed.'); return base64_encode("\x01".$iv.$tag.$cipher);
}
function decrypt_sensitive(string $encoded): string {
    $raw=base64_decode($encoded,true);if($raw===false||strlen($raw)<29||$raw[0]!=="\x01")throw new RuntimeException('Invalid encrypted value.');
    $plain=openssl_decrypt(substr($raw,29),'aes-256-gcm',encryption_key(),OPENSSL_RAW_DATA,substr($raw,1,12),substr($raw,13,16));
    if($plain===false)throw new RuntimeException('Decryption failed.');return $plain;
}
function mask_account_number(string $number): string {$digits=preg_replace('/\D+/','',$number);return str_repeat('•',max(0,strlen($digits)-4)).substr($digits,-4);}
