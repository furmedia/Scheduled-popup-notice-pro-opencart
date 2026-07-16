# Marketplace Release Checklist

## Release model

This product is shipped as six separate OpenCart packages. Install only the archive that matches the target store:

| Target | Marketplace archive | Installation method |
|---|---|---|
| OpenCart 2.0-2.2 | `scheduled_popup_notice_pro_oc2_20_22.ocmod.zip` | Upload the package, then refresh Modifications |
| OpenCart 2.3 | `scheduled_popup_notice_pro_oc2_23.ocmod.zip` | Upload the package, then refresh Modifications |
| OpenCart 3 | `scheduled_popup_notice_pro_oc3.ocmod.zip` | Upload the package, then refresh Modifications |
| OpenCart 3 + Journal 3 | `scheduled_popup_notice_pro_oc3_journal.ocmod.zip` | Upload the package, then refresh Modifications |
| OpenCart 4.0 | `scheduled_popup_notice_pro_oc4_40.ocmod.zip` | Upload through Extension Installer; use the native `install.json` events |
| OpenCart 4.1 | `scheduled_popup_notice_pro_oc4_41.ocmod.zip` | Upload through Extension Installer; use the native `install.json` events |

The OpenCart 4 packages are native event-based extensions and are not OCMOD refresh packages. OpenCart's extension documentation requires an OpenCart 4 package root with `install.json`, `admin/`, and `catalog/`; it also documents the Extension Installer and the 32 MB package limit. See the [official OpenCart extension documentation](https://docs.opencart.com/developer-guide/extensions).

Do not merge all six archives into one installable ZIP. A single combined archive would make version detection and marketplace installation ambiguous.

## Languages

The module includes English, Romanian, German, French, Spanish, Italian, and Portuguese admin and storefront translations. The `romanian` locale directory is retained as a compatibility alias for stores that use the older OpenCart Romanian locale key; `ro-ro` is the standard Romanian tree.

## Verification completed

- PHP syntax checked for every package adapter with PHP 5.6, 7.4, and 8.2 runtimes.
- All XML OCMOD files parsed successfully.
- All OpenCart 4 JSON installer manifests parsed successfully.
- Every archive was checked for the expected root structure and is below 32 MB.
- Six Foxly storefronts and their admin login pages were reachable on 2026-07-16.

## Verification still required before marketplace certification

An authenticated administrator session is required to install and test each archive on the six Foxly demos. For each target, complete a clean install, save settings, activate a current schedule, test popup close and blur cleanup, verify the order-email hook, refresh modifications/cache where applicable, and uninstall cleanly. The OpenCart 3 + Journal package must also be checked with the Journal theme enabled.

The current repository therefore documents static compatibility and public demo reachability, but does not claim remote installation certification until those authenticated admin tests are completed.

## Listing material

- English listing copy: `MARKETPLACE_LISTING.md`
- Main promotional image: `assets/scheduled-popup-notice-pro-hero-710x380.jpg`
- User guide and installation notes: `README.md`
- Compatibility details: `docs/COMPATIBILITY.md`

