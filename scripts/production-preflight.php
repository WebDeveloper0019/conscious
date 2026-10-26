<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This command can only be run from the command line.\n");
    exit(1);
}

$projectRoot = dirname(__DIR__);
$environmentFile = $projectRoot . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . 'release' . DIRECTORY_SEPARATOR . '.env';
foreach ($argv as $argument) {
    if (str_starts_with($argument, '--env=')) {
        $environmentFile = substr($argument, 6);
    }
}

function readEnvironment(string $path): array
{
    if (!is_file($path)) {
        return [];
    }
    $values = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $values[$key] = trim($value, "\"'");
    }
    return $values;
}

$env = readEnvironment($environmentFile);
$checks = [];
$check = static function (bool $passed, string $label) use (&$checks): void {
    $checks[] = $passed;
    echo ($passed ? '[PASS] ' : '[FAIL] ') . $label . PHP_EOL;
};
$value = static fn(string $key): string => trim((string)($env[$key] ?? getenv($key) ?: ''));

$check(version_compare(PHP_VERSION, '8.2.0', '>='), 'PHP 8.2 or newer');
foreach (['curl', 'fileinfo', 'json', 'mbstring', 'openssl', 'pdo_mysql'] as $extension) {
    $check(extension_loaded($extension), "PHP extension: {$extension}");
}
$check($value('APP_ENV') === 'production', 'APP_ENV is production');
$appUrl = $value('APP_URL');
$check(str_starts_with($appUrl, 'https://') && filter_var($appUrl, FILTER_VALIDATE_URL) !== false, 'APP_URL is a valid HTTPS URL');
$check(strlen($value('APP_KEY')) >= 32 && !str_contains(strtolower($value('APP_KEY')), 'replace'), 'APP_KEY is strong and configured');
$check($value('DB_HOST') !== '' && $value('DB_NAME') !== '', 'Database host and name are configured');
$check($value('DB_USER') !== '' && strtolower($value('DB_USER')) !== 'root', 'Database uses a dedicated non-root account');
$check($value('DB_PASS') !== '', 'Database password is configured');
$check($value('MAIL_TRANSPORT') === 'resend' && $value('RESEND_API_KEY') !== '', 'Transactional email is configured');
$check($value('RAZORPAY_KEY_ID') !== '' && $value('RAZORPAY_KEY_SECRET') !== '' && $value('RAZORPAY_WEBHOOK_SECRET') !== '', 'Razorpay and webhook credentials are configured');
$storage = $value('PRIVATE_STORAGE_PATH');
$check($storage !== '' && !preg_match('~(?:public_html|htdocs|www)(?:[/\\\\]|$)~i', $storage), 'Private storage is outside the public document root');
$releaseRoot = $projectRoot . '/build/release';
$publicRoot = $releaseRoot . '/public_html';
$privateRoot = $releaseRoot . '/private';
$check(is_dir($publicRoot) && is_dir($privateRoot . '/backend') && is_dir($privateRoot . '/storage'), 'Release uses public/private separation');
$check(is_file($privateRoot . '/backend/dependencies/autoload.php'), 'Private Composer dependencies are installed');
$check(
    !is_dir($publicRoot . '/api/dependencies')
    && !is_dir($publicRoot . '/api/database')
    && !is_dir($publicRoot . '/api/bin')
    && !is_dir($publicRoot . '/api/data'),
    'Backend dependencies, migrations, jobs and runtime data are private'
);
$forbiddenPublicDirectories = ['build', 'config', 'docs', 'outputs', 'scripts', 'storage', 'tests'];
$publicIsClean = true;
foreach ($forbiddenPublicDirectories as $directory) {
    if (is_dir($publicRoot . '/' . $directory)) {
        $publicIsClean = false;
        break;
    }
}
$check($publicIsClean, 'Development-only directories are absent from public_html');
$check(is_file($publicRoot . '/.htaccess') && is_file($publicRoot . '/api/.htaccess'), 'Web-server security rules are packaged');

$failed = count(array_filter($checks, static fn(bool $passed): bool => !$passed));
echo PHP_EOL . ($failed === 0 ? 'Preflight passed.' : "Preflight failed: {$failed} check(s) require attention.") . PHP_EOL;
exit($failed === 0 ? 0 : 1);
