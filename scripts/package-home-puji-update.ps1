param(
    [string]$DateTag = '20261114'
)

$ErrorActionPreference = 'Stop'
$projectRoot = (Resolve-Path -LiteralPath (Join-Path $PSScriptRoot '..')).Path
$releaseRoot = Join-Path $projectRoot 'build\release'
$stageRoot = Join-Path $projectRoot "build\ci-home-puji-hotfix-$DateTag"
$archivePath = "$stageRoot.zip"

if (Test-Path -LiteralPath $stageRoot -PathType Any) {
    throw "Staging path already exists: $stageRoot"
}
if (Test-Path -LiteralPath $archivePath -PathType Any) {
    throw "Archive already exists: $archivePath"
}

$files = @(
    @{ Source='public_html\index.php'; Destination='public_html\conscious\index.php' },
    @{ Source='public_html\includes\public-header.php'; Destination='public_html\conscious\includes\public-header.php' },
    @{ Source='public_html\includes\public-footer.php'; Destination='public_html\conscious\includes\public-footer.php' },
    @{ Source='public_html\assets\js\home-hero.js'; Destination='public_html\conscious\assets\js\home-hero.js' },
    @{ Source='public_html\assets\js\bharatcraft.js'; Destination='public_html\conscious\assets\js\bharatcraft.js' },
    @{ Source='public_html\assets\js\ai-assistant.js'; Destination='public_html\conscious\assets\js\ai-assistant.js' },
    @{ Source='public_html\assets\css\ai-assistant.css'; Destination='public_html\conscious\assets\css\ai-assistant.css' },
    @{ Source='public_html\assets\images\assistant\website-guide.png'; Destination='public_html\conscious\assets\images\assistant\website-guide.png' },
    @{ Source='private\backend\config.php'; Destination='private\ci-backend\backend\config.php' },
    @{ Source='private\backend\lib\ai-client.php'; Destination='private\ci-backend\backend\lib\ai-client.php' }
)

foreach ($entry in $files) {
    $source = Join-Path $releaseRoot $entry.Source
    if (-not (Test-Path -LiteralPath $source -PathType Leaf)) {
        throw "Missing release file: $source"
    }
}

New-Item -ItemType Directory -Path $stageRoot | Out-Null
foreach ($entry in $files) {
    $destination = Join-Path $stageRoot $entry.Destination
    New-Item -ItemType Directory -Path (Split-Path -Parent $destination) -Force | Out-Null
    Copy-Item -LiteralPath (Join-Path $releaseRoot $entry.Source) -Destination $destination
}

Compress-Archive -Path (Join-Path $stageRoot '*') -DestinationPath $archivePath -CompressionLevel Optimal
Write-Output "UPDATE_READY: $archivePath"
Write-Output "FILES: $($files.Count)"
Write-Output "SHA256: $((Get-FileHash -LiteralPath $archivePath -Algorithm SHA256).Hash)"
