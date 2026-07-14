param(
    [string]$RepositoryRoot = (Split-Path -Parent $PSScriptRoot)
)

$ErrorActionPreference = 'Stop'
$source = Join-Path $RepositoryRoot 'opencart'
$compat = Join-Path $RepositoryRoot 'compatibility'

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
    if (Test-Path $target) { Remove-Item -Recurse -Force $target }
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
    }

    $adminView = if ($is22) { Join-Path $target 'upload\admin\view\template\module\cristale_shipping_notice.twig' } else { Join-Path $target 'upload\admin\view\template\extension\module\cristale_shipping_notice.twig' }
    if (Test-Path $adminView) { Convert-TwigToTpl $adminView }
    $catalogView = Join-Path $target 'upload\catalog\view\theme\default\template\extension\module\cristale_shipping_notice.twig'
    if ($is22) { $catalogView = Join-Path $target 'upload\catalog\view\theme\default\template\module\cristale_shipping_notice.twig' }
    Convert-TwigToTpl $catalogView

    $xml = Get-Content -Raw (Join-Path $source 'install.xml')
    $xml = $xml.Replace('catalog/controller/common/footer.php', 'catalog/controller/common/footer.php')
    $xml = $xml.Replace('catalog/view/theme/*/template/common/footer.twig', 'catalog/view/theme/*/template/common/footer.tpl')
    $xml = $xml.Replace('catalog/controller/mail/order.php', 'catalog/model/checkout/order.php')
    $xml = $xml.Replace('$data[''text_greeting''] = sprintf($language->get(''text_greeting''), $order_info[''store_name'']);', '$data[''text_greeting''] = sprintf($language->get(''text_new_greeting''), $order_info[''store_name'']);')
    $route = if ($is22) { 'module/cristale_shipping_notice' } else { 'extension/module/cristale_shipping_notice' }
    $xml = $xml.Replace('extension/module/cristale_shipping_notice', $route)
    $xml = $xml.Replace('extension/module/cristale_shipping_notice/getEmailMessage', "$route/getEmailMessage")
    $xml = $xml.Replace('{% if cristale_shipping_notice %}{{ cristale_shipping_notice }}{% endif %}', '<?php if ($cristale_shipping_notice) { echo $cristale_shipping_notice; } ?>')
    Set-Content -LiteralPath (Join-Path $target 'install.xml') -Value $xml.TrimEnd() -Encoding UTF8
    Set-Content -LiteralPath (Join-Path $target 'README.md') -Value ("# Scheduled Popup Notice Pro - $name`r`n`r`nCompatibilitate dedicata pentru OpenCart $name. Instaleaza arhiva OCMOD, apoi refresh la Modificari si instaleaza modulul din Extensii.") -Encoding UTF8
}

Prepare-Legacy 'oc2_20_22' $true
Prepare-Legacy 'oc2_23' $false

foreach ($name in @('oc3', 'oc3_journal')) {
    $target = Join-Path $compat $name
    if (Test-Path $target) { Remove-Item -Recurse -Force $target }
    Copy-Tree $source $target
    if ($name -eq 'oc3_journal') {
        $xml = Get-Content -Raw (Join-Path $target 'install.xml')
        $xml = $xml.Replace('catalog/view/theme/*/template/common/footer.twig', 'catalog/view/theme/*/template/common/footer.twig')
        Set-Content -LiteralPath (Join-Path $target 'install.xml') -Value $xml.TrimEnd() -Encoding UTF8
    }
    Set-Content -LiteralPath (Join-Path $target 'README.md') -Value ("# Scheduled Popup Notice Pro - $name`r`n`r`nCompatibilitate dedicata pentru OpenCart 3.x. Arhiva include OCMOD si resursele popup.") -Encoding UTF8
}

function Prepare-OpenCart4($name) {
    $target = Join-Path $compat $name
    if (Test-Path $target) { Remove-Item -Recurse -Force $target }
    Copy-Tree (Join-Path $RepositoryRoot 'opencart4') $target

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
    $catalogTemplate = Join-Path $target 'catalog\view\template\module\scheduled_popup.twig'
    New-Item -ItemType Directory -Force -Path (Split-Path $catalogTemplate) | Out-Null
    Copy-Item (Join-Path $source 'upload\catalog\view\theme\default\template\extension\module\cristale_shipping_notice.twig') $catalogTemplate -Force
    $templateText = Get-Content -Raw $catalogTemplate
    $templateText = $templateText.Replace('<div class="cristale-shipping-notice"', '<div data-furmedia-scheduled-popup="1" class="cristale-shipping-notice"')
    Set-Content -LiteralPath $catalogTemplate -Value $templateText.TrimEnd() -Encoding UTF8

    $imagePath = Join-Path $target 'catalog\view\image\shipping-notice-background.webp'
    New-Item -ItemType Directory -Force -Path (Split-Path $imagePath) | Out-Null
    Copy-Item (Join-Path $source 'upload\catalog\view\theme\default\image\cristale_shipping_notice\shipping-notice-background.webp') $imagePath -Force

    Set-Content -LiteralPath (Join-Path $target 'README.md') -Value ("# Scheduled Popup Notice Pro - $name`r`n`r`nPachet nativ pentru OpenCart 4.x. Foloseste evenimentele OpenCart pentru popup si mesajul din e-mail, fara modificarea fisierelor de baza sau a temei.") -Encoding UTF8
}

Prepare-OpenCart4 'oc4_40'
Prepare-OpenCart4 'oc4_41'

Write-Output 'Legacy and OpenCart 3 compatibility trees generated.'
