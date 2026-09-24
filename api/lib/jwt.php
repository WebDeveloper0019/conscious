<?php
/**
 * Minimal HS256 JWT implementation — no Composer/library required.
 * Only supports what this app needs: sign a payload, verify + decode it.
 */

function base64url_encode(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode(string $data): string {
    $remainder = strlen($data) % 4;
    if ($remainder) {
        $data .= str_repeat('=', 4 - $remainder);
    }
    return base64_decode(strtr($data, '-_', '+/'));
}

/**
 * @param array $payload  Claims to encode. 'exp' (unix timestamp) is respected on decode.
 */
function jwt_encode(array $payload, string $secret): string {
    $header = ['typ' => 'JWT', 'alg' => 'HS256'];

    $segments = [
        base64url_encode(json_encode($header)),
        base64url_encode(json_encode($payload)),
    ];

    $signingInput = implode('.', $segments);
    $signature = hash_hmac('sha256', $signingInput, $secret, true);
    $segments[] = base64url_encode($signature);

    return implode('.', $segments);
}

/**
 * Returns the decoded payload array, or null if the token is missing,
 * malformed, has a bad signature, or is expired.
 */
function jwt_decode(?string $token, string $secret): ?array {
    if (!$token) {
        return null;
    }

    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return null;
    }

    [$headerB64, $payloadB64, $signatureB64] = $parts;

    $expectedSignature = hash_hmac('sha256', "$headerB64.$payloadB64", $secret, true);
    $actualSignature = base64url_decode($signatureB64);

    if (!hash_equals($expectedSignature, $actualSignature)) {
        return null;
    }

    $payload = json_decode(base64url_decode($payloadB64), true);
    if (!is_array($payload)) {
        return null;
    }

    if (isset($payload['exp']) && time() >= $payload['exp']) {
        return null; // expired
    }

    return $payload;
}
