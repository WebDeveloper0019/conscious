<?php
declare(strict_types=1);

// Run with `php -n tests/ai-client-smoke.php cloudflare` (also: ollama,
// disabled, ollama-remote). Stub the provider boundary without API credentials.
if (PHP_SAPI !== 'cli' || extension_loaded('curl')) {
    fwrite(STDERR, "Run this isolated test with php -n.\n");
    exit(1);
}
$scenario = $argv[1] ?? 'cloudflare';
define('AI_PROVIDER', str_starts_with($scenario, 'ollama') ? 'ollama' : $scenario);
define('CF_ACCOUNT_ID', str_repeat('a', 32));
define('CF_AI_TOKEN', 'isolated-test-token');
define('CF_AI_MODEL', '@cf/meta/llama-3.3-70b-instruct-fp8-fast');
define('OLLAMA_BASE_URL', $scenario === 'ollama-remote' ? 'https://example.test' : 'http://127.0.0.1:11434');
foreach (['CURLOPT_RETURNTRANSFER','CURLOPT_POST','CURLOPT_POSTFIELDS','CURLOPT_CONNECTTIMEOUT','CURLOPT_TIMEOUT','CURLOPT_HTTPHEADER','CURLINFO_HTTP_CODE'] as $index => $name) define($name, $index + 1);
$calls = [];
$testStatus = 200;
$testResponse = json_encode(AI_PROVIDER === 'cloudflare' ? ['result' => ['response' => ' Hello Puji ']] : ['response' => ' Hello Puji ']);
function curl_init(string $url): object { return (object)['url' => $url, 'options' => []]; }
function curl_setopt_array(object $handle, array $options): bool { $handle->options = $options; return true; }
function curl_exec(object $handle): string|false { $GLOBALS['calls'][] = $handle; return $GLOBALS['testResponse']; }
function curl_getinfo(object $handle, int $option): int { return $GLOBALS['testStatus']; }
function curl_close(object $handle): void {}
function check(bool $condition, string $label): void {
    if (!$condition) throw new RuntimeException('FAIL ' . $label);
    echo 'PASS ', $label, PHP_EOL;
}
require __DIR__ . '/../api/lib/ai-client.php';
if (in_array($scenario, ['disabled', 'ollama-remote'], true)) {
    check(!ai_provider_ready(), 'unavailable provider remains disabled');
    check(ai_complete('System', 'Hello') === null && $calls === [], 'disabled provider makes no network request');
    exit;
}
check(ai_complete('System', 'Hello', 77) === 'Hello Puji', 'provider response is parsed and trimmed');
$call = $calls[0];
$payload = json_decode($call->options[CURLOPT_POSTFIELDS], true, 512, JSON_THROW_ON_ERROR);
if (AI_PROVIDER === 'cloudflare') {
    check($call->url === 'https://api.cloudflare.com/client/v4/accounts/' . CF_ACCOUNT_ID . '/ai/run/%40cf/meta/llama-3.3-70b-instruct-fp8-fast', 'configured model uses the fixed Cloudflare endpoint');
    check($payload['messages'] === [['role' => 'system', 'content' => 'System'], ['role' => 'user', 'content' => 'Hello']] && $payload['max_tokens'] === 77, 'existing Cloudflare message contract is preserved');
    check(in_array('Authorization: Bearer ' . CF_AI_TOKEN, $call->options[CURLOPT_HTTPHEADER], true), 'API token is sent only in the authorization header');
} else {
    check($call->url === 'http://127.0.0.1:11434/api/generate', 'local Ollama endpoint is preserved');
    check($payload['model'] === 'llama3.1:8b' && $payload['options']['num_predict'] === 77 && $payload['stream'] === false, 'local Ollama model and request contract are preserved');
}
$testStatus = 401;
check(ai_complete('System', 'Hello') === null, 'provider HTTP failure stays unavailable');
$testStatus = 200;
$testResponse = 'invalid json';
check(ai_complete('System', 'Hello') === null, 'malformed provider response is rejected');
$testResponse = false;
check(ai_complete('System', 'Hello') === null, 'network failure stays unavailable');
$testResponse = json_encode(AI_PROVIDER === 'cloudflare' ? ['result' => ['response' => ' ']] : ['response' => ' ']);
check(ai_complete('System', 'Hello') === null, 'empty provider response is rejected');
