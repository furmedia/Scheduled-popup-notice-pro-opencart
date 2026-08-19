param(
    [string]$RepositoryRoot = (Split-Path -Parent $PSScriptRoot)
)

$ErrorActionPreference = 'Stop'
$RepositoryRoot = [System.IO.Path]::GetFullPath($RepositoryRoot)
$validationRoot = Join-Path $RepositoryRoot '.validation'

function Assert-ChildPath($path) {
    $full = [System.IO.Path]::GetFullPath($path)
    $prefix = $RepositoryRoot.TrimEnd([System.IO.Path]::DirectorySeparatorChar) + [System.IO.Path]::DirectorySeparatorChar
    if (-not $full.StartsWith($prefix, [System.StringComparison]::OrdinalIgnoreCase)) {
        throw "Refusing to modify a path outside the module repository: $full"
    }
    return $full
}

function Run-PowerShellScript($relative) {
    $path = Join-Path $RepositoryRoot $relative
    $global:LASTEXITCODE = 0
    & $path -RepositoryRoot $RepositoryRoot
    if ($null -ne $LASTEXITCODE -and $LASTEXITCODE -ne 0) {
        throw "$relative failed with exit code $LASTEXITCODE"
    }
}

function Test-ZipEntries($zipPath, $requiredEntries) {
    $archive = [System.IO.Compression.ZipFile]::OpenRead($zipPath)
    try {
        $entries = @{}
        foreach ($entry in $archive.Entries) {
            $entries[$entry.FullName.Replace('\', '/')] = $true
        }
        foreach ($required in $requiredEntries) {
            if (-not $entries.ContainsKey($required)) {
                throw "Missing package entry '$required' in $(Split-Path -Leaf $zipPath)"
            }
        }
    } finally {
        $archive.Dispose()
    }
}

function Get-LanguageKeys($path) {
    $source = Get-Content -Raw -LiteralPath $path
    return @([regex]::Matches($source, '\$_\[''([^'']+)''\]') | ForEach-Object { $_.Groups[1].Value } | Sort-Object -Unique)
}

Write-Output '== Rebuild compatibility adapters =='
Run-PowerShellScript 'tools\build-compatibility.ps1'

Write-Output '== Language parity =='
$languageGroups = [ordered]@{
    'admin' = Join-Path $RepositoryRoot 'opencart\upload\admin\language'
    'catalog' = Join-Path $RepositoryRoot 'opencart\upload\catalog\language'
}
foreach ($group in $languageGroups.GetEnumerator()) {
    $reference = Join-Path $group.Value 'en-gb\extension\module\cristale_shipping_notice.php'
    $referenceKeys = Get-LanguageKeys $reference
    $languageFiles = Get-ChildItem -LiteralPath $group.Value -Recurse -File -Filter 'cristale_shipping_notice.php'
    if ($languageFiles.Count -ne 8) {
        throw "Expected 8 $($group.Key) language files, found $($languageFiles.Count)."
    }
    foreach ($file in $languageFiles) {
        $difference = Compare-Object $referenceKeys (Get-LanguageKeys $file.FullName)
        if ($difference) {
            throw "Language keys do not match en-gb in $($file.FullName)"
        }
    }
    Write-Output "$($group.Key) language files: $($languageFiles.Count); keys per file: $($referenceKeys.Count)"
}

Write-Output '== Version adapter assertions =='
$legacyRoot = Join-Path $RepositoryRoot 'compatibility\oc2_20_22'
foreach ($requiredPath in @(
    'upload\admin\language\english\module\cristale_shipping_notice.php',
    'upload\catalog\language\english\module\cristale_shipping_notice.php',
    'upload\admin\language\romanian\module\cristale_shipping_notice.php',
    'upload\catalog\language\romanian\module\cristale_shipping_notice.php'
)) {
    if (-not (Test-Path -LiteralPath (Join-Path $legacyRoot $requiredPath))) {
        throw "Missing OpenCart 2.0-2.2 legacy language alias: $requiredPath"
    }
}

$legacyControllers = Get-ChildItem -LiteralPath (Join-Path $RepositoryRoot 'compatibility\oc2_20_22'), (Join-Path $RepositoryRoot 'compatibility\oc2_23') -Recurse -File -Filter '*.php'
foreach ($controller in $legacyControllers) {
    if ((Get-Content -Raw -LiteralPath $controller.FullName).Contains('extension_install_id')) {
        throw "OpenCart 2 package contains unsupported extension_install_id in $($controller.FullName)"
    }
}

foreach ($name in @('oc2_20_22', 'oc2_23')) {
    $manifestPath = Join-Path $RepositoryRoot "compatibility\$name\install.xml"
    $manifest = Get-Content -Raw -LiteralPath $manifestPath
    $legacyFooterAnchor = '$data[''powered''] = sprintf($this->language->get(''text_powered''), $this->config->get(''config_name''), date(''Y'', time()));'
    $legacyAdminPath = if ($name -eq 'oc2_20_22') {
        Join-Path $RepositoryRoot "compatibility\$name\upload\admin\controller\module\cristale_shipping_notice.php"
    } else {
        Join-Path $RepositoryRoot "compatibility\$name\upload\admin\controller\extension\module\cristale_shipping_notice.php"
    }
    $legacyAdmin = Get-Content -Raw -LiteralPath $legacyAdminPath
    $embeddedFooterAnchor = '$data[\''powered\''] = sprintf($this->language->get(\''text_powered\''), $this->config->get(\''config_name\''), date(\''Y\'', time()));'

    if (-not $manifest.Contains($legacyFooterAnchor)) {
        throw "OpenCart 2 manifest does not use the footer anchor shared by supported 2.x releases: $manifestPath"
    }

    if ($manifest.Contains('$data[''scripts''] = $this->document->getScripts(''footer'');')) {
        throw "OpenCart 2 manifest still uses the footer anchor missing from OpenCart 2.0.3.1: $manifestPath"
    }

    if (-not $legacyAdmin.Contains($embeddedFooterAnchor) -or $legacyAdmin.Contains('$data[\''scripts\''] = $this->document->getScripts(\''footer\'');')) {
        throw "OpenCart 2 embedded modification XML does not use the shared footer anchor: $legacyAdminPath"
    }
}

foreach ($name in @('oc4_40', 'oc4_41')) {
    $root = Join-Path $RepositoryRoot "compatibility\$name"
    $adminTemplate = Join-Path $root 'admin\view\template\module\scheduled_popup.twig'
    $template = Get-Content -Raw -LiteralPath $adminTemplate
    if ($template -match 'pull-right|data-toggle=|data-dismiss=|panel panel-default|panel-heading|panel-body|btn btn-default|fa fa-') {
        throw "OpenCart 4 admin template still contains Bootstrap 3 or Font Awesome 4 markup: $adminTemplate"
    }
    foreach ($requiredMarker in @('data-bs-toggle="tooltip"', 'class="card spn-admin"', 'fa-solid fa-save')) {
        if (-not $template.Contains($requiredMarker)) {
            throw "OpenCart 4 admin template is missing '$requiredMarker': $adminTemplate"
        }
    }

    $adminController = Get-Content -Raw -LiteralPath (Join-Path $root 'admin\controller\module\scheduled_popup.php')
    $catalogController = Get-Content -Raw -LiteralPath (Join-Path $root 'catalog\controller\module\scheduled_popup.php')
    foreach ($trigger in @('catalog/view/mail/order_invoice/after', 'catalog/view/mail/order_add/after')) {
        if (-not $adminController.Contains($trigger)) {
            throw "OpenCart 4 event registration is missing $trigger in $name"
        }
    }
    if ($adminController.Contains('catalog/controller/mail/order/add/before') -or -not $catalogController.Contains('function mailInvoiceAfter')) {
        throw "OpenCart 4 email integration is not using the supported mail template event in $name"
    }
}

$calendarTemplates = Get-ChildItem -LiteralPath (Join-Path $RepositoryRoot 'compatibility') -Recurse -File | Where-Object {
    $_.FullName -match '[\\/]admin[\\/]view[\\/]template[\\/]' -and $_.Name -in @('cristale_shipping_notice.tpl', 'cristale_shipping_notice.twig', 'scheduled_popup.twig')
}
if ($calendarTemplates.Count -ne 6) {
    throw "Expected six version-specific admin templates, found $($calendarTemplates.Count)."
}
foreach ($templateFile in $calendarTemplates) {
    $templateSource = Get-Content -Raw -LiteralPath $templateFile.FullName
    foreach ($marker in @('type="date"', 'type="time"', 'data-date-part=', 'data-time-part=', 'text_time_optional')) {
        if (-not $templateSource.Contains($marker)) {
            throw "Separated date/time marker '$marker' is missing from $($templateFile.FullName)"
        }
    }
    foreach ($forbiddenMarker in @('type="datetime-local"', 'data-datetime-field="1"')) {
        if ($templateSource.Contains($forbiddenMarker)) {
            throw "Combined required date/time control '$forbiddenMarker' returned in $($templateFile.FullName)"
        }
    }
}
Write-Output "Separated date/optional-time admin templates: $($calendarTemplates.Count)"

Write-Output '== Order email and Save and Stay safeguards =='
$emailVariantRoots = @(Get-ChildItem -LiteralPath (Join-Path $RepositoryRoot 'compatibility') -Directory | Where-Object { $_.Name -match '^(oc2_|oc3(?:$|_)|oc4_)' } | Sort-Object Name)
if ($emailVariantRoots.Count -ne 6) {
    throw "Expected six email-enabled compatibility variants, found $($emailVariantRoots.Count)."
}

foreach ($variantRoot in $emailVariantRoots) {
    $engineFiles = @(Get-ChildItem -LiteralPath $variantRoot.FullName -Recurse -File -Filter 'furmedia_scheduled_popup.php')
    $catalogControllers = @(Get-ChildItem -LiteralPath $variantRoot.FullName -Recurse -File | Where-Object {
        $_.FullName -match '[\\/]catalog[\\/]controller[\\/]' -and $_.Name -in @('cristale_shipping_notice.php', 'scheduled_popup.php')
    })
    $adminControllers = @(Get-ChildItem -LiteralPath $variantRoot.FullName -Recurse -File | Where-Object {
        $_.FullName -match '[\\/]admin[\\/]controller[\\/]' -and $_.Name -in @('cristale_shipping_notice.php', 'scheduled_popup.php')
    })
    $adminTemplates = @(Get-ChildItem -LiteralPath $variantRoot.FullName -Recurse -File | Where-Object {
        $_.FullName -match '[\\/]admin[\\/]view[\\/]template[\\/]' -and $_.Name -in @('cristale_shipping_notice.tpl', 'cristale_shipping_notice.twig', 'scheduled_popup.twig')
    })
    $catalogTemplates = @(Get-ChildItem -LiteralPath $variantRoot.FullName -Recurse -File | Where-Object {
        $_.FullName -match '[\\/]catalog[\\/]view[\\/]' -and $_.Name -in @('cristale_shipping_notice.tpl', 'cristale_shipping_notice.twig', 'scheduled_popup.twig')
    })

    if ($engineFiles.Count -ne 1 -or $catalogControllers.Count -ne 1 -or $adminControllers.Count -ne 1 -or $adminTemplates.Count -ne 1 -or $catalogTemplates.Count -ne 1) {
        throw "Incomplete email/UI source set in $($variantRoot.Name)."
    }

    $engineText = Get-Content -Raw -LiteralPath $engineFiles[0].FullName
    $catalogText = Get-Content -Raw -LiteralPath $catalogControllers[0].FullName
    $adminText = Get-Content -Raw -LiteralPath $adminControllers[0].FullName
    $templateText = Get-Content -Raw -LiteralPath $adminTemplates[0].FullName
    $catalogTemplateText = Get-Content -Raw -LiteralPath $catalogTemplates[0].FullName

    foreach ($marker in @('''email_enabled'' => 1', '$result[''email_enabled'']')) {
        if (-not $engineText.Contains($marker)) {
            throw "Email campaign normalization is missing '$marker' in $($variantRoot.Name)."
        }
    }
    foreach ($marker in @('''starts_at_time'' => ''''', '''ends_at_time'' => ''''', 'dateTimeWithOptionalTime', '''devices'' => array(''desktop'', ''tablet'', ''mobile'')', 'private static function devices')) {
        if (-not $engineText.Contains($marker)) {
            throw "Campaign normalization support is missing '$marker' in $($variantRoot.Name)."
        }
    }
    foreach ($marker in @('''popup_width_laptop'' => 520', '$result[''breakpoint_laptop'']')) {
        if (-not $engineText.Contains($marker)) {
            throw "Responsive campaign normalization is missing '$marker' in $($variantRoot.Name)."
        }
    }
    foreach ($marker in @('function getEmailMessage', 'empty($campaign[''email_enabled''])', '$email_parts')) {
        if (-not $catalogText.Contains($marker)) {
            throw "Order email handler is missing '$marker' in $($variantRoot.Name)."
        }
    }
    foreach ($marker in @('entry_email_enabled', 'button_save_stay', 'entry_devices', 'error_campaign_devices', 'text_responsive_studio', 'entry_breakpoint_laptop')) {
        if (-not $adminText.Contains($marker)) {
            throw "Admin controller is missing '$marker' in $($variantRoot.Name)."
        }
    }
    foreach ($marker in @('checkbox(ui.entry_email_enabled', 'text_email_enabled_help', 'name="save_stay"', 'button_save_stay', 'data-device=', 'text_device_help', 'responsiveStudio(c)', 'data-preview-profile=')) {
        if (-not $templateText.Contains($marker)) {
            throw "Admin template is missing '$marker' in $($variantRoot.Name)."
        }
    }
    if ($templateText.Contains('margin:-28px') -or $catalogTemplateText.Contains('margin:-28px') -or $catalogTemplateText.Contains('margin-top:-') -or -not $templateText.Contains('margin:0 auto;') -or -not $catalogTemplateText.Contains('margin:0 auto;')) {
        throw "Popup title still overlaps the hero banner in $($variantRoot.Name)."
    }
    foreach ($marker in @('function currentDevice()', 'function currentProfile(campaign)', 'campaign.devices', 'allowedDevices.indexOf(device)', 'show(next)', '--spn-dialog-width', 'popup_width_', 'transform:translate(-50%,-50%)')) {
        if (-not $catalogTemplateText.Contains($marker)) {
            throw "Storefront device/display marker '$marker' is missing in $($variantRoot.Name)."
        }
    }
    if ($catalogTemplateText.Contains('if (queue.length === 0 && !activeCampaign)')) {
        throw "Single-campaign queue regression returned in $($variantRoot.Name)."
    }
    if ($variantRoot.Name -match '^(oc2_|oc3)') {
        foreach ($marker in @('fa-solid ', 'fa-regular ')) {
            if ($templateText.Contains($marker)) {
                throw "Legacy admin template contains an incompatible Font Awesome class '$marker' in $($variantRoot.Name)."
            }
        }
    }
    else {
        foreach ($marker in @('fa-solid fa-floppy-disk', 'fa-solid fa-check', 'fa-solid fa-upload', 'fa-solid fa-download')) {
            if (-not $templateText.Contains($marker)) {
                throw "OpenCart 4 admin template is missing '$marker' in $($variantRoot.Name)."
            }
        }
    }

    $manifestPath = Join-Path $variantRoot.FullName 'install.xml'
    if (Test-Path -LiteralPath $manifestPath) {
        $manifestText = Get-Content -Raw -LiteralPath $manifestPath
        foreach ($marker in @('getEmailMessage', 'data-furmedia-scheduled-popup-email')) {
            if (-not $manifestText.Contains($marker)) {
                throw "Legacy order email OCMOD hook is missing '$marker' in $($variantRoot.Name)."
            }
        }
    }
    else {
        foreach ($marker in @('catalog/view/mail/order_invoice/after', 'catalog/view/mail/order_add/after', 'mailInvoiceAfter')) {
            if (-not $adminText.Contains($marker)) {
                throw "OpenCart 4 order email event is missing '$marker' in $($variantRoot.Name)."
            }
        }
        if (-not $catalogText.Contains('data-furmedia-scheduled-popup-email')) {
            throw "OpenCart 4 visual order email banner is missing in $($variantRoot.Name)."
        }
    }

    $adminLanguageFiles = @(Get-ChildItem -LiteralPath $variantRoot.FullName -Recurse -File | Where-Object {
        $_.FullName -match '[\\/]admin[\\/]language[\\/]' -and $_.Name -in @('cristale_shipping_notice.php', 'scheduled_popup.php')
    })
    foreach ($languageFile in $adminLanguageFiles) {
        $languageText = Get-Content -Raw -LiteralPath $languageFile.FullName
        foreach ($marker in @('button_save_stay', 'entry_email_enabled', 'text_email_enabled_help')) {
            if (-not $languageText.Contains($marker)) {
                throw "Admin language file is missing '$marker': $($languageFile.FullName)"
            }
        }
    }
}
Write-Output "Order-email and Save-and-Stay variants checked: $($emailVariantRoots.Count)"

$installableFiles = Get-ChildItem -LiteralPath (Join-Path $RepositoryRoot 'opencart'), (Join-Path $RepositoryRoot 'opencart4'), (Join-Path $RepositoryRoot 'compatibility') -Recurse -File | Where-Object {
    $_.Extension -in @('.php', '.twig', '.tpl', '.xml', '.json', '.md') -and $_.FullName -notmatch '[\\/]graphify-out[\\/]'
}
foreach ($file in $installableFiles) {
    $sourceText = Get-Content -Raw -LiteralPath $file.FullName
    if ($sourceText -match 'cristale-semipretioase\.ro|24\s*[\-–]\s*29\s+iunie|2026-06-30') {
        throw "Store-specific example data leaked into release source: $($file.FullName)"
    }
}

$php = Get-Command php -ErrorAction Stop
$phpVersion = (& $php.Source -r 'echo PHP_VERSION;').Trim()
Write-Output "PHP CLI used for lint/tests: $phpVersion"

Write-Output '== PHP syntax =='
$phpRoots = @('opencart', 'opencart4', 'compatibility') | ForEach-Object { Join-Path $RepositoryRoot $_ }
$phpFiles = Get-ChildItem -LiteralPath $phpRoots -Recurse -File -Filter '*.php' | Sort-Object FullName -Unique
foreach ($file in $phpFiles) {
    $result = & $php.Source -l $file.FullName 2>&1
    if ($LASTEXITCODE -ne 0) {
        throw "PHP lint failed: $($file.FullName)`n$result"
    }
}
Write-Output "PHP files checked: $($phpFiles.Count)"

Write-Output '== OpenCart 2/3 legacy syntax guard =='
$legacyRoots = @('compatibility\oc2_20_22', 'compatibility\oc2_23', 'compatibility\oc3', 'compatibility\oc3_journal') | ForEach-Object { Join-Path $RepositoryRoot $_ }
$legacyFiles = Get-ChildItem -LiteralPath $legacyRoots -Recurse -File -Filter '*.php'
$forbidden = [ordered]@{
    'null coalescing operator' = '\?\?'
    'arrow function' = '\bfn\s*\('
    'return type declaration' = 'function\s+\w+\s*\([^)]*\)\s*:\s*[A-Za-z?\\]'
    'typed property' = '(public|protected|private)\s+(bool|int|float|string|array|object|mixed)\s+\$'
    'match expression' = '\bmatch\s*\('
}
foreach ($file in $legacyFiles) {
    $source = Get-Content -Raw -LiteralPath $file.FullName
    foreach ($rule in $forbidden.GetEnumerator()) {
        if ($source -match $rule.Value) {
            throw "OpenCart 2/3 syntax guard found $($rule.Key) in $($file.FullName)"
        }
    }
}
Write-Output "Legacy PHP files checked: $($legacyFiles.Count)"

Write-Output '== Storefront image payload =='
$defaultWebp = Join-Path $RepositoryRoot 'opencart\upload\catalog\view\theme\default\image\cristale_shipping_notice\shipping-notice-background.webp'
if (-not (Test-Path -LiteralPath $defaultWebp) -or (Get-Item -LiteralPath $defaultWebp).Length -gt 102400) {
    throw 'The built-in storefront WebP is missing or exceeds 100 KB.'
}
$legacyPngFiles = Get-ChildItem -LiteralPath (Join-Path $RepositoryRoot 'opencart'), (Join-Path $RepositoryRoot 'compatibility') -Recurse -File -Filter 'shipping-notice-background.png'
if ($legacyPngFiles.Count -gt 0) {
    throw "Unused legacy popup PNG found: $($legacyPngFiles.FullName -join ', ')"
}
Write-Output "Built-in WebP: $((Get-Item -LiteralPath $defaultWebp).Length) bytes; legacy PNG files: 0"

Write-Output '== Engine behavior =='
& $php.Source (Join-Path $RepositoryRoot 'tools\test-engine.php')
if ($LASTEXITCODE -ne 0) {
    throw 'Engine behavior tests failed.'
}

Write-Output '== XML and JSON manifests =='
$xmlFiles = Get-ChildItem -LiteralPath (Join-Path $RepositoryRoot 'opencart'), (Join-Path $RepositoryRoot 'compatibility') -Recurse -File -Filter 'install.xml'
foreach ($file in $xmlFiles) {
    [xml]$xml = Get-Content -Raw -LiteralPath $file.FullName
    if ($xml.modification.version -ne '2.0.9') {
        throw "Unexpected OCMOD version in $($file.FullName)"
    }
}
$jsonFiles = Get-ChildItem -LiteralPath (Join-Path $RepositoryRoot 'opencart4'), (Join-Path $RepositoryRoot 'compatibility') -Recurse -File -Filter 'install.json'
foreach ($file in $jsonFiles) {
    $manifest = Get-Content -Raw -LiteralPath $file.FullName | ConvertFrom-Json
    if ($manifest.version -ne '2.0.9') {
        throw "Unexpected OpenCart 4 manifest version in $($file.FullName)"
    }
}
Write-Output "XML manifests: $($xmlFiles.Count); JSON manifests: $($jsonFiles.Count)"

$node = Get-Command node -ErrorAction SilentlyContinue
if ($node) {
    Write-Output "== JavaScript syntax ($(& $node.Source --version)) =="
    $validationRoot = Assert-ChildPath $validationRoot
    if (Test-Path -LiteralPath $validationRoot) {
        Remove-Item -LiteralPath $validationRoot -Recurse -Force
    }
    New-Item -ItemType Directory -Force -Path $validationRoot | Out-Null
    $templates = @(
        (Join-Path $RepositoryRoot 'opencart\upload\admin\view\template\extension\module\cristale_shipping_notice.twig')
        (Join-Path $RepositoryRoot 'opencart\upload\catalog\view\theme\default\template\extension\module\cristale_shipping_notice.twig')
    )
    $index = 0
    foreach ($template in $templates) {
        $content = Get-Content -Raw -LiteralPath $template
        $matches = [regex]::Matches($content, '<script>(.*?)</script>', [System.Text.RegularExpressions.RegexOptions]::Singleline)
        foreach ($match in $matches) {
            $script = [regex]::Replace($match.Groups[1].Value, '\{\{\s*[^}]+\s*\}\}', 'VzEwPQ==')
            $jsPath = Join-Path $validationRoot ("inline-$index.js")
            Set-Content -LiteralPath $jsPath -Value $script -Encoding UTF8
            & $node.Source --check $jsPath
            if ($LASTEXITCODE -ne 0) {
                throw "JavaScript syntax failed for $template"
            }
            $index++
        }
    }
    Remove-Item -LiteralPath $validationRoot -Recurse -Force
    Write-Output "Inline scripts checked: $index"
} else {
    Write-Warning 'Node.js was not found; JavaScript syntax validation was skipped.'
}

Write-Output '== Build release archives =='
Run-PowerShellScript 'tools\build-packages.ps1'

Add-Type -AssemblyName System.IO.Compression.FileSystem
$dist = Join-Path $RepositoryRoot 'dist'
$requirements = [ordered]@{
    'scheduled_popup_notice_pro_oc2_20_22.ocmod.zip' = @('install.xml', 'upload/admin/controller/module/cristale_shipping_notice.php', 'upload/system/library/furmedia_scheduled_popup.php', 'README.md')
    'scheduled_popup_notice_pro_oc2_23.ocmod.zip' = @('install.xml', 'upload/admin/controller/extension/module/cristale_shipping_notice.php', 'upload/system/library/furmedia_scheduled_popup.php', 'README.md')
    'scheduled_popup_notice_pro_oc3.ocmod.zip' = @('install.xml', 'upload/admin/controller/extension/module/cristale_shipping_notice.php', 'upload/admin/view/template/extension/module/cristale_shipping_notice.twig', 'upload/system/library/furmedia_scheduled_popup.php', 'README.md')
    'scheduled_popup_notice_pro_oc3_journal.ocmod.zip' = @('install.xml', 'upload/admin/controller/extension/module/cristale_shipping_notice.php', 'upload/catalog/view/theme/default/template/extension/module/cristale_shipping_notice.twig', 'upload/system/library/furmedia_scheduled_popup.php', 'README.md')
    'scheduled_popup_notice_pro_oc4_40.ocmod.zip' = @('install.json', 'admin/controller/module/scheduled_popup.php', 'admin/view/template/module/scheduled_popup.twig', 'catalog/controller/module/scheduled_popup.php', 'catalog/view/template/module/scheduled_popup.twig', 'system/library/furmedia_scheduled_popup.php', 'README.md')
    'scheduled_popup_notice_pro_oc4_41.ocmod.zip' = @('install.json', 'admin/controller/module/scheduled_popup.php', 'admin/view/template/module/scheduled_popup.twig', 'catalog/controller/module/scheduled_popup.php', 'catalog/view/template/module/scheduled_popup.twig', 'system/library/furmedia_scheduled_popup.php', 'README.md')
}

foreach ($package in $requirements.GetEnumerator()) {
    $path = Join-Path $dist $package.Key
    if (-not (Test-Path -LiteralPath $path)) {
        throw "Missing release archive: $path"
    }
    if ((Get-Item -LiteralPath $path).Length -ge 32MB) {
        throw "Archive exceeds 32 MB: $($package.Key)"
    }
    Test-ZipEntries $path $package.Value
}

$bundle = Join-Path $dist 'scheduled_popup_notice_pro_all_versions.zip'
Test-ZipEntries $bundle @(
    'packages/scheduled_popup_notice_pro_oc2_20_22.ocmod.zip',
    'packages/scheduled_popup_notice_pro_oc2_23.ocmod.zip',
    'packages/scheduled_popup_notice_pro_oc3.ocmod.zip',
    'packages/scheduled_popup_notice_pro_oc3_journal.ocmod.zip',
    'packages/scheduled_popup_notice_pro_oc4_40.ocmod.zip',
    'packages/scheduled_popup_notice_pro_oc4_41.ocmod.zip',
    'docs/USER_GUIDE.md',
    'docs/GHID_UTILIZARE_RO.md',
    'README.md'
)

Write-Output '== Release checksums =='
Get-Content -LiteralPath (Join-Path $dist 'SHA256SUMS.txt')
Write-Output 'RELEASE VALIDATION PASSED'
