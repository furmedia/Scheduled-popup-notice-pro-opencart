# Scheduled Popup & Notice Pro 2.0 User Guide

## 1. Installation

Choose the ZIP that exactly matches the store's OpenCart generation. Do not install an OpenCart 3 package on OpenCart 2 or 4.

### OpenCart 2 and 3

1. Open **Extensions > Installer** and upload the matching `.ocmod.zip`.
2. Open **Extensions > Modifications** and click **Refresh**.
3. Open **Extensions > Extensions > Modules**.
4. Install and edit **Scheduled Popup & Notice Pro**.
5. Grant `access` and `modify` permission to the administrator group if OpenCart requests it.

### OpenCart 4

1. Open **Extensions > Installer** and upload the matching `.ocmod.zip`.
2. Open **Extensions > Extensions > Modules**.
3. Install and edit **Scheduled Popup & Notice Pro**.

The module is disabled by default. OpenCart 4 registers native events during installation; no Modifications refresh is needed.

## 2. Module status and campaign list

The main status controls the complete module. A campaign also has its own status. Both must be enabled before the campaign can appear.

Use **Add campaign**, **Clone**, and **Delete** to manage campaigns. The internal name is visible only in admin and statistics. A higher priority campaign is shown before a lower priority campaign when both match.

The module accepts up to 50 campaigns. After changing settings, use the main **Save** button.

## 3. Schedule

For each campaign set:

- **Timezone**: for example `Europe/Bucharest`.
- **Starts at**: first start date and time.
- **Ends at**: first end date and time. The end is exclusive.
- **Recurrence**: none, weekly, or monthly.
- **Repeat until**: optional final date/time for recurring campaigns.
- **Date format** and **time format**: PHP date-format characters used by shortcodes.

A weekly campaign repeats at the same local weekday and time. A monthly campaign repeats on the same day where possible; a start on day 29, 30, or 31 is moved to the final day of shorter months.

No cron job is required. The active occurrence is calculated when a storefront page or order email is generated.

## 4. Content by language

Each enabled storefront language has independent fields for:

- popup title;
- main message;
- secondary message;
- footer message;
- countdown label;
- CTA button text;
- order-confirmation email message.

The current storefront language is selected automatically. For order email, the order's language is used. New campaigns start with editable localized copy for the seven bundled languages. The popup title and main message are required for every enabled storefront language; optional fields may be left empty when they should not be displayed.

Content is plain text. This prevents pasted HTML or scripts from breaking the store layout.

## 5. Shortcodes

Shortcodes can be used in every campaign text field. Both `{name}` and `[name]` syntax are supported. Focus a text field and click a shortcode button to insert it at the cursor position.

| Shortcode | Result |
|---|---|
| `{start_date}` | current occurrence start date |
| `{start_time}` | current occurrence start time |
| `{end_date}` | current occurrence end date |
| `{end_time}` | current occurrence end time |
| `{days_remaining}` | remaining days, rounded up |
| `{hours_remaining}` | remaining hours, rounded up |
| `{countdown}` | static days:hours:minutes value when rendered |
| `{store_name}` | OpenCart store name |
| `{campaign_name}` | internal campaign name |
| `{year}` | current year in the campaign timezone |

Example:

```text
Orders placed between {start_date} and {end_date} will be dispatched after {end_date}.
```

With recurrence, the dates change automatically for every occurrence.

## 6. Image and design

Choose the built-in artwork or upload a JPG, PNG, or WebP image up to 5 MB and 20 megapixels. On servers with GD/WebP support, uploads are resized to fit within 1280 x 960 and encoded as quality-82 WebP. If WebP encoding is unavailable, the validated original format is retained. Uploaded files are stored under `image/catalog/scheduled-popup-notice/`.

Choose Elegant, Minimal, or Bold as a starting preset, then edit:

- accent color;
- background color;
- text color;
- button color;
- overlay color;
- overlay opacity;
- page blur.

The live preview uses the selected campaign and language. It is a preview, not a second storefront popup.

## 7. Countdown and CTA

Enable the live countdown to display the remaining time until the active occurrence ends. The countdown label is translated per campaign language.

Enable the CTA to show a button in the popup. Configure its translated text, URL, and whether it opens in the same tab or a new tab. Absolute `http`/`https` URLs and local URLs beginning with `/`, `?`, or `#` are accepted.

## 8. Page targeting

Choose one target mode:

- **All pages**: every storefront page can display the campaign.
- **Selected categories**: category pages whose category ID was selected.
- **Selected products**: product pages whose product ID was selected.

Use autocomplete to add categories or products. Targeting also applies to the optional order email: at least one ordered product must match the selected product or belong to a selected category.

## 9. Multiple campaigns and dismissal

All matching active campaigns are sorted by priority and queued. The visitor sees one popup at a time. Closing it reveals the next matching campaign.

Dismissal is saved only for the current browser session and the current recurrence occurrence. A new weekly or monthly occurrence may appear again.

## 10. Statistics

The statistics tab reports:

- impressions;
- CTA clicks;
- closes;
- click-through rate.

Events are deduplicated per browser session and occurrence. Only aggregate campaign ID, event type, date, and count are stored. The module does not store IP addresses, emails, customer IDs, or fingerprints.

Use **Reset statistics** to clear the selected campaign's counters.

## 11. Order email message

When the email field is not empty, the campaign is active, and the order matches the target, the message is appended through the standard new-order email integration. Schedule shortcodes are replaced using the order language and store name.

The feature does not alter old orders or emails. Custom mail extensions that completely replace OpenCart's standard mail event may need an additional adapter.

## 12. Cache tool

Use **Clear cache and warm storefront** after changing theme templates or when old popup markup remains visible. The action removes OpenCart cache files and requests the storefront once. It does not delete carts, sessions, orders, customers, or product data.

## 13. Upgrade from 1.x

Do not uninstall version 1.x first.

1. Upload the matching 2.0 package over the old version.
2. Refresh Modifications on OpenCart 2/3.
3. Open the module and save once.
4. Review the **Imported campaign**, language fields, dates, and status.

## 14. Troubleshooting

- Popup absent: verify module status, campaign status, schedule timezone, target page, and session dismissal.
- Old output after update: refresh Modifications on OpenCart 2/3, clear theme cache, then use the module cache action.
- Image absent: verify file type, size, image-directory permissions, and HTTPS URL.
- Email message absent: verify the field in the order language, active schedule, order target, and standard mail hook.
- Journal issue: use the dedicated Journal package and refresh Journal/OpenCart caches.
