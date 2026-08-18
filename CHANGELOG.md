# Changelog

## 2.0.3 - 2026-08-19

- Replaced the free-text timezone field with a grouped selector containing PHP-supported timezones and their current UTC offsets.
- Added recent-product suggestions and searchable targeting by product name, model, SKU, or exact product ID.
- Added ready-to-use Romanian and English content templates containing dynamic schedule and store shortcodes.
- Localized new-campaign starter content for all bundled languages and migrated unchanged English placeholders in non-English language fields.

## 2.0.2 - 2026-08-19

- Replaced plain schedule date fields with native calendar and time pickers.
- Added accessible calendar icon buttons that open the picker when supported by the browser.
- Preserved the OpenCart storage format automatically, including compatibility with existing campaign dates.

## 2.0.1 - 2026-08-18

- Added automatic campaign-image resizing to a maximum of 1280 x 960 and quality-82 WebP encoding when supported by GD.
- Added a compatibility fallback that preserves the validated original image on servers without WebP encoding support.
- Added a 20-megapixel upload safety limit and collision-resistant image filenames.
- Removed an unused 1 MB legacy PNG from all storefront packages; the built-in 20 KB WebP remains unchanged.

## 2.0.0 - 2026-08-18

- Added management for up to 50 simultaneous campaigns.
- Added campaign status, internal name, and display priority.
- Added one-time, weekly, and monthly schedules with timezone and optional recurrence end.
- Added per-language title, main text, secondary text, footer, countdown label, CTA text, and order-email message.
- Added dynamic date, time, remaining-time, store, campaign, and year shortcodes with curly-brace and square-bracket syntax.
- Added campaign-specific JPG, PNG, and WebP image upload from admin, with automatic resizing and WebP optimization when supported by the server.
- Added Elegant, Minimal, and Bold presets plus editable colors, overlay opacity, and blur.
- Added an optional live countdown.
- Added configurable CTA URL and same-tab/new-tab behavior.
- Added all-page, selected-category, and selected-product targeting with admin autocomplete.
- Added anonymous aggregate impression, CTA-click, and close statistics with CTR and reset action.
- Added campaign queueing, occurrence-specific session dismissal, focus restoration, Escape support, and reduced-motion support.
- Applied schedule, language, and product/category targeting to the optional order-confirmation message.
- Added automatic migration of 1.x settings to an Imported campaign.
- Added compatibility adapters and packages for OpenCart 2.0-2.2, 2.3, 3.0, 3 + Journal 3, 4.0, and 4.1.
- Added English and Romanian user guides, a compatibility matrix, Marketplace copy, and automated release validation.

## 1.0.0

- First Marketplace-ready release.
- Added scheduled popup with automatic expiration.
- Added optional order-confirmation email message.
- Added responsive overlay, blur, lazy-loaded image, accessible close control, and session dismissal.
- Added admin translations for seven locales plus the legacy Romanian locale directory.
- Removed store-specific campaign data and branding from the distribution package.
