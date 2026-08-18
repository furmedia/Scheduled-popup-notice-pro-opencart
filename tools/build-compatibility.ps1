param(
    [string]$RepositoryRoot = (Split-Path -Parent $PSScriptRoot)
)

$ErrorActionPreference = 'Stop'
$RepositoryRoot = [System.IO.Path]::GetFullPath($RepositoryRoot)
$source = Join-Path $RepositoryRoot 'opencart'
$compat = Join-Path $RepositoryRoot 'compatibility'

function Assert-ChildPath($path) {
    $full = [System.IO.Path]::GetFullPath($path)
    $prefix = $RepositoryRoot.TrimEnd([System.IO.Path]::DirectorySeparatorChar) + [System.IO.Path]::DirectorySeparatorChar
    if (-not $full.StartsWith($prefix, [System.StringComparison]::OrdinalIgnoreCase)) {
        throw "Refusing to modify a path outside the module repository: $full"
    }
    return $full
}

function Reset-Directory($path) {
    $path = Assert-ChildPath $path
    if (Test-Path -LiteralPath $path) {
        Remove-Item -LiteralPath $path -Recurse -Force
    }
    New-Item -ItemType Directory -Force -Path $path | Out-Null
}

function Write-PackageReadme($target, $targetLabel, $nativeEvents) {
    $install = if ($nativeEvents) {
@'
1. Upload the .ocmod.zip through Extensions > Installer.
2. Open Extensions > Extensions > Modules.
3. Install and edit Scheduled Popup & Notice Pro.
4. Enable the module, configure a campaign, and save.

This package uses native OpenCart events. No OCMOD refresh is required.
'@
    } else {
@'
1. Upload the .ocmod.zip through Extensions > Installer.
2. Open Extensions > Modifications and click Refresh.
3. Open Extensions > Extensions > Modules.
4. Install and edit Scheduled Popup & Notice Pro.
5. Enable the module, configure a campaign, and save.
'@
    }

    $readme = @"
# Scheduled Popup & Notice Pro 2.0 - $targetLabel

This archive is built specifically for $targetLabel. Do not install it on another OpenCart generation.

## Install

$install

## Included Pro features

- Up to 50 simultaneous campaigns with priority and sequential display.
- One-time, weekly, and monthly schedules with timezone.
- Native calendar and time pickers for all campaign date fields.
- Per-language popup and order-email content.
- Dynamic schedule shortcodes.
- Custom image upload with automatic WebP optimization, design presets, colors, overlay, and blur.
- Countdown, configurable CTA, category/product targeting, and aggregate statistics.
- Safe cache clear and automatic migration from 1.x settings.

The module is disabled on first install. It contains no store credentials, customer data, domain, branding, or enabled campaign.
"@

    Set-Content -LiteralPath (Join-Path $target 'README.md') -Value $readme.TrimEnd() -Encoding UTF8
}

function Copy-Tree($from, $to) {
    New-Item -ItemType Directory -Force -Path $to | Out-Null
    Copy-Item -Path (Join-Path $from '*') -Destination $to -Recurse -Force
}

function Convert-TwigToTpl($path) {
    $text = Get-Content -Raw -LiteralPath $path
    $text = [regex]::Replace($text, '\{\%\s*for\s+(\w+)\s+in\s+(\w+)\s*\%\}', { param($m) '<?php foreach ($' + $m.Groups[2].Value + ' as $' + $m.Groups[1].Value + ') { ?>' })
    $text = $text.Replace('{% endfor %}', '<?php } ?>')
    $text = [regex]::Replace($text, '\{\%\s*if\s+([\w]+)\s*\%\}', { param($m) '<?php if ($' + $m.Groups[1].Value + ') { ?>' })
    $text = $text.Replace('{% else %}', '<?php } else { ?>')
    $text = $text.Replace('{% endif %}', '<?php } ?>')
    $text = [regex]::Replace($text, '\{\{\s*(\w+)\.(\w+)\s*\}\}', { param($m) '<?php echo $' + $m.Groups[1].Value + "['" + $m.Groups[2].Value + "']; ?>" })
    $text = [regex]::Replace($text, '\{\{\s*(\w+)\s*\}\}', { param($m) '<?php echo $' + $m.Groups[1].Value + '; ?>' })
    Set-Content -LiteralPath ($path -replace '\.twig$', '.tpl') -Value $text.TrimEnd() -Encoding UTF8
    Remove-Item -LiteralPath $path
}

function Convert-AdminTwigToOpenCart4($path) {
    $text = Get-Content -Raw -LiteralPath $path
    $replacements = [ordered]@{
        '<div class="pull-right">' = '<div class="float-end">'
        'data-toggle="tooltip"' = 'data-bs-toggle="tooltip"'
        '<ul class="breadcrumb">' = '<ol class="breadcrumb">'
        '</ul>' = '</ol>'
        '<li><a href="{{ breadcrumb.href }}">' = '<li class="breadcrumb-item"><a href="{{ breadcrumb.href }}">'
        '<button type="button" class="close" data-dismiss="alert">&times;</button>' = '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>'
        '<div class="panel panel-default spn-admin">' = '<div class="card spn-admin">'
        '<div class="panel-heading"><h3 class="panel-title">' = '<div class="card-header">'
        '</h3></div>' = '</div>'
        '<div class="panel-body">' = '<div class="card-body">'
        'btn btn-default' = 'btn btn-light'
        'fa fa-save' = 'fa-solid fa-save'
        'fa fa-reply' = 'fa-solid fa-reply'
        'fa fa-exclamation-circle' = 'fa-solid fa-circle-exclamation'
        'fa fa-check-circle' = 'fa-solid fa-circle-check'
        'fa fa-calendar-check-o' = 'fa-regular fa-calendar-check'
        'fa fa-calendar' = 'fa-regular fa-calendar'
        'fa fa-info-circle' = 'fa-solid fa-circle-info'
        'fa fa-plus' = 'fa-solid fa-plus'
        'fa fa-refresh' = 'fa-solid fa-arrows-rotate'
        'fa fa-copy' = 'fa-regular fa-copy'
        'fa fa-trash' = 'fa-solid fa-trash'
        'fa fa-eraser' = 'fa-solid fa-eraser'
        '<select class="form-control"' = '<select class="form-select"'
    }

    foreach ($replacement in $replacements.GetEnumerator()) {
        $text = $text.Replace($replacement.Key, $replacement.Value)
    }

    $text = $text.Replace('id="spn-global-status" class="form-control"', 'id="spn-global-status" class="form-select"')
    Set-Content -LiteralPath $path -Value $text.TrimEnd() -Encoding UTF8
}

function Copy-Languages($destination, $legacy) {
    $adminBase = Join-Path $destination 'upload\admin\language'
    $catalogBase = Join-Path $destination 'upload\catalog\language'
    foreach ($kind in @('admin', 'catalog')) {
        $base = if ($kind -eq 'admin') { $adminBase } else { $catalogBase }
        $target = if ($legacy) { 'module' } else { 'extension\module' }
        foreach ($locale in @('de-de', 'en-gb', 'es-es', 'fr-fr', 'it-it', 'pt-br', 'ro-ro', 'romanian')) {
            $path = Join-Path $base "$locale\$target"
            New-Item -ItemType Directory -Force -Path $path | Out-Null
            Copy-Item (Join-Path $source "upload\$kind\language\$locale\extension\module\cristale_shipping_notice.php") $path -Force
        }
    }
}

function Prepare-Legacy($name, $is22) {
    $target = Join-Path $compat $name
    Reset-Directory $target
    Copy-Tree $source $target

    $admin = Join-Path $target 'upload\admin\controller\extension\module\cristale_shipping_notice.php'
    $catalog = Join-Path $target 'upload\catalog\controller\extension\module\cristale_shipping_notice.php'
    if ($is22) {
        New-Item -ItemType Directory -Force -Path (Join-Path $target 'upload\admin\controller\module'), (Join-Path $target 'upload\catalog\controller\module') | Out-Null
        Move-Item $admin (Join-Path $target 'upload\admin\controller\module\cristale_shipping_notice.php')
        Move-Item $catalog (Join-Path $target 'upload\catalog\controller\module\cristale_shipping_notice.php')
    }

    $controllerPaths = if ($is22) { @('upload\admin\controller\module\cristale_shipping_notice.php', 'upload\catalog\controller\module\cristale_shipping_notice.php') } else { @('upload\admin\controller\extension\module\cristale_shipping_notice.php', 'upload\catalog\controller\extension\module\cristale_shipping_notice.php') }
    foreach ($relative in $controllerPaths) {
        $path = Join-Path $target $relative
        $text = Get-Content -Raw $path
        if ($is22) {
            $text = $text.Replace('ControllerExtensionModuleCristaleShippingNotice', 'ControllerModuleCristaleShippingNotice')
            $text = $text.Replace('extension/module/cristale_shipping_notice', 'module/cristale_shipping_notice')
            $text = $text.Replace('marketplace/extension', 'extension/extension')
            $text = $text.Replace('user_token', 'token')
        } elseif ($relative.StartsWith('upload\admin')) {
            $text = $text.Replace('marketplace/extension', 'extension/extension')
            $text = $text.Replace('user_token', 'token')
        }
        if ($relative.StartsWith('upload\admin')) {
            $text = $text.Replace('`extension_install_id` = ''0'', ', '')
            $text = $text.Replace('catalog/view/theme/*/template/common/footer.twig', 'catalog/view/theme/*/template/common/footer.tpl')
            $text = $text.Replace('{% if cristale_shipping_notice %}{{ cristale_shipping_notice }}{% endif %}', '<?php if ($cristale_shipping_notice) { echo $cristale_shipping_notice; } ?>')
            $text = $text.Replace('catalog/controller/mail/order.php', 'catalog/model/checkout/order.php')
            $text = $text.Replace("get(\'text_greeting\')", "get(\'text_new_greeting\')")

            $embeddedFooterSearch = @'
$data[\'scripts\'] = $this->document->getScripts(\'footer\');
'@
            $embeddedFooterReplacement = @'
$data[\'powered\'] = sprintf($this->language->get(\'text_powered\'), $this->config->get(\'config_name\'), date(\'Y\', time()));
'@
            $controllerLines = $text -split "`r?`n"
            for ($lineIndex = 0; $lineIndex -lt $controllerLines.Count; $lineIndex++) {
                if ($controllerLines[$lineIndex].Contains($embeddedFooterSearch)) {
                    $controllerLines[$lineIndex] = $controllerLines[$lineIndex].Replace($embeddedFooterSearch, $embeddedFooterReplacement).Replace('position="before"', 'position="after"')
                    break
                }
            }
            $text = [string]::Join([Environment]::NewLine, $controllerLines)
        }
        Set-Content -LiteralPath $path -Value $text.TrimEnd() -Encoding UTF8
    }

    if ($is22) {
        foreach ($kind in @('admin', 'catalog')) {
            $old = Join-Path $target "upload\$kind\language"
            $new = Join-Path $target "upload\$kind\language"
            foreach ($locale in @('de-de', 'en-gb', 'es-es', 'fr-fr', 'it-it', 'pt-br', 'ro-ro', 'romanian')) {
                $from = Join-Path $old "$locale\extension\module"
                $to = Join-Path $new "$locale\module"
                if (Test-Path $from) { New-Item -ItemType Directory -Force -Path $to | Out-Null; Move-Item (Join-Path $from '*') $to -Force }
            }
            $view = Join-Path $target "upload\$kind\view\template\extension\module"
            if ($kind -eq 'catalog') { $view = Join-Path $target 'upload\catalog\view\theme\default\template\extension\module' }
            if (Test-Path $view) {
                $legacyView = $view.Replace('\extension\module', '\module')
                New-Item -ItemType Directory -Force -Path $legacyView | Out-Null
                Move-Item (Join-Path $view '*') $legacyView -Force
            }
        }

        $legacyLanguageAliases = [ordered]@{
            'english' = 'en-gb'
            'german' = 'de-de'
            'spanish' = 'es-es'
            'french' = 'fr-fr'
            'italian' = 'it-it'
            'portuguese' = 'pt-br'
            'portuguese-br' = 'pt-br'
        }
        foreach ($kind in @('admin', 'catalog')) {
            foreach ($alias in $legacyLanguageAliases.GetEnumerator()) {
                $from = Join-Path $target "upload\$kind\language\$($alias.Value)\module\cristale_shipping_notice.php"
                $to = Join-Path $target "upload\$kind\language\$($alias.Key)\module"
                New-Item -ItemType Directory -Force -Path $to | Out-Null
                $aliasPath = Join-Path $to 'cristale_shipping_notice.php'
                Copy-Item -LiteralPath $from -Destination $aliasPath -Force
                $aliasContent = [System.IO.File]::ReadAllText($aliasPath, [System.Text.Encoding]::UTF8).TrimEnd("`r", "`n") + "`n"
                [System.IO.File]::WriteAllText($aliasPath, $aliasContent, (New-Object System.Text.UTF8Encoding($false)))
            }
        }
    }

    $adminView = if ($is22) { Join-Path $target 'upload\admin\view\template\module\cristale_shipping_notice.twig' } else { Join-Path $target 'upload\admin\view\template\extension\module\cristale_shipping_notice.twig' }
    if (Test-Path $adminView) { Convert-TwigToTpl $adminView }
    $catalogView = Join-Path $target 'upload\catalog\view\theme\default\template\extension\module\cristale_shipping_notice.twig'
    if ($is22) { $catalogView = Join-Path $target 'upload\catalog\view\theme\default\template\module\cristale_shipping_notice.twig' }
    Convert-TwigToTpl $catalogView

    $xml = Get-Content -Raw (Join-Path $source 'install.xml')
    $xml = $xml.Replace('catalog/controller/common/footer.php', 'catalog/controller/common/footer.php')
    $xmlLines = $xml -split "`r?`n"
    for ($lineIndex = 0; $lineIndex -lt $xmlLines.Count; $lineIndex++) {
        if ($xmlLines[$lineIndex].Contains('$data[''scripts''] = $this->document->getScripts(''footer'');')) {
            $xmlLines[$lineIndex] = $xmlLines[$lineIndex].Replace('$data[''scripts''] = $this->document->getScripts(''footer'');', '$data[''powered''] = sprintf($this->language->get(''text_powered''), $this->config->get(''config_name''), date(''Y'', time()));')

            if (($lineIndex + 1) -lt $xmlLines.Count) {
                $xmlLines[$lineIndex + 1] = $xmlLines[$lineIndex + 1].Replace('position="before"', 'position="after"')
            }

            break
        }
    }
    $xml = [string]::Join([Environment]::NewLine, $xmlLines)
    $xml = $xml.Replace('catalog/view/theme/*/template/common/footer.twig', 'catalog/view/theme/*/template/common/footer.tpl')
    $xml = $xml.Replace('catalog/controller/mail/order.php', 'catalog/model/checkout/order.php')
    $xml = $xml.Replace('$data[''text_greeting''] = sprintf($language->get(''text_greeting''), $order_info[''store_name'']);', '$data[''text_greeting''] = sprintf($language->get(''text_new_greeting''), $order_info[''store_name'']);')
    $route = if ($is22) { 'module/cristale_shipping_notice' } else { 'extension/module/cristale_shipping_notice' }
    $xml = $xml.Replace('extension/module/cristale_shipping_notice', $route)
    $xml = $xml.Replace('extension/module/cristale_shipping_notice/getEmailMessage', "$route/getEmailMessage")
    $xml = $xml.Replace('{% if cristale_shipping_notice %}{{ cristale_shipping_notice }}{% endif %}', '<?php if ($cristale_shipping_notice) { echo $cristale_shipping_notice; } ?>')
    Set-Content -LiteralPath (Join-Path $target 'install.xml') -Value $xml.TrimEnd() -Encoding UTF8
    $label = if ($is22) { 'OpenCart 2.0-2.2' } else { 'OpenCart 2.3.x' }
    Write-PackageReadme $target $label $false
}

Prepare-Legacy 'oc2_20_22' $true
Prepare-Legacy 'oc2_23' $false

foreach ($name in @('oc3', 'oc3_journal')) {
    $target = Join-Path $compat $name
    Reset-Directory $target
    Copy-Tree $source $target
    if ($name -eq 'oc3_journal') {
        $xml = Get-Content -Raw (Join-Path $target 'install.xml')
        $xml = $xml.Replace('catalog/view/theme/*/template/common/footer.twig', 'catalog/view/theme/*/template/common/footer.twig')
        Set-Content -LiteralPath (Join-Path $target 'install.xml') -Value $xml.TrimEnd() -Encoding UTF8
    }
    $label = if ($name -eq 'oc3_journal') { 'OpenCart 3.0.x + Journal 3' } else { 'OpenCart 3.0.x' }
    Write-PackageReadme $target $label $false
}

function Prepare-OpenCart4($name) {
    $target = Join-Path $compat $name
    Reset-Directory $target
    Copy-Tree (Join-Path $RepositoryRoot 'opencart4') $target

    $enginePath = Join-Path $target 'system\library\furmedia_scheduled_popup.php'
    New-Item -ItemType Directory -Force -Path (Split-Path $enginePath) | Out-Null
    Copy-Item (Join-Path $source 'upload\system\library\furmedia_scheduled_popup.php') $enginePath -Force

    foreach ($kind in @('admin', 'catalog')) {
        foreach ($locale in @('de-de', 'en-gb', 'es-es', 'fr-fr', 'it-it', 'pt-br', 'ro-ro', 'romanian')) {
            $languagePath = Join-Path $target "$kind\language\$locale\module"
            New-Item -ItemType Directory -Force -Path $languagePath | Out-Null
            Copy-Item (Join-Path $source "upload\$kind\language\$locale\extension\module\cristale_shipping_notice.php") (Join-Path $languagePath 'scheduled_popup.php') -Force
        }
    }

    $adminTemplate = Join-Path $target 'admin\view\template\module\scheduled_popup.twig'
    New-Item -ItemType Directory -Force -Path (Split-Path $adminTemplate) | Out-Null
    Copy-Item (Join-Path $source 'upload\admin\view\template\extension\module\cristale_shipping_notice.twig') $adminTemplate -Force
    Convert-AdminTwigToOpenCart4 $adminTemplate
    $catalogTemplate = Join-Path $target 'catalog\view\template\module\scheduled_popup.twig'
    New-Item -ItemType Directory -Force -Path (Split-Path $catalogTemplate) | Out-Null
    Copy-Item (Join-Path $source 'upload\catalog\view\theme\default\template\extension\module\cristale_shipping_notice.twig') $catalogTemplate -Force
    $templateText = Get-Content -Raw $catalogTemplate
    $templateText = $templateText.Replace('<div class="cristale-shipping-notice"', '<div data-furmedia-scheduled-popup="1" class="cristale-shipping-notice"')
    Set-Content -LiteralPath $catalogTemplate -Value $templateText.TrimEnd() -Encoding UTF8

    $imagePath = Join-Path $target 'catalog\view\image\shipping-notice-background.webp'
    New-Item -ItemType Directory -Force -Path (Split-Path $imagePath) | Out-Null
    Copy-Item (Join-Path $source 'upload\catalog\view\theme\default\image\cristale_shipping_notice\shipping-notice-background.webp') $imagePath -Force

    $label = if ($name -eq 'oc4_41') { 'OpenCart 4.1.x' } else { 'OpenCart 4.0.x' }
    Write-PackageReadme $target $label $true
}

Prepare-OpenCart4 'oc4_40'
Prepare-OpenCart4 'oc4_41'

Write-Output 'Legacy and OpenCart 3 compatibility trees generated.'
