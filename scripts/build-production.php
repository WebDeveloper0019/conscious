<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This command can only be run from the command line.\n");
    exit(1);
}

$projectRoot = dirname(__DIR__);
$buildRoot = $projectRoot . DIRECTORY_SEPARATOR . 'build';
$releaseRoot = $buildRoot . DIRECTORY_SEPARATOR . 'release';
$publicRoot = $releaseRoot . DIRECTORY_SEPARATOR . 'public_html';
$privateRoot = $releaseRoot . DIRECTORY_SEPARATOR . 'private';
$backendRoot = $privateRoot . DIRECTORY_SEPARATOR . 'backend';

function ensureDirectory(string $path): void
{
    if (!is_dir($path) && !mkdir($path, 0775, true) && !is_dir($path)) {
        throw new RuntimeException("Unable to create directory: {$path}");
    }
}

function removeTree(string $path, string $allowedParent): void
{
    $normalizedPath = str_replace('\\', '/', $path);
    $normalizedParent = rtrim(str_replace('\\', '/', $allowedParent), '/');
    if ($normalizedPath !== $normalizedParent . '/release') {
        throw new RuntimeException('Refusing to remove an unexpected path.');
    }
    if (!is_dir($path)) {
        return;
    }
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($iterator as $item) {
        $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    }
    rmdir($path);
}

function copyFile(string $source, string $destination): void
{
    ensureDirectory(dirname($destination));
    if (!copy($source, $destination)) {
        throw new RuntimeException("Unable to copy: {$source}");
    }
}

/** @param callable(string, SplFileInfo): bool|null $include */
function copyTree(string $source, string $destination, ?callable $include = null): void
{
    if (!is_dir($source)) {
        return;
    }
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($iterator as $item) {
        $relative = str_replace('\\', '/', substr($item->getPathname(), strlen($source) + 1));
        if ($include !== null && !$include($relative, $item)) {
            continue;
        }
        $target = $destination . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
        $item->isDir() ? ensureDirectory($target) : copyFile($item->getPathname(), $target);
    }
}

function normalizeProductionBasePath(string $root): int
{
    $updated = 0;
    $textExtensions = ['css', 'html', 'js', 'json', 'php', 'sql', 'svg', 'xml'];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $item) {
        if (!$item->isFile() || !in_array(strtolower($item->getExtension()), $textExtensions, true)) {
            continue;
        }

        $contents = file_get_contents($item->getPathname());
        if ($contents === false || !str_contains($contents, '/conscious')) {
            continue;
        }

        $normalized = str_replace('/conscious', '', $contents);
        if ($normalized !== $contents) {
            file_put_contents($item->getPathname(), $normalized);
            $updated++;
        }
    }

    return $updated;
}

removeTree($releaseRoot, $buildRoot);
ensureDirectory($publicRoot);
ensureDirectory($backendRoot);
foreach (['private', 'logs', 'cache', 'mail-outbox', 'rate-limit'] as $directory) {
    ensureDirectory($privateRoot . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . $directory);
}

copyFile($projectRoot . DIRECTORY_SEPARATOR . '.htaccess', $publicRoot . DIRECTORY_SEPARATOR . '.htaccess');
$productionHtaccess = $publicRoot . DIRECTORY_SEPARATOR . '.htaccess';
$htaccessContents = file_get_contents($productionHtaccess);
if ($htaccessContents === false) {
    throw new RuntimeException('Unable to read production .htaccess.');
}
$htaccessContents = preg_replace(
    '~\R# Compatibility for releases served at the domain root while legacy links still\R# contain the local XAMPP /conscious prefix\.\RRewriteCond .*?\RRewriteRule .*?\R~s',
    PHP_EOL,
    $htaccessContents
);
if ($htaccessContents === null || file_put_contents($productionHtaccess, $htaccessContents) === false) {
    throw new RuntimeException('Unable to prepare production .htaccess.');
}

foreach (glob($projectRoot . DIRECTORY_SEPARATOR . '*.php') ?: [] as $file) {
    copyFile($file, $publicRoot . DIRECTORY_SEPARATOR . basename($file));
}

// Keep an HTML entry only when no maintained PHP equivalent exists.
foreach (glob($projectRoot . DIRECTORY_SEPARATOR . '*.html') ?: [] as $file) {
    $phpEquivalent = substr($file, 0, -5) . '.php';
    if (!is_file($phpEquivalent)) {
        copyFile($file, $publicRoot . DIRECTORY_SEPARATOR . basename($file));
    }
}

foreach (['assets', 'includes', 'portals', 'events'] as $directory) {
    copyTree(
        $projectRoot . DIRECTORY_SEPARATOR . $directory,
        $publicRoot . DIRECTORY_SEPARATOR . $directory,
        static function (string $relative, SplFileInfo $item) use ($projectRoot, $directory): bool {
            if (preg_match('~(?:^|/)(?:docs|tests)(?:/|$)~i', $relative)) {
                return false;
            }
            if ($item->isDir()) {
                return true;
            }
            if (str_ends_with(strtolower($relative), '.html')) {
                $source = $projectRoot . DIRECTORY_SEPARATOR . $directory . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
                if (is_file(substr($source, 0, -5) . '.php')) {
                    return false;
                }
            }
            return !preg_match('~(?:^|/)(?:README\.md|.*\.md|.*\.map)$~i', $relative);
        }
    );
}

$apiSource = $projectRoot . DIRECTORY_SEPARATOR . 'api';
copyTree(
    $apiSource,
    $publicRoot . DIRECTORY_SEPARATOR . 'api',
    static function (string $relative, SplFileInfo $item): bool {
        if (preg_match('~^(?:bin|database|data|dependencies|lib)(?:/|$)~i', $relative)) {
            return false;
        }
        if ($item->isDir()) {
            return true;
        }
        return !preg_match('~^(?:config\.php|\.env(?:\..*)?|README\.md|schema\.sql|composer\.(?:json|lock))$~i', $relative);
    }
);

// Keep all implementation, dependencies, CLI jobs and migrations outside the document root.
copyTree(
    $apiSource,
    $backendRoot,
    static function (string $relative, SplFileInfo $item): bool {
        if (preg_match('~^data(?:/|$)~i', $relative)) {
            return false;
        }
        if ($item->isDir()) {
            return true;
        }
        return !preg_match('~(?:^|/)(?:\.env(?:\..*)?|README\.md|schema\.sql|bin/seed-.*\.php|dependencies/.*/examples(?:/|$))~i', $relative);
    }
);
ensureDirectory($backendRoot . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'rate-limit');
ensureDirectory($backendRoot . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'mail-outbox');

// Compatibility bridges preserve existing endpoint includes without exposing backend source.
$configBridge = <<<'PHP'
<?php
declare(strict_types=1);

$backendConfig = dirname(__DIR__, 2) . '/private/backend/config.php';
if (!is_file($backendConfig)) {
    http_response_code(503);
    exit('Application backend is unavailable.');
}
require_once $backendConfig;
PHP;
file_put_contents($publicRoot . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . 'config.php', $configBridge . PHP_EOL);

$publicLibRoot = $publicRoot . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . 'lib';
ensureDirectory($publicLibRoot);
foreach (glob($apiSource . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . '*.php') ?: [] as $library) {
    $filename = basename($library);
    $bridge = "<?php\ndeclare(strict_types=1);\nrequire_once dirname(__DIR__, 3) . '/private/backend/lib/{$filename}';\n";
    file_put_contents($publicLibRoot . DIRECTORY_SEPARATOR . $filename, $bridge);
}

copyFile($projectRoot . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'environments' . DIRECTORY_SEPARATOR . 'production.env.example', $releaseRoot . DIRECTORY_SEPARATOR . '.env.example');
$normalizedFiles = normalizeProductionBasePath($publicRoot) + normalizeProductionBasePath($backendRoot);

file_put_contents(
    $releaseRoot . DIRECTORY_SEPARATOR . 'RELEASE.txt',
    "Conscious India production release\nBuilt: " . gmdate('c') . "\nPHP: " . PHP_VERSION . "\n"
);

echo "Production release created successfully.\n";
echo "Public document root: {$publicRoot}\n";
echo "Private backend:      {$backendRoot}\n";
echo "Private storage:      {$privateRoot}" . DIRECTORY_SEPARATOR . "storage\n";
echo "Normalized URLs:      {$normalizedFiles} public file(s)\n";
