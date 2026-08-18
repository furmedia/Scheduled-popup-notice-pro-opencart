# Scheduled Popup & Notice Pro 2.0

## Short description

Create multiple scheduled popup campaigns with recurrence, multilingual content, custom artwork, targeting, countdowns, calls to action, anonymous statistics, and optional order-confirmation messages.

## Full description

Scheduled Popup & Notice Pro is a complete announcement campaign manager for OpenCart. It lets a store owner prepare campaigns in advance and automatically display or remove them according to date, time, timezone, recurrence, storefront language, page target, and priority.

The extension is suitable for shipping pauses, holidays, launches, flash sales, maintenance announcements, legal notices, seasonal messages, and other temporary storefront communication. No cron job, external service, or theme-file editing is required.

When several campaigns are active, they are queued by priority and shown one at a time. A campaign can also add a matching message to the new-order email. Once its occurrence ends, the popup and email message stop automatically.

## Pro campaign features

- Up to 50 independent campaigns.
- Internal campaign name, enabled status, and display priority.
- One-time, weekly, or monthly recurrence with native calendar and time pickers.
- Grouped timezone selector with current UTC offsets and optional recurrence end date.
- Exclusive end-time logic for precise automatic expiration.
- Separate popup and email copy for every enabled OpenCart storefront language, with editable localized starter copy.
- Dynamic shortcodes for dates, times, remaining time, store name, campaign name, and year.
- Custom JPG, PNG, or WebP image upload from admin, up to 5 MB and 20 megapixels, with automatic resizing and WebP optimization when supported by the server.
- Elegant, Minimal, and Bold visual presets.
- Editable accent, background, text, button, and overlay colors.
- Adjustable overlay opacity and page blur.
- Optional live countdown.
- Configurable call-to-action button, URL, and same-tab/new-tab behavior.
- Display on every page, selected categories, or selected products.
- Recent-product suggestions and search by product name, model, SKU, or exact ID.
- One-click Romanian and English content templates prefilled with dynamic shortcodes.
- Anonymous impression, CTA click, and close statistics with CTR.
- Session-level event deduplication and per-occurrence dismissal.
- Optional order-confirmation email message using the same schedule, language, and product/category targeting rules.
- Safe cache clear and storefront warm-up tool that does not delete carts, sessions, customers, or orders.
- Automatic migration of existing 1.x settings into an imported campaign.

## Storefront experience

- Responsive centered dialog for desktop and mobile.
- Visible close control and Escape-key support.
- Accessible dialog labels, focus handling, and reduced-motion support.
- Optional backdrop blur without modifying the store layout wrapper.
- Multiple matching campaigns appear sequentially after the visitor closes the current campaign.
- Popup content is rendered as text to avoid accidental HTML or script injection.

## Dynamic shortcodes

Use curly braces or square brackets, for example `{start_date}` or `[start_date]`.

- `{start_date}` and `{start_time}`
- `{end_date}` and `{end_time}`
- `{days_remaining}` and `{hours_remaining}`
- `{countdown}`
- `{store_name}`
- `{campaign_name}`
- `{year}`

Date and time formats are configurable per campaign. This makes recurring notices reusable: set the schedule once and let the displayed dates update automatically. Shortcode buttons insert directly into the currently focused campaign field.

## Included languages

- English
- Romanian (`ro-ro` and legacy `romanian` directory)
- German
- French
- Spanish
- Italian
- Brazilian Portuguese

The admin interface and storefront labels are translated. Campaign title, main message, secondary message, footer, countdown label, CTA text, and order-email message can be edited independently for each enabled storefront language.

## Compatibility downloads

The product includes separate packages for:

- OpenCart 2.0-2.2
- OpenCart 2.3.x
- OpenCart 3.0.x
- OpenCart 3.0.x with Journal 3
- OpenCart 4.0.x
- OpenCart 4.1.x

Install only the archive matching the target OpenCart generation. OpenCart 2 and 3 use OCMOD. OpenCart 4 uses native extension events.

## Privacy and dependencies

- No SaaS account, cron service, tracking provider, or external PHP library is required.
- Statistics are aggregate counters by campaign, event type, and date.
- No IP address, email address, customer ID, or browser fingerprint is stored by the statistics feature.
- Uploaded campaign images remain in the store's own image directory; servers with GD/WebP support resize them to fit within 1280 x 960 and encode quality-82 WebP automatically.

## Upgrade notes

To upgrade from 1.x, upload version 2.0 without uninstalling the existing module first. On OpenCart 2/3, refresh Modifications, open the module, and save once. Existing schedule and message fields are migrated to an Imported campaign.

## Support scope

Support covers installation, permissions, OCMOD refresh, native OpenCart 4 events, campaign configuration, recurrence, translations, targeting, popup display, statistics, image upload, and the standard new-order email integration. A custom theme or checkout that replaces the standard footer or mail pipeline may require a theme-specific adapter.
