# Scheduled Popup & Notice Pro 2.0

Scheduled Popup & Notice Pro is a campaign manager for timed storefront popups and optional order-confirmation messages. It ships as version-specific packages for OpenCart 2.0 through OpenCart 4.1, including a dedicated OpenCart 3 + Journal 3 build.

## Features

- Up to 50 independent campaigns with status, internal name and priority.
- One-time, weekly or monthly schedules with timezone, optional recurrence end, and native calendar/time pickers.
- Automatic activation and exclusive expiration; no cron job is required.
- Separate popup and email content for every enabled storefront language.
- Dynamic date, time, countdown, store and campaign shortcodes.
- Custom JPG, PNG or WebP campaign image upload, automatically resized and converted to WebP when supported by the server.
- Elegant, Minimal and Bold presets plus editable accent, background, text, button and overlay colors.
- Adjustable overlay opacity and page blur.
- Optional live countdown.
- Optional call-to-action button with URL and same-tab/new-tab behavior.
- Display on all pages, selected categories or selected products.
- Anonymous session-deduplicated impression, button-click and close statistics with CTR.
- Responsive accessible dialog, visible close button, Escape support and session dismissal.
- Multiple active campaigns are shown in priority order, one after another.
- Optional order-confirmation message follows the same schedule, language and product/category targeting rules.
- Safe OpenCart cache-clear and homepage warm-up tool; carts, sessions, customers and orders are not deleted.
- Existing 1.x settings are migrated to an imported campaign during upgrade.
- No external PHP library, SaaS account or cron service is required.

## Dynamic shortcodes

Shortcodes may be used in the popup title, main message, secondary message, footer message, countdown label, button text and order email message.

| Shortcode | Value |
|---|---|
| `{start_date}` | Start date of the current occurrence |
| `{start_time}` | Start time of the current occurrence |
| `{end_date}` | End date of the current occurrence |
| `{end_time}` | End time of the current occurrence |
| `{days_remaining}` | Remaining whole/partial days, rounded up |
| `{hours_remaining}` | Remaining whole/partial hours, rounded up |
| `{countdown}` | Static `days:hours:minutes` value generated at render time |
| `{store_name}` | Current OpenCart store name |
| `{campaign_name}` | Internal campaign name |
| `{year}` | Current year in the campaign timezone |

Square-bracket aliases such as `[start_date]` are also accepted. Date and time output formats are configurable per campaign using PHP date-format characters. In admin, focus any campaign text field and click a shortcode to insert it at the cursor position.

## Compatibility packages

Install only the archive matching the store generation.

| Archive | Target | Integration |
|---|---|---|
| `scheduled_popup_notice_pro_oc2_20_22.ocmod.zip` | OpenCart 2.0-2.2 | legacy controller/TPL OCMOD adapter |
| `scheduled_popup_notice_pro_oc2_23.ocmod.zip` | OpenCart 2.3.x | extension/module TPL OCMOD adapter |
| `scheduled_popup_notice_pro_oc3.ocmod.zip` | OpenCart 3.0.x | OCMOD + Twig |
| `scheduled_popup_notice_pro_oc3_journal.ocmod.zip` | OpenCart 3.0.x + Journal 3 | OCMOD + wildcard theme footer hook |
| `scheduled_popup_notice_pro_oc4_40.ocmod.zip` | OpenCart 4.0.x | native extension events |
| `scheduled_popup_notice_pro_oc4_41.ocmod.zip` | OpenCart 4.1.x | native extension events |

The shared OpenCart 2/3 runtime is intentionally written with PHP 5.6-compatible syntax. The OpenCart 4 adapter uses PHP 8 syntax and follows the PHP requirement of the installed OpenCart 4 release. The store and its other extensions may impose stricter PHP limits.

## Installation

### OpenCart 2 and 3

1. Upload the matching `.ocmod.zip` in **Extensions > Installer**.
2. Open **Extensions > Modifications** and click **Refresh**.
3. Open **Extensions > Extensions > Modules**.
4. Install and edit **Scheduled Popup & Notice Pro**.
5. Enable the module, create or edit a campaign, then save.

### OpenCart 4

1. Upload the matching `.ocmod.zip` in **Extensions > Installer**.
2. Open **Extensions > Extensions > Modules**.
3. Install and edit **Scheduled Popup & Notice Pro**.
4. Enable the module, create or edit a campaign, then save.

OpenCart 4 registers native storefront and order-email events during module installation. It does not need an OCMOD refresh.

## Upgrading from 1.x

Do not uninstall the old module first. Upload the matching 2.0 package over it, refresh Modifications on OpenCart 2/3, open the module and save once. The legacy schedule and text are preserved as an **Imported campaign**. Review the new per-language tabs and enable the campaign only after its dates are correct.

## Campaign behavior

- The end time is exclusive: a campaign ending at `12:00:00` is inactive at exactly 12:00.
- Monthly schedules keep the selected day when possible and clamp to the final day of shorter months.
- A visitor dismisses one occurrence for the current browser session. A later weekly/monthly occurrence can display again.
- When several campaigns match, higher priority is displayed first. The next campaign appears after the current one is closed.
- Product and category targeting is also applied to the order email: at least one matching ordered product is required.
- Statistics store campaign ID, event type, calendar date and aggregate count only. No IP, email, customer ID or browser fingerprint is stored.

## Languages

Admin and storefront labels are included for English, Romanian, German, French, Spanish, Italian and Brazilian Portuguese. Both `ro-ro` and the legacy `romanian` locale directory are supplied. Campaign copy is entered separately for every enabled OpenCart language, and new campaigns receive editable starter copy in the matching language.

## Limits and safety

- 1-50 campaigns per module configuration.
- Custom images: JPG, PNG or WebP, maximum 5 MB and 20 megapixels.
- Servers with GD/WebP support automatically resize uploads to fit within 1280 x 960 and encode them as quality-82 WebP; compatible servers without WebP retain the validated original format.
- Uploaded campaign images are restricted to `image/catalog/scheduled-popup-notice/`.
- Popup content is plain text and rendered with DOM text nodes.
- Custom colors are validated as six-digit hexadecimal values.
- The CTA accepts valid absolute URLs or local URLs beginning with `/`, `?` or `#`.
- The package does not include store credentials, customer data, domains, branding or a pre-enabled live campaign.

See [User Guide](docs/USER_GUIDE.md), [Ghid in romana](docs/GHID_UTILIZARE_RO.md), [Compatibility Matrix](docs/COMPATIBILITY.md), and [Marketplace Release Checklist](docs/MARKETPLACE_RELEASE.md).
