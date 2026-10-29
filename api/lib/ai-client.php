<?php
declare(strict_types=1);

function ai_provider_ready(): bool {
    if (AI_PROVIDER === 'cloudflare') return CF_ACCOUNT_ID !== '' && CF_AI_TOKEN !== '';
    if (AI_PROVIDER === 'ollama') {
        $host = strtolower((string)parse_url(OLLAMA_BASE_URL, PHP_URL_HOST));
        return OLLAMA_BASE_URL !== '' && in_array($host, ['localhost', '127.0.0.1', '::1'], true);
    }
    return false;
}

function ai_complete(string $system, string $prompt, int $maxTokens = 512): ?string {
    if (!ai_provider_ready() || !function_exists('curl_init')) return null;
    if (AI_PROVIDER === 'cloudflare') {
        $url = 'https://api.cloudflare.com/client/v4/accounts/' . rawurlencode(CF_ACCOUNT_ID) . '/ai/run/@cf/meta/llama-3.1-8b-instruct';
        $payload = ['messages'=>[['role'=>'system','content'=>$system],['role'=>'user','content'=>$prompt]],'max_tokens'=>$maxTokens,'temperature'=>0.5];
        $headers = ['Authorization: Bearer ' . CF_AI_TOKEN, 'Content-Type: application/json'];
    } else {
        $url = OLLAMA_BASE_URL . '/api/generate';
        $payload = ['model'=>'llama3.1:8b','prompt'=>$system . "\n\n" . $prompt,'stream'=>false,'options'=>['num_predict'=>$maxTokens,'temperature'=>0.5]];
        $headers = ['Content-Type: application/json'];
    }
    $encoded = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($encoded === false) return null;
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$encoded,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>45,CURLOPT_HTTPHEADER=>$headers]);
    $response = curl_exec($ch); $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    if ($response === false || $status < 200 || $status >= 300) return null;
    $data = json_decode((string)$response, true);
    $value = AI_PROVIDER === 'cloudflare' ? ($data['result']['response'] ?? null) : ($data['response'] ?? null);
    return is_string($value) && trim($value) !== '' ? trim($value) : null;
}
