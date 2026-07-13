# Scheduled Popup & Notice Pro

Scheduled Popup & Notice Pro is an OpenCart 3.x extension for timed storefront popups and optional order-confirmation messages.

## Features

- Start and end date/time with timezone support.
- Automatic expiration using an exclusive end date.
- Centered responsive popup with overlay, optional page blur, lazy-loaded background image, keyboard-friendly close button, and session-level dismissal.
- Editable title, main text, submessage, and order email message.
- Email injection runs only while the schedule is active.
- Safe cache-clear and homepage warm-up action in admin; it does not delete carts, orders, customers, or sessions.
- Admin interface translations for English, Romanian, German, Spanish, French, Italian, and Brazilian Portuguese.
- OpenCart 3.x OCMOD package with no external PHP dependencies.

## Compatibility

- OpenCart 3.0.x, tested against 3.0.3.8.
- PHP 7.4+.
- Journal-compatible when the active theme uses the standard OpenCart footer controller/template.

## Installation

1. Upload `dist/scheduled_popup_notice_pro.ocmod.zip` through **Extensions → Installer**.
2. Open **Extensions → Modifications** and click **Refresh**.
3. Open **Extensions → Extensions → Modules**.
4. Install and edit **Scheduled Popup & Notice Pro**.
5. Set the status, timezone, schedule, and messages.

The package is disabled on first install and contains an expired demo schedule so it cannot unexpectedly appear on a live store.

## Publishing notes

The popup text is intentionally stored as configurable content rather than hard-coded translations. This lets merchants write the announcement in any storefront language. The admin controls are translated for the included locales.

The package does not include Cristale-semipretioase.ro branding, credentials, domains, customer data, or site-specific campaign text.
