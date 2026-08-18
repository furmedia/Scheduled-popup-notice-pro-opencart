# Compatibility Matrix

Scheduled Popup & Notice Pro ships separate adapters because OpenCart 2, 3, and 4 use different controller, language, template, event, and installer contracts.

| Family | Archive | Adapter | PHP source target |
|---|---|---|---|
| OpenCart 2.0-2.2 | `scheduled_popup_notice_pro_oc2_20_22.ocmod.zip` | legacy module routes, TPL, OCMOD | PHP 5.6-compatible syntax |
| OpenCart 2.3 | `scheduled_popup_notice_pro_oc2_23.ocmod.zip` | extension/module routes, TPL, OCMOD | PHP 5.6-compatible syntax |
| OpenCart 3.0 | `scheduled_popup_notice_pro_oc3.ocmod.zip` | extension/module routes, Twig, OCMOD | PHP 5.6-compatible shared runtime |
| OpenCart 3.0 + Journal 3 | `scheduled_popup_notice_pro_oc3_journal.ocmod.zip` | OpenCart 3 adapter with wildcard theme footer hook | PHP 5.6-compatible shared runtime |
| OpenCart 4.0 | `scheduled_popup_notice_pro_oc4_40.ocmod.zip` | native namespaced controllers and events | follows the target OpenCart 4 PHP requirement |
| OpenCart 4.1 | `scheduled_popup_notice_pro_oc4_41.ocmod.zip` | native namespaced controllers and events | follows the target OpenCart 4 PHP requirement |

The PHP version supported by the complete store is determined by the installed OpenCart release, theme, and other extensions. The table describes this module's source adapter; it is not a promise that an old OpenCart core will run on a newer PHP release.

## Automated validation

The release validator performs the following checks on every build:

- PHP syntax validation using the locally available PHP CLI.
- A scan that rejects modern PHP syntax from the OpenCart 2/3 adapter.
- XML parsing for all OCMOD manifests.
- JSON parsing for all OpenCart 4 manifests.
- JavaScript syntax validation for the admin and storefront scripts when Node.js is available.
- Required package-root and runtime-file checks.
- ZIP structure inspection and package-size checks.
- Schedule, weekly recurrence, monthly end-of-month, shortcode, and legacy-migration engine tests.

At release time, record the exact local PHP and Node versions printed by `tools/validate-release.ps1`. Do not rewrite that result as runtime certification for PHP versions that were not actually executed.

## Demo certification checklist

Before marking a target as demo-certified, install its matching archive on that exact OpenCart demo and verify:

1. Clean install and permissions.
2. Saving all campaign tabs and reloading the admin page.
3. One-time, weekly, and monthly active windows.
4. Per-language storefront content.
5. Built-in and uploaded images.
6. Countdown and CTA behavior.
7. Category and product targeting.
8. Multiple campaign queue and priority.
9. Impression, click, close, and reset statistics.
10. Standard new-order email integration.
11. Cache clear without losing a cart or session.
12. Clean uninstall and reinstall.

For OpenCart 3 + Journal, verify the active Journal theme explicitly. Custom themes and custom checkout/mail replacements may require a dedicated integration hook.
