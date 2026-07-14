# Scheduled Popup & Notice Pro

Scheduled Popup & Notice Pro is a family of version-specific OpenCart extensions for timed storefront popups and optional order-confirmation messages.

## Features

- Start and end date/time with timezone support.
- Automatic expiration using an exclusive end date.
- Centered responsive popup with overlay, optional page blur, lazy-loaded background image, keyboard-friendly close button, and session-level dismissal.
- Editable title, main text, submessage, and order email message.
- Email injection runs only while the schedule is active.
- Safe cache-clear and homepage warm-up action in admin; it does not delete carts, orders, customers, or sessions.
- Admin interface translations for English, Romanian, German, Spanish, French, Italian, and Brazilian Portuguese.
- OpenCart 3.x OCMOD package with no external PHP dependencies.

## Compatibility packages

Use the archive matching the installed OpenCart generation. Do not install the OpenCart 3 archive on OpenCart 2 or 4.

| Package | Target | Status |
|---|---|---|
| `scheduled_popup_notice_pro_oc2_20_22.ocmod.zip` | OpenCart 2.0–2.2 | legacy OCMOD adapter |
| `scheduled_popup_notice_pro_oc2_23.ocmod.zip` | OpenCart 2.3.x | extension/module OCMOD adapter |
| `scheduled_popup_notice_pro_oc3.ocmod.zip` | OpenCart 3.0.x | supported OCMOD adapter |
| `scheduled_popup_notice_pro_oc3_journal.ocmod.zip` | OpenCart 3 + Journal 3 | Journal-aware OCMOD adapter |
| `scheduled_popup_notice_pro_oc4_40.ocmod.zip` | OpenCart 4.0.x | native event extension |
| `scheduled_popup_notice_pro_oc4_41.ocmod.zip` | OpenCart 4.1.x | native event extension |

The OpenCart 2 and OpenCart 4 archives are deliberately separate because those releases do not share the OpenCart 3 route, template, event, or installer contracts. See `docs/COMPATIBILITY.md` and the per-version `docs/` files for exact boundaries and test requirements. PHP support follows the target OpenCart release; the package does not change the store's PHP requirements.

## Installation

1. Choose the archive for the exact OpenCart generation from `dist/`.
2. Upload it through **Extensions → Installer**.
3. For OpenCart 2 and 3, open **Extensions → Modifications** and click **Refresh**.
4. Open **Extensions → Extensions → Modules**, install and edit **Scheduled Popup & Notice Pro**.
5. Set the status, timezone, schedule, and messages.

OpenCart 4 uses native events and `install.json`; it does not require an OCMOD refresh. The module installation registers the footer popup and order-email events automatically.

The package is disabled on first install and contains an expired demo schedule so it cannot unexpectedly appear on a live store.

## Publishing notes

The popup text is intentionally stored as configurable content rather than hard-coded translations. This lets merchants write the announcement in any storefront language. The admin controls are translated for the included locales.

The package does not include Cristale-semipretioase.ro branding, credentials, domains, customer data, or site-specific campaign text.
