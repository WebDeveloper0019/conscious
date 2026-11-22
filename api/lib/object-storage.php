<?php
declare(strict_types=1);

function r2_is_configured(): bool
{
    return R2_ACCOUNT_ID !== '' && R2_ACCESS_KEY_ID !== '' && R2_SECRET_ACCESS_KEY !== '' && R2_BUCKET !== '';
}

function r2_endpoint(): string
{
    return R2_ENDPOINT !== '' ? R2_ENDPOINT : 'https://' . R2_ACCOUNT_ID . '.r2.cloudflarestorage.com';
}

function r2_encode_path(string $path): string
{
    return implode('/', array_map('rawurlencode', explode('/', trim($path, '/'))));
}

function r2_signing_key(string $date): string
{
    $dateKey = hash_hmac('sha256', $date, 'AWS4' . R2_SECRET_ACCESS_KEY, true);
    $regionKey = hash_hmac('sha256', 'auto', $dateKey, true);
    $serviceKey = hash_hmac('sha256', 's3', $regionKey, true);
    return hash_hmac('sha256', 'aws4_request', $serviceKey, true);
}

function r2_request(string $method, string $key, string $payload = '', string $contentType = 'application/octet-stream'): array
{
    if (!r2_is_configured()) throw new RuntimeException('R2 document storage is not configured.');
    if (!function_exists('curl_init')) throw new RuntimeException('The cURL PHP extension is required for R2 storage.');

    $endpoint = r2_endpoint();
    $parts = parse_url($endpoint);
    $host = (string)($parts['host'] ?? '');
    if ($host === '') throw new RuntimeException('The R2 endpoint is invalid.');

    $canonicalUri = '/' . r2_encode_path(R2_BUCKET) . '/' . r2_encode_path($key);
    $url = $endpoint . $canonicalUri;
    $now = gmdate('Ymd\THis\Z');
    $date = substr($now, 0, 8);
    $payloadHash = hash('sha256', $payload);
    $canonicalHeaders = 'content-type:' . $contentType . "\n" . 'host:' . $host . "\n" . 'x-amz-content-sha256:' . $payloadHash . "\n" . 'x-amz-date:' . $now . "\n";
    $signedHeaders = 'content-type;host;x-amz-content-sha256;x-amz-date';
    $canonicalRequest = $method . "\n" . $canonicalUri . "\n\n" . $canonicalHeaders . $signedHeaders . "\n" . $payloadHash;
    $scope = $date . '/auto/s3/aws4_request';
    $stringToSign = 'AWS4-HMAC-SHA256' . "\n" . $now . "\n" . $scope . "\n" . hash('sha256', $canonicalRequest);
    $signature = hash_hmac('sha256', $stringToSign, r2_signing_key($date));
    $authorization = 'AWS4-HMAC-SHA256 Credential=' . R2_ACCESS_KEY_ID . '/' . $scope . ', SignedHeaders=' . $signedHeaders . ', Signature=' . $signature;

    $handle = curl_init($url);
    curl_setopt_array($handle, [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => [
            'Authorization: ' . $authorization,
            'Content-Type: ' . $contentType,
            'Host: ' . $host,
            'X-Amz-Content-Sha256: ' . $payloadHash,
            'X-Amz-Date: ' . $now,
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HEADER => true,
    ]);
    if ($method === 'PUT') curl_setopt($handle, CURLOPT_POSTFIELDS, $payload);
    $response = curl_exec($handle);
    if ($response === false) {
        $message = curl_error($handle);
        curl_close($handle);
        throw new RuntimeException('R2 request failed: ' . $message);
    }
    $status = (int)curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
    $headerSize = (int)curl_getinfo($handle, CURLINFO_HEADER_SIZE);
    curl_close($handle);
    $body = substr((string)$response, $headerSize);
    if ($status < 200 || $status >= 300) throw new RuntimeException('R2 request failed with status ' . $status . '.');
    return ['status' => $status, 'body' => $body];
}

function r2_put_object(string $key, string $localPath, string $contentType): void
{
    $payload = file_get_contents($localPath);
    if ($payload === false) throw new RuntimeException('The uploaded document could not be read.');
    r2_request('PUT', $key, $payload, $contentType);
}

function r2_get_object(string $key): string
{
    return (string)r2_request('GET', $key)['body'];
}

function r2_delete_object(string $key): void
{
    r2_request('DELETE', $key);
}

function file_asset_provider(array $asset): string
{
    return strtolower((string)($asset['storage_provider'] ?? 'local')) === 'r2' ? 'r2' : 'local';
}

function file_asset_local_path(array $asset): ?string
{
    if (file_asset_provider($asset) !== 'local') return null;
    $base = realpath(PRIVATE_STORAGE_PATH);
    $candidate = rtrim(PRIVATE_STORAGE_PATH, '/\\') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, (string)$asset['storage_key']);
    $resolved = realpath($candidate);
    if ($base === false || $resolved === false || !str_starts_with($resolved, $base . DIRECTORY_SEPARATOR) || !is_file($resolved)) return null;
    return $resolved;
}

function file_asset_read(array $asset): ?string
{
    if (file_asset_provider($asset) === 'r2') return r2_get_object((string)$asset['storage_key']);
    $path = file_asset_local_path($asset);
    if ($path === null) return null;
    $contents = file_get_contents($path);
    return $contents === false ? null : $contents;
}

function file_asset_delete_object(array $asset): void
{
    if (file_asset_provider($asset) === 'r2') {
        r2_delete_object((string)$asset['storage_key']);
        return;
    }
    $path = file_asset_local_path($asset);
    if ($path !== null) @unlink($path);
}
