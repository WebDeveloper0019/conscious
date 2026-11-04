<?php
declare(strict_types=1);

// The PHP development server does not read Apache .htaccess files.
// Keep this router on loopback and deny files that Apache denies in production.
$requestTarget = (string)($_SERVER['REQUEST_URI'] ?? '/');
// REQUEST_URI is a request target, not a URL: parse_url() treats a leading
// double slash as an authority and can drop the directory we need to deny.
$path = rawurldecode(explode('?', $requestTarget, 2)[0]);
$segments = array_values(array_filter(
    explode('/', str_replace('\\', '/', $path)),
    static fn(string $segment): bool => $segment !== ''
));

$privateDirectories = ['.git', 'build', 'config', 'design', 'docs', 'outputs', 'scripts', 'storage', 'tests'];
$privateApiDirectories = ['bin', 'data', 'database', 'dependencies', 'lib', 'migrations'];
$name = $segments === [] ? '' : $segments[count($segments) - 1];
$hiddenSegments = array_filter($segments, static fn(string $segment): bool => str_starts_with($segment, '.'));
$denied = !str_starts_with($path, '/') || preg_match('/[\x00-\x1f\x7f]/', $path) === 1
    || $hiddenSegments !== []
    || (isset($segments[0]) && in_array(strtolower($segments[0]), $privateDirectories, true))
    || (isset($segments[0], $segments[1]) && strtolower($segments[0]) === 'api'
        && in_array(strtolower($segments[1]), $privateApiDirectories, true))
    || (isset($segments[0], $segments[1]) && strtolower($segments[0]) === 'api'
        && strtolower($segments[1]) === 'config.php')
    || preg_match('/\.(?:sql|log|ini|md)$/i', $name) === 1
    || in_array(strtolower($name), ['composer.json', 'composer.lock', 'package.json', 'package-lock.json', 'phpunit.xml'], true);

if ($denied) {
    http_response_code(403);
    exit;
}

return false;
