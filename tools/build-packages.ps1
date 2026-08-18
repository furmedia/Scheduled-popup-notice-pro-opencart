param(
    [string]$RepositoryRoot = (Split-Path -Parent $PSScriptRoot)
)

$ErrorActionPreference = 'Stop'
$RepositoryRoot = [System.IO.Path]::GetFullPath($RepositoryRoot)
$dist = Join-Path $RepositoryRoot 'dist'
$packages = [ordered]@{
    'scheduled_popup_notice_pro_oc2_20_22.ocmod.zip' = 'compatibility\oc2_20_22'
    'scheduled_popup_notice_pro_oc2_23.ocmod.zip' = 'compatibility\oc2_23'
    'scheduled_popup_notice_pro_oc3.ocmod.zip' = 'compatibility\oc3'
    'scheduled_popup_notice_pro_oc3_journal.ocmod.zip' = 'compatibility\oc3_journal'
    'scheduled_popup_notice_pro_oc4_40.ocmod.zip' = 'compatibility\oc4_40'
    'scheduled_popup_notice_pro_oc4_41.ocmod.zip' = 'compatibility\oc4_41'
}

function Assert-ChildPath($path) {
    $full = [System.IO.Path]::GetFullPath($path)
    $prefix = $RepositoryRoot.TrimEnd([System.IO.Path]::DirectorySeparatorChar) + [System.IO.Path]::DirectorySeparatorChar
    if (-not $full.StartsWith($prefix, [System.StringComparison]::OrdinalIgnoreCase)) {
        throw "Refusing to modify a path outside the module repository: $full"
    }
    return $full
}

function Compress-ArchiveWithRetry {
    param(
        [Parameter(Mandatory = $true)][string]$Path,
        [Parameter(Mandatory = $true)][string]$DestinationPath,
        [int]$Attempts = 6
    )

    for ($attempt = 1; $attempt -le $Attempts; $attempt++) {
        try {
            if (Test-Path -LiteralPath $DestinationPath) {
                Remove-Item -LiteralPath $DestinationPath -Force
            }

            Compress-Archive -Path $Path -DestinationPath $DestinationPath -CompressionLevel Optimal
            return
        } catch {
            Remove-Item -LiteralPath $DestinationPath -Force -ErrorAction SilentlyContinue

            if ($attempt -eq $Attempts) {
                throw
            }

            Start-Sleep -Milliseconds (250 * $attempt)
        }
    }
}

New-Item -ItemType Directory -Force -Path $dist | Out-Null
foreach ($obsoleteName in @('scheduled_popup_notice_pro.ocmod.zip', 'scheduled_popup_notice_pro_oc2_20_22 (1).ocmod.zip')) {
    $obsolete = Assert-ChildPath (Join-Path $dist $obsoleteName)
    if (Test-Path -LiteralPath $obsolete) {
        Remove-Item -LiteralPath $obsolete -Force
    }
}

foreach ($package in $packages.GetEnumerator()) {
    $source = Join-Path $RepositoryRoot $package.Value
    $destination = Join-Path $dist $package.Key

    if (-not (Test-Path $source)) {
        throw "Package source does not exist: $source"
    }

    Compress-ArchiveWithRetry -Path (Join-Path $source '*') -DestinationPath $destination
    Write-Output ("{0} ({1} bytes)" -f $package.Key, (Get-Item $destination).Length)
}

$checksumPath = Join-Path $dist 'SHA256SUMS.txt'
$checksumLines = foreach ($name in $packages.Keys) {
    $hash = (Get-FileHash -Algorithm SHA256 -LiteralPath (Join-Path $dist $name)).Hash.ToLowerInvariant()
    "$hash  $name"
}
Set-Content -LiteralPath $checksumPath -Value $checksumLines -Encoding ASCII

$bundleStage = Assert-ChildPath (Join-Path $RepositoryRoot '.release-bundle')
if (Test-Path -LiteralPath $bundleStage) {
    Remove-Item -LiteralPath $bundleStage -Recurse -Force
}
New-Item -ItemType Directory -Force -Path (Join-Path $bundleStage 'packages'), (Join-Path $bundleStage 'docs'), (Join-Path $bundleStage 'assets') | Out-Null
foreach ($name in $packages.Keys) {
    $packageStage = Join-Path (Join-Path $bundleStage 'packages') $name
    Copy-Item -LiteralPath (Join-Path $dist $name) -Destination $packageStage
}
Copy-Item -LiteralPath (Join-Path $RepositoryRoot 'README.md'), (Join-Path $RepositoryRoot 'MARKETPLACE_LISTING.md'), (Join-Path $RepositoryRoot 'CHANGELOG.md'), $checksumPath -Destination $bundleStage
Copy-Item -Path (Join-Path $RepositoryRoot 'docs\*') -Destination (Join-Path $bundleStage 'docs') -Recurse -Force
Copy-Item -Path (Join-Path $RepositoryRoot 'assets\*') -Destination (Join-Path $bundleStage 'assets') -Recurse -Force

$bundle = Join-Path $dist 'scheduled_popup_notice_pro_all_versions.zip'
Compress-ArchiveWithRetry -Path (Join-Path $bundleStage '*') -DestinationPath $bundle
Remove-Item -LiteralPath $bundleStage -Recurse -Force

$allHashes = Get-ChildItem -LiteralPath $dist -Filter '*.zip' | Sort-Object Name | ForEach-Object {
    $hash = (Get-FileHash -Algorithm SHA256 -LiteralPath $_.FullName).Hash.ToLowerInvariant()
    "$hash  $($_.Name)"
}
Set-Content -LiteralPath $checksumPath -Value $allHashes -Encoding ASCII
Write-Output ("{0} ({1} bytes)" -f (Split-Path -Leaf $bundle), (Get-Item $bundle).Length)
Write-Output ("Checksums: {0}" -f $checksumPath)
