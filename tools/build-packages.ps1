param(
    [string]$RepositoryRoot = (Split-Path -Parent $PSScriptRoot)
)

$ErrorActionPreference = 'Stop'
$dist = Join-Path $RepositoryRoot 'dist'
$packages = [ordered]@{
    'scheduled_popup_notice_pro_oc2_20_22.ocmod.zip' = 'compatibility\oc2_20_22'
    'scheduled_popup_notice_pro_oc2_23.ocmod.zip' = 'compatibility\oc2_23'
    'scheduled_popup_notice_pro_oc3.ocmod.zip' = 'compatibility\oc3'
    'scheduled_popup_notice_pro_oc3_journal.ocmod.zip' = 'compatibility\oc3_journal'
    'scheduled_popup_notice_pro_oc4_40.ocmod.zip' = 'compatibility\oc4_40'
    'scheduled_popup_notice_pro_oc4_41.ocmod.zip' = 'compatibility\oc4_41'
    'scheduled_popup_notice_pro.ocmod.zip' = 'opencart'
}

New-Item -ItemType Directory -Force -Path $dist | Out-Null
foreach ($package in $packages.GetEnumerator()) {
    $source = Join-Path $RepositoryRoot $package.Value
    $destination = Join-Path $dist $package.Key

    if (-not (Test-Path $source)) {
        throw "Package source does not exist: $source"
    }

    if (Test-Path $destination) {
        Remove-Item -Force $destination
    }

    Compress-Archive -Path (Join-Path $source '*') -DestinationPath $destination -CompressionLevel Optimal
    Write-Output ("{0} ({1} bytes)" -f $package.Key, (Get-Item $destination).Length)
}
