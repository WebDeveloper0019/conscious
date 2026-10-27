<?php
declare(strict_types=1);

/**
 * Shared AI client — tries Cloudflare Workers AI first, falls back to Ollama.
 * Switch provider via AI_PROVIDER env var: 'cloudflare' or 'ollama'.
 */

function ai_complete(string $prompt, int $max_tokens = 512): ?string
{
    if (AI_PROVIDER === 'ollama') {
        return _ollama_complete($prompt, $max_tokens);
    }

    $result = _cf_complete($prompt, $max_tokens);

    // Auto-fallback to Ollama if Cloudflare fails and Ollama is configured
    if ($result === null && OLLAMA_BASE_URL !== '') {
        return _ollama_complete($prompt, $max_tokens);
    }

    return $result;
}

function _cf_complete(string $prompt, int $max_tokens): ?string
{
    if (CF_ACCOUNT_ID === '' || CF_AI_TOKEN === '') {
        return null;
    }

    $url = sprintf(
        'https://api.cloudflare.com/client/v4/accounts/%s/ai/run/@cf/meta/llama-3.1-8b-instruct',
        CF_ACCOUNT_ID
    );

    $payload = json_encode([
        'messages'   => [['role' => 'user', 'content' => $prompt]],
        'max_tokens' => $max_tokens,
        'temperature' => 0.7,
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . CF_AI_TOKEN,
            'Content-Type: application/json',
        ],
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || $response === false) {
        return null;
    }

    $data = json_decode($response, true);
    return isset($data['result']['response']) ? trim((string)$data['result']['response']) : null;
}

function _ollama_complete(string $prompt, int $max_tokens): ?string
{
    if (OLLAMA_BASE_URL === '') {
        return null;
    }

    $payload = json_encode([
        'model'   => 'llama3.1:8b',
        'prompt'  => $prompt,
        'stream'  => false,
        'options' => ['num_predict' => $max_tokens, 'temperature' => 0.7],
    ]);

    $ch = curl_init(OLLAMA_BASE_URL . '/api/generate');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || $response === false) {
        return null;
    }

    $data = json_decode($response, true);
    return isset($data['response']) ? trim((string)$data['response']) : null;
}
