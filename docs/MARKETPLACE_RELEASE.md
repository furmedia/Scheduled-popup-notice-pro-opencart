# Marketplace Release Checklist

## Product metadata

- Product name: **Scheduled Popup & Notice Pro**
- Release version: **2.0.3**
- Author: **Furmedia**
- License model requested for the listing: **USD 19 one-time, 12 months support**
- Listing copy: `MARKETPLACE_LISTING.md`
- Changelog: `CHANGELOG.md`
- Main image: `assets/scheduled-popup-notice-pro-hero-710x380.jpg`
- Thumbnail: `assets/scheduled-popup-notice-pro-thumbnail-260x152.jpg`

## Version-specific downloads

| Target | Archive | Installation method |
|---|---|---|
| OpenCart 2.0-2.2 | `scheduled_popup_notice_pro_oc2_20_22.ocmod.zip` | Installer, Modifications refresh, install module |
| OpenCart 2.3 | `scheduled_popup_notice_pro_oc2_23.ocmod.zip` | Installer, Modifications refresh, install module |
| OpenCart 3.0 | `scheduled_popup_notice_pro_oc3.ocmod.zip` | Installer, Modifications refresh, install module |
| OpenCart 3.0 + Journal 3 | `scheduled_popup_notice_pro_oc3_journal.ocmod.zip` | Installer, Modifications refresh, install module |
| OpenCart 4.0 | `scheduled_popup_notice_pro_oc4_40.ocmod.zip` | Extension Installer, install module; native events |
| OpenCart 4.1 | `scheduled_popup_notice_pro_oc4_41.ocmod.zip` | Extension Installer, install module; native events |

Do not upload `scheduled_popup_notice_pro_all_versions.zip` as an installable extension. If used, it is only a customer download bundle containing documentation and the six version-specific packages. Each OpenCart store must receive exactly one matching package.

## Build and validate

From the module root:

```powershell
powershell -ExecutionPolicy Bypass -File .\tools\validate-release.ps1
```

The command regenerates all compatibility trees and ZIP files, runs static and engine tests, validates manifests, checks package roots, and prints checksums. A failed check stops the release.

## Manual QA before upload

- Install each package on its matching Foxly demo.
- Confirm the module appears under Extensions > Modules.
- Confirm a disabled first install does not display a popup.
- Exercise all campaign tabs and save/reload.
- Verify desktop and mobile popup behavior.
- Verify multiple campaigns, recurrence, language fallback, uploaded image, colors, countdown, CTA, targeting, and statistics.
- Place a test order and inspect both HTML and text emails.
- Verify category/product-targeted email inclusion and exclusion.
- Clear module cache with a product in the cart and confirm the cart remains intact.
- Test uninstall/reinstall and 1.x upgrade migration.
- For Journal, verify with Journal 3 enabled.

## Marketplace upload

1. Use the English title and copy from `MARKETPLACE_LISTING.md`.
2. Upload the 710x380 image and the thumbnail.
3. Add each version-specific ZIP to the corresponding OpenCart version selector.
4. Set the listing price and support period.
5. Add the English user guide and compatibility matrix to the listing documentation.
6. Do not mark a demo/version as certified until its row has passed the manual QA list.

The OpenCart 4 package root contains `install.json`, `admin/`, `catalog/`, and `system/`. OpenCart 2/3 package roots contain `install.xml`, `upload/`, and documentation.
