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
