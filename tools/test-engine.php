<?php
require_once dirname(__DIR__) . '/opencart/upload/system/library/furmedia_scheduled_popup.php';

$passed = 0;

function check($condition, $message) {
    global $passed;
    if (!$condition) {
        fwrite(STDERR, "FAIL: " . $message . PHP_EOL);
        exit(1);
    }
    $passed++;
}

function campaign($start, $end, $recurrence) {
    $value = FurmediaScheduledPopupEngine::defaultCampaign(array(1, 2));
    $value['status'] = 1;
    $value['name'] = 'QA campaign';
    $value['timezone'] = 'Europe/Bucharest';
    $value['starts_at'] = $start;
    $value['ends_at'] = $end;
    $value['recurrence'] = $recurrence;
    return $value;
}

$one = campaign('2026-08-18 10:00:00', '2026-08-18 12:00:00', 'none');
$active = FurmediaScheduledPopupEngine::activeOccurrence($one, new DateTime('2026-08-18 11:00:00', new DateTimeZone('Europe/Bucharest')));
check($active !== false, 'one-time campaign is active inside its window');
check(FurmediaScheduledPopupEngine::activeOccurrence($one, new DateTime('2026-08-18 12:00:00', new DateTimeZone('Europe/Bucharest'))) === false, 'end time is exclusive');

$weekly = campaign('2026-03-23 10:00:00', '2026-03-23 12:00:00', 'weekly');
$active = FurmediaScheduledPopupEngine::activeOccurrence($weekly, new DateTime('2026-03-30 10:30:00', new DateTimeZone('Europe/Bucharest')));
check($active !== false, 'weekly recurrence remains aligned across daylight-saving change');
check($active['start']->format('Y-m-d H:i:s') === '2026-03-30 10:00:00', 'weekly recurrence keeps local start time');

$monthly = campaign('2026-01-31 10:00:00', '2026-01-31 12:00:00', 'monthly');
$active = FurmediaScheduledPopupEngine::activeOccurrence($monthly, new DateTime('2026-02-28 10:30:00', new DateTimeZone('Europe/Bucharest')));
check($active !== false, 'monthly recurrence clamps to the final day of a shorter month');
check($active['start']->format('Y-m-d H:i:s') === '2026-02-28 10:00:00', 'monthly clamped date is correct');

$yearly = campaign('2024-02-29 10:00:00', '2024-02-29 12:00:00', 'yearly');
$active = FurmediaScheduledPopupEngine::activeOccurrence($yearly, new DateTime('2025-02-28 10:30:00', new DateTimeZone('Europe/Bucharest')));
check($active !== false, 'yearly recurrence supports leap-day campaigns');
check($active['start']->format('Y-m-d H:i:s') === '2025-02-28 10:00:00', 'yearly recurrence clamps leap day in non-leap years');

$monthly['recurrence_until'] = '2026-02-28 10:15:00';
check(FurmediaScheduledPopupEngine::activeOccurrence($monthly, new DateTime('2026-02-28 10:15:00', new DateTimeZone('Europe/Bucharest'))) === false, 'recurrence end is exclusive');

$shortcodeCampaign = $one;
$shortcodeCampaign['date_format'] = 'd.m.Y';
$shortcodeCampaign['time_format'] = 'H:i';
$now = new DateTime('2026-08-18 11:00:00', new DateTimeZone('Europe/Bucharest'));
$occurrence = FurmediaScheduledPopupEngine::activeOccurrence($shortcodeCampaign, $now);
$rendered = FurmediaScheduledPopupEngine::replaceShortcodes(
    '{start_date}|[end_time]|{hours_remaining}|{store_name}|{campaign_name}|{year}',
    $shortcodeCampaign,
    $occurrence,
    'QA Store',
    $now
);
check($rendered === '18.08.2026|12:00|1|QA Store|QA campaign|2026', 'all shortcode families and aliases are replaced');

$legacy = FurmediaScheduledPopupEngine::fromLegacy(array(1, 2), array(
    'status' => 1,
    'timezone' => 'Europe/Bucharest',
    'starts_at' => '2026-08-18 10:00:00',
    'ends_at' => '2026-08-18 12:00:00',
    'banner_title' => 'Legacy title',
    'banner_message' => 'Legacy message',
    'banner_submessage' => 'Legacy submessage',
    'email_message' => 'Legacy email'
));
check($legacy['name'] === 'Imported campaign' && $legacy['content']['2']['email_message'] === 'Legacy email', 'legacy settings migrate to every enabled language');

$localized = FurmediaScheduledPopupEngine::defaultCampaign(
    array(1, 2),
    array(1 => 'ro-ro', 2 => 'en-gb')
);
check($localized['content']['1']['title'] === 'Anunț important', 'Romanian starter content follows the language code');

$all_day = FurmediaScheduledPopupEngine::defaultCampaign(array(1), array(1 => 'en-gb'));
$all_day['status'] = 1;
$all_day['timezone'] = 'Europe/Bucharest';
$all_day['starts_at'] = '2026-08-20 09:30:00';
$all_day['ends_at'] = '2026-08-24 10:15:00';
$all_day['starts_at_time'] = '';
$all_day['ends_at_time'] = '';
$all_day = FurmediaScheduledPopupEngine::normalizeCampaign($all_day, array(1), array(1 => 'en-gb'));
check($all_day['starts_at'] === '2026-08-20 00:00:00' && $all_day['ends_at'] === '2026-08-24 23:59:59', 'blank optional times cover the complete selected dates');
check(FurmediaScheduledPopupEngine::activeOccurrence($all_day, new DateTime('2026-08-24 23:30:00', new DateTimeZone('Europe/Bucharest'))) !== false, 'date-only campaign remains active through the final selected day');

$legacy_time = campaign('2026-08-20 09:30:00', '2026-08-24 10:15:00', 'none');
unset($legacy_time['starts_at_time'], $legacy_time['ends_at_time']);
$legacy_time = FurmediaScheduledPopupEngine::normalizeCampaign($legacy_time, array(1, 2));
check($legacy_time['starts_at_time'] === '09:30' && $legacy_time['ends_at_time'] === '10:15', 'existing campaigns preserve their previously saved times');

$device_targeted = FurmediaScheduledPopupEngine::defaultCampaign(array(1));
check($device_targeted['devices'] === array('desktop', 'tablet', 'mobile'), 'new and existing campaigns target every device by default');
$device_targeted['devices'] = array('mobile', 'invalid', 'desktop', 'mobile');
$device_targeted = FurmediaScheduledPopupEngine::normalizeCampaign($device_targeted, array(1));
check($device_targeted['devices'] === array('mobile', 'desktop'), 'device targeting removes invalid and duplicate values');
$responsive = FurmediaScheduledPopupEngine::defaultCampaign(array(1));
check($responsive['popup_width_laptop'] === 520 && $responsive['breakpoint_laptop'] === 1440, 'responsive profiles have backward-compatible defaults');
$responsive['popup_width_mobile'] = 10;
$responsive['popup_width_desktop'] = 9000;
$responsive['popup_height_tablet'] = 200;
$responsive['breakpoint_mobile'] = 850;
$responsive['breakpoint_tablet'] = 700;
$responsive['breakpoint_laptop'] = 800;
$responsive = FurmediaScheduledPopupEngine::normalizeCampaign($responsive, array(1));
check($responsive['popup_width_mobile'] === 240 && $responsive['popup_width_desktop'] === 1600 && $responsive['popup_height_tablet'] === 98, 'responsive popup dimensions are clamped to safe limits');
check($responsive['breakpoint_mobile'] < $responsive['breakpoint_tablet'] && $responsive['breakpoint_tablet'] < $responsive['breakpoint_laptop'], 'responsive breakpoints remain strictly ordered');
check(strpos($localized['content']['1']['message'], '{store_name}') !== false, 'Romanian starter content includes dynamic shortcodes');
check($localized['content']['2']['title'] === 'Important announcement', 'English starter content follows the language code');
check(strpos($localized['content']['2']['email_message'], '{start_time}') !== false, 'English starter email includes dynamic shortcodes');

$oldEnglish = FurmediaScheduledPopupEngine::defaultCampaign(array(1));
$migrated = FurmediaScheduledPopupEngine::normalizeCampaign(
    $oldEnglish,
    array(1),
    array(1 => 'ro-ro')
);
check($migrated['content']['1']['title'] === 'Anunț important', 'unchanged English placeholder content migrates to Romanian');

$intermediateEnglish = $oldEnglish;
$intermediateEnglish['content']['1'] = array(
    'title' => 'Important announcement',
    'message' => 'Use this space for your scheduled announcement.',
    'submessage' => 'The popup automatically disappears after the end date.',
    'thanks' => 'Thank you for your understanding!',
    'email_message' => 'This message is added to order confirmation emails while the schedule is active.',
    'button_text' => 'Learn more',
    'countdown_label' => 'Time remaining'
);
$migratedIntermediate = FurmediaScheduledPopupEngine::normalizeCampaign(
    $intermediateEnglish,
    array(1),
    array(1 => 'ro-ro')
);
check($migratedIntermediate['content']['1']['title'] === 'Anunț important', 'intermediate English placeholder content migrates to Romanian');
check(strpos($migratedIntermediate['content']['1']['message'], '{store_name}') !== false, 'intermediate placeholder migration includes dynamic shortcodes');

$customRomanian = $oldEnglish;
$customRomanian['content']['1']['title'] = 'Mesajul meu';
$preserved = FurmediaScheduledPopupEngine::normalizeCampaign(
    $customRomanian,
    array(1),
    array(1 => 'ro-ro')
);
check($preserved['content']['1']['title'] === 'Mesajul meu', 'custom non-English content is preserved during normalization');

$normalized = FurmediaScheduledPopupEngine::normalizeCampaign(array_merge($one, array(
    'accent_color' => 'javascript:red',
    'button_url' => 'javascript:alert(1)',
    'image' => '../bad.php',
    'target_products' => array(7, '7', -2, 9)
)), array(1));
check($normalized['accent_color'] === '#713568', 'invalid color falls back safely');
check($normalized['button_url'] === '', 'unsafe CTA URL is removed');
check($normalized['image'] === '', 'unsafe image path is removed');
check($normalized['target_products'] === array(7, 9), 'target IDs are positive and unique');

$low = $one;
$high = $one;
$low['priority'] = 1;
$high['priority'] = 20;
$ordered = array($low, $high);
usort($ordered, array('FurmediaScheduledPopupEngine', 'sortByPriority'));
check($ordered[0]['priority'] === 20, 'higher priority sorts first');

echo "Engine tests passed: " . $passed . PHP_EOL;
