<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../config.php';

if (($argv[1] ?? '') !== '--confirm-live-test-mailboxes' || count($argv) !== 2 || APP_ENV !== 'production') {
    fwrite(STDERR, "Usage (production SSH only): php create-test-mailboxes.php --confirm-live-test-mailboxes\n");
    exit(1);
}

$folder = rtrim(PRIVATE_STORAGE_PATH, '/\\') . '/test-accounts';
if (!is_dir($folder) && !mkdir($folder, 0700, true) && !is_dir($folder)) throw new RuntimeException('Private storage unavailable.');
$path = $folder . '/mailboxes.json';
$lock = fopen($folder . '/mailboxes.lock', 'c');
if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) throw new RuntimeException('Mailbox provisioning is already running.');

function mailtm(string $method, string $path, ?array $payload = null): array
{
    $handle = curl_init('https://api.mail.tm' . $path);
    curl_setopt_array($handle, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => ['Accept: application/json', 'Content-Type: application/json'],
        CURLOPT_TIMEOUT => 25, CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => false,
    ]);
    if ($payload !== null) curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($payload, JSON_THROW_ON_ERROR));
    $body = curl_exec($handle);
    $status = (int)curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
    curl_close($handle);
    return [$status, is_string($body) ? json_decode($body, true) : null];
}

function save_mailboxes(string $path, array $data): void
{
    $temporary = $path . '.tmp';
    if (file_put_contents($temporary, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), LOCK_EX) === false
        || !chmod($temporary, 0600) || !rename($temporary, $path)) {
        throw new RuntimeException('Could not save mailbox credentials privately.');
    }
}

try {
    if (is_file($path)) {
        $data = json_decode((string)file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data) || ($data['provider'] ?? '') !== 'mail.tm' || !is_array($data['accounts'] ?? null)
            || !array_is_list($data['accounts']) || count($data['accounts']) > 20) throw new RuntimeException('Existing mailbox file is invalid.');
    } else {
        [$status, $domains] = mailtm('GET', '/domains');
        if ($status !== 200 || !is_array($domains)) throw new RuntimeException('Temporary-mail domains are unavailable.');
        $list = $domains['hydra:member'] ?? $domains['member'] ?? [];
        $domain = null;
        foreach ($list as $candidate) {
            if (!empty($candidate['isActive']) && empty($candidate['isPrivate'])) { $domain = (string)$candidate['domain']; break; }
        }
        if (!$domain || !preg_match('/^[a-z0-9.-]+$/i', $domain)) throw new RuntimeException('No public temporary-mail domain is available.');
        $data = ['provider' => 'mail.tm', 'domain' => $domain, 'accounts' => []];
        save_mailboxes($path, $data);
    }

    for ($index = count($data['accounts']); $index < 20; $index++) {
        $role = $index < 10 ? 'customer' : 'vendor';
        $number = $index < 10 ? $index + 1 : $index - 9;
        $address = sprintf('ci-%s-%02d-%s@%s', $role === 'customer' ? 'cust' : 'vend', $number, bin2hex(random_bytes(5)), $data['domain']);
        $mailboxPassword = 'Ci!' . bin2hex(random_bytes(16));
        $created = false;
        for ($attempt = 0; $attempt < 5; $attempt++) {
            [$status, $reply] = mailtm('POST', '/accounts', ['address' => $address, 'password' => $mailboxPassword]);
            if ($status === 201 && is_array($reply) && !empty($reply['id'])) { $created = true; break; }
            if ($status !== 429 && $status !== 500 && $status !== 503) break;
            sleep(min(60, 10 * ($attempt + 1)));
        }
        if (!$created) throw new RuntimeException('Mailbox ' . ($index + 1) . ' could not be created (HTTP ' . $status . '). Resume later without losing earlier inboxes.');
        $data['accounts'][] = ['role' => $role, 'email' => $address, 'mailbox_password' => $mailboxPassword, 'mailbox_id' => (string)$reply['id']];
        save_mailboxes($path, $data);
        fwrite(STDOUT, 'Created temporary inbox ' . ($index + 1) . "/20\n");
        usleep(300000);
    }
    fwrite(STDOUT, "MAILBOXES_READY: 20\nPRIVATE_MAILBOX_FILE: {$path}\n");
    fwrite(STDOUT, "Temporary inboxes are for test accounts only. See https://mail.tm/ for provider access.\n");
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . "\n");
    exit(1);
} finally {
    flock($lock, LOCK_UN);
    fclose($lock);
}
