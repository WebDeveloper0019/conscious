<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$projectRoot = dirname(__DIR__);
$releaseRoot = $projectRoot . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . 'release';
$publicRoot = $releaseRoot . DIRECTORY_SEPARATOR . 'public_html';
$privateRoot = $releaseRoot . DIRECTORY_SEPARATOR . 'private';
$failures = [];

$check = static function (bool $passed, string $message) use (&$failures): void {
    echo ($passed ? '[PASS] ' : '[FAIL] ') . $message . PHP_EOL;
    if (!$passed) {
        $failures[] = $message;
    }
};

$requiredDirectories = [
    $publicRoot,
    $publicRoot . '/api',
    $publicRoot . '/assets',
    $privateRoot . '/backend/bin',
    $privateRoot . '/backend/database/migrations',
    $privateRoot . '/backend/dependencies',
    $privateRoot . '/backend/lib',
    $privateRoot . '/storage/private',
    $privateRoot . '/storage/logs',
];
foreach ($requiredDirectories as $directory) {
    $check(is_dir($directory), 'Required directory: ' . str_replace($releaseRoot . DIRECTORY_SEPARATOR, '', $directory));
}

$requiredFiles = [
    $publicRoot . '/.htaccess',
    $publicRoot . '/index.php',
    $publicRoot . '/api/.htaccess',
    $publicRoot . '/api/config.php',
    $privateRoot . '/backend/config.php',
    $privateRoot . '/backend/dependencies/autoload.php',
    $releaseRoot . '/.env.example',
    $releaseRoot . '/RELEASE.txt',
];
foreach ($requiredFiles as $file) {
    $check(is_file($file), 'Required file: ' . str_replace($releaseRoot . DIRECTORY_SEPARATOR, '', $file));
}

$forbiddenPublicPaths = [
    '.git', 'build', 'config', 'docs', 'outputs', 'scripts', 'storage', 'tests',
    'api/bin', 'api/data', 'api/database', 'api/dependencies',
];
foreach ($forbiddenPublicPaths as $relative) {
    $check(!file_exists($publicRoot . '/' . $relative), 'Not publicly deployed: ' . $relative);
}

$sensitiveFiles = [];
$localPrefixFiles = [];
$developmentUrlFiles = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($publicRoot, FilesystemIterator::SKIP_DOTS)
);
foreach ($iterator as $item) {
    if (!$item->isFile()) {
        continue;
    }
    $relative = str_replace('\\', '/', substr($item->getPathname(), strlen($publicRoot) + 1));
    if (preg_match('~(?:^|/)(?:\.env(?:\..*)?|composer\.(?:json|lock)|.*\.(?:sql|log|md))$~i', $relative)) {
        $sensitiveFiles[] = $relative;
    }
    if (in_array(strtolower($item->getExtension()), ['css', 'html', 'js', 'json', 'php', 'svg', 'xml'], true)) {
        $contents = file_get_contents($item->getPathname());
        if ($contents !== false && str_contains($contents, '/conscious')) {
            $localPrefixFiles[] = $relative;
        }
        if ($contents !== false && preg_match('~(?:https?://(?:localhost|127\.0\.0\.1)|/conscious-main-new(?:/|\b))~i', $contents)) {
            $developmentUrlFiles[] = $relative;
        }
    }
}
$check($sensitiveFiles === [], 'No sensitive source or metadata files exist in public_html');
$check($localPrefixFiles === [], 'No local /conscious URL prefix remains in public files');
$check($developmentUrlFiles === [], 'No development host or legacy project URL remains in public files');
$productionHtaccess = file_get_contents($publicRoot . '/.htaccess') ?: '';
$check(!str_contains($productionHtaccess, 'XAMPP') && !str_contains($productionHtaccess, '^conscious'), 'Production rewrite rules contain no local-install compatibility route');

$privatePrefixFiles = [];
$privateIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($privateRoot . '/backend', FilesystemIterator::SKIP_DOTS));
foreach ($privateIterator as $item) {
    if (!$item->isFile() || !in_array(strtolower($item->getExtension()), ['css', 'html', 'js', 'json', 'php', 'sql', 'svg', 'xml'], true)) {
        continue;
    }
    $contents = file_get_contents($item->getPathname());
    if ($contents !== false && str_contains($contents, '/conscious')) {
        $privatePrefixFiles[] = str_replace('\\', '/', substr($item->getPathname(), strlen($privateRoot) + 1));
    }
}
$check($privatePrefixFiles === [], 'No local /conscious URL prefix remains in private backend or migrations');

$bridgeFiles = glob($publicRoot . '/api/lib/*.php') ?: [];
$bridgesAreThin = $bridgeFiles !== [];
foreach ($bridgeFiles as $bridge) {
    $contents = file_get_contents($bridge) ?: '';
    if (!str_contains($contents, '/private/backend/lib/') || substr_count($contents, PHP_EOL) > 4) {
        $bridgesAreThin = false;
        break;
    }
}
$check($bridgesAreThin, 'Public API library bridges contain no backend implementation');

echo PHP_EOL;
if ($failures !== []) {
    echo 'Release verification failed with ' . count($failures) . ' issue(s).' . PHP_EOL;
    exit(1);
}

echo 'Release structure verified successfully.' . PHP_EOL;
