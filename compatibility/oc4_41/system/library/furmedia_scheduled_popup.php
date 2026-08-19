<?php
/**
 * Shared, framework-neutral scheduling engine for Scheduled Popup Notice Pro.
 * Kept compatible with PHP 5.6 so the same source can serve OpenCart 2.x-4.x.
 */
class FurmediaScheduledPopupEngine {
    const VERSION = '2.0.7';

    public static function defaultContent($language_code = 'en') {
        $templates = array(
            'en' => array(
                'title' => 'Important announcement',
                'message' => 'No deliveries are made during the period {start_date}, {start_time} - {end_date}, {end_time}.',
                'submessage' => 'Deliveries resume on {end_date} at {end_time}.',
                'thanks' => 'Thank you for your understanding!',
                'email_message' => 'Important: no deliveries are made from {start_date}, {start_time} until {end_date}, {end_time}. Deliveries resume on {end_date} at {end_time}.',
                'button_text' => 'Learn more',
                'countdown_label' => 'Time remaining'
            ),
            'ro' => array(
                'title' => 'Anunț important',
                'message' => 'La {store_name}, nu se fac livrări în perioada {start_date}, ora {start_time} - {end_date}, ora {end_time}.',
                'submessage' => 'Livrările se reîncep pe {end_date}, ora {end_time}.',
                'thanks' => 'Vă mulțumim pentru înțelegere!',
                'email_message' => 'Important: La {store_name}, în perioada {start_date}, ora {start_time} până la {end_date}, ora {end_time}, nu se fac livrări. Livrările se reiau pe {end_date}, ora {end_time}.',
                'button_text' => 'Află mai multe',
                'countdown_label' => 'Timp rămas'
            ),
            'de' => array(
                'title' => 'Wichtige Ankündigung',
                'message' => '{store_name}: geänderter Zeitplan vom {start_date}, {start_time} bis {end_date}, {end_time}.',
                'submessage' => 'Nach dem {end_date} gilt wieder der normale Zeitplan. Noch {days_remaining} Tag(e).',
                'thanks' => 'Vielen Dank für Ihr Verständnis!',
                'email_message' => 'Wichtig: Bei {store_name} gilt vom {start_date}, {start_time} bis {end_date}, {end_time} ein geänderter Zeitplan. Danach gilt wieder der normale Zeitplan.',
                'button_text' => 'Mehr erfahren',
                'countdown_label' => 'Verbleibende Zeit'
            ),
            'fr' => array(
                'title' => 'Annonce importante',
                'message' => '{store_name} : horaires modifiés du {start_date} à {start_time} au {end_date} à {end_time}.',
                'submessage' => 'Le service normal reprend après le {end_date}. Il reste {days_remaining} jour(s).',
                'thanks' => 'Merci de votre compréhension !',
                'email_message' => 'Important : {store_name} applique des horaires modifiés du {start_date} à {start_time} au {end_date} à {end_time}. Le service normal reprend ensuite.',
                'button_text' => 'En savoir plus',
                'countdown_label' => 'Temps restant'
            ),
            'es' => array(
                'title' => 'Aviso importante',
                'message' => '{store_name}: horario modificado desde {start_date}, {start_time}, hasta {end_date}, {end_time}.',
                'submessage' => 'El servicio normal se reanuda después del {end_date}. Quedan {days_remaining} día(s).',
                'thanks' => '¡Gracias por su comprensión!',
                'email_message' => 'Importante: {store_name} tendrá un horario modificado desde {start_date}, {start_time}, hasta {end_date}, {end_time}. Después se reanudará el servicio normal.',
                'button_text' => 'Más información',
                'countdown_label' => 'Tiempo restante'
            ),
            'it' => array(
                'title' => 'Avviso importante',
                'message' => '{store_name}: orario modificato dal {start_date}, {start_time}, al {end_date}, {end_time}.',
                'submessage' => 'Il servizio normale riprende dopo il {end_date}. Mancano {days_remaining} giorno/i.',
                'thanks' => 'Grazie per la comprensione!',
                'email_message' => 'Importante: {store_name} avrà un orario modificato dal {start_date}, {start_time}, al {end_date}, {end_time}. In seguito riprenderà il servizio normale.',
                'button_text' => 'Scopri di più',
                'countdown_label' => 'Tempo rimanente'
            ),
            'pt' => array(
                'title' => 'Aviso importante',
                'message' => '{store_name}: horário alterado de {start_date}, {start_time}, até {end_date}, {end_time}.',
                'submessage' => 'O serviço normal recomeça após {end_date}. Faltam {days_remaining} dia(s).',
                'thanks' => 'Agradecemos a sua compreensão!',
                'email_message' => 'Importante: {store_name} terá um horário alterado de {start_date}, {start_time}, até {end_date}, {end_time}. Depois, o serviço normal será retomado.',
                'button_text' => 'Saiba mais',
                'countdown_label' => 'Tempo restante'
            )
        );

        $prefix = self::languagePrefix($language_code);
        return isset($templates[$prefix]) ? $templates[$prefix] : $templates['en'];
    }

    public static function defaultCampaign($language_ids, $language_codes = array()) {
        $content = array();
        foreach ((array)$language_ids as $language_id) {
            $content[(string)(int)$language_id] = self::defaultContent(self::languageCode($language_id, $language_codes));
        }

        return array(
            'id' => self::newId(),
            'name' => 'First campaign',
            'status' => 0,
            'email_enabled' => 1,
            'priority' => 10,
            'timezone' => 'UTC',
            'starts_at' => date('Y-m-d') . ' 00:00:00',
            'starts_at_time' => '',
            'ends_at' => date('Y-m-d', strtotime('+1 day')) . ' 00:00:00',
            'ends_at_time' => '',
            'recurrence' => 'none',
            'recurrence_until' => '',
            'recurrence_until_time' => '',
            'date_format' => 'd.m.Y',
            'time_format' => 'H:i',
            'image' => '',
            'remove_image' => 0,
            'preset' => 'elegant',
            'accent_color' => '#713568',
            'background_color' => '#fffafc',
            'text_color' => '#2f2934',
            'button_color' => '#e21d2f',
            'overlay_color' => '#25182a',
            'overlay_opacity' => 42,
            'blur' => 3,
            'countdown' => 0,
            'trigger' => 'on_load',
            'trigger_delay' => 0,
            'trigger_selector' => '',
            'trigger_scroll_depth' => 55,
            'auto_close_seconds' => 0,
            'position' => 'center',
            'popup_width_desktop' => 520,
            'popup_width_laptop' => 520,
            'popup_width_tablet' => 480,
            'popup_width_mobile' => 360,
            'popup_height_desktop' => 90,
            'popup_height_laptop' => 90,
            'popup_height_tablet' => 92,
            'popup_height_mobile' => 94,
            'breakpoint_mobile' => 767,
            'breakpoint_tablet' => 1024,
            'breakpoint_laptop' => 1440,
            'button_url' => '',
            'button_target' => '_self',
            'devices' => array('desktop', 'tablet', 'mobile'),
            'geo_countries' => array(),
            'target_type' => 'all',
            'target_categories' => array(),
            'target_products' => array(),
            'target_product_labels' => array(),
            'content' => $content
        );
    }

    public static function newId() {
        return 'spn_' . str_replace('.', '', uniqid('', true));
    }

    public static function decodeCampaigns($json, $language_ids, $legacy, $language_codes = array()) {
        $campaigns = json_decode((string)$json, true);
        if (!is_array($campaigns) || !$campaigns) {
            $campaigns = array(self::fromLegacy($language_ids, $legacy, $language_codes));
        }

        $normalized = array();
        foreach ($campaigns as $campaign) {
            if (is_array($campaign)) {
                $normalized[] = self::normalizeCampaign($campaign, $language_ids, $language_codes);
            }
        }

        if (!$normalized) {
            $normalized[] = self::defaultCampaign($language_ids, $language_codes);
        }

        usort($normalized, array(__CLASS__, 'sortByPriority'));
        return $normalized;
    }

    public static function encodeCampaigns($campaigns) {
        return json_encode(array_values((array)$campaigns), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public static function fromLegacy($language_ids, $legacy, $language_codes = array()) {
        $campaign = self::defaultCampaign($language_ids, $language_codes);
        $campaign['name'] = 'Imported campaign';
        $campaign['status'] = !empty($legacy['status']) ? 1 : 0;
        foreach (array('timezone', 'starts_at', 'ends_at') as $key) {
            if (isset($legacy[$key]) && $legacy[$key] !== '') {
                $campaign[$key] = (string)$legacy[$key];
            }
        }
        $campaign['starts_at_time'] = self::extractTime($campaign['starts_at']);
        $campaign['ends_at_time'] = self::extractTime($campaign['ends_at']);

        $mapping = array(
            'banner_title' => 'title',
            'banner_message' => 'message',
            'banner_submessage' => 'submessage',
            'email_message' => 'email_message'
        );
        foreach ((array)$language_ids as $language_id) {
            foreach ($mapping as $legacy_key => $content_key) {
                if (isset($legacy[$legacy_key]) && $legacy[$legacy_key] !== '') {
                    $campaign['content'][(string)(int)$language_id][$content_key] = (string)$legacy[$legacy_key];
                }
            }
        }
        return $campaign;
    }

    public static function normalizeCampaign($campaign, $language_ids, $language_codes = array()) {
        $campaign = (array)$campaign;
        $has_starts_at_time = array_key_exists('starts_at_time', $campaign);
        $has_ends_at_time = array_key_exists('ends_at_time', $campaign);
        $has_recurrence_until_time = array_key_exists('recurrence_until_time', $campaign);
        $defaults = self::defaultCampaign($language_ids, $language_codes);
        $result = array_merge($defaults, $campaign);

        $result['id'] = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$result['id']);
        if ($result['id'] === '') {
            $result['id'] = self::newId();
        }
        $result['name'] = self::plain($result['name'], 120);
        $result['status'] = !empty($result['status']) ? 1 : 0;
        $result['email_enabled'] = !empty($result['email_enabled']) ? 1 : 0;
        $result['priority'] = max(-9999, min(9999, (int)$result['priority']));
        $result['timezone'] = (string)$result['timezone'];
        $result['starts_at_time'] = $has_starts_at_time ? self::optionalTime($result['starts_at_time']) : self::extractTime($result['starts_at']);
        $result['ends_at_time'] = $has_ends_at_time ? self::optionalTime($result['ends_at_time']) : self::extractTime($result['ends_at']);
        $result['recurrence_until_time'] = $has_recurrence_until_time ? self::optionalTime($result['recurrence_until_time']) : self::extractTime($result['recurrence_until']);
        $result['starts_at'] = self::dateTimeWithOptionalTime($result['starts_at'], $result['starts_at_time'], false);
        $result['ends_at'] = self::dateTimeWithOptionalTime($result['ends_at'], $result['ends_at_time'], true);
        $result['recurrence'] = in_array($result['recurrence'], array('none', 'weekly', 'monthly', 'yearly'), true) ? $result['recurrence'] : 'none';
        $result['trigger'] = in_array($result['trigger'], array('on_load', 'on_delay', 'on_click', 'on_scroll', 'on_exit', 'on_inactivity', 'on_adblock'), true) ? $result['trigger'] : 'on_load';
        $result['trigger_delay'] = max(0, min(900, (int)$result['trigger_delay']));
        $result['trigger_selector'] = self::plain($result['trigger_selector'], 220);
        $result['trigger_scroll_depth'] = max(1, min(100, (int)$result['trigger_scroll_depth']));
        $result['auto_close_seconds'] = max(0, min(900, (int)$result['auto_close_seconds']));
        $result['position'] = in_array($result['position'], array('center', 'top_left', 'top_center', 'top_right', 'middle_left', 'middle_center', 'middle_right', 'bottom_left', 'bottom_center', 'bottom_right'), true) ? $result['position'] : 'center';
        $result['popup_width_desktop'] = max(280, min(1600, (int)$result['popup_width_desktop']));
        $result['popup_width_laptop'] = max(280, min(1400, (int)$result['popup_width_laptop']));
        $result['popup_width_tablet'] = max(260, min(1200, (int)$result['popup_width_tablet']));
        $result['popup_width_mobile'] = max(240, min(800, (int)$result['popup_width_mobile']));
        foreach (array('desktop', 'laptop', 'tablet', 'mobile') as $profile) {
            $result['popup_height_' . $profile] = max(45, min(98, (int)$result['popup_height_' . $profile]));
        }
        $result['breakpoint_mobile'] = max(320, min(900, (int)$result['breakpoint_mobile']));
        $result['breakpoint_tablet'] = max($result['breakpoint_mobile'] + 1, min(1400, (int)$result['breakpoint_tablet']));
        $result['breakpoint_laptop'] = max($result['breakpoint_tablet'] + 1, min(2560, (int)$result['breakpoint_laptop']));
        $result['devices'] = self::devices($result['devices']);
        $result['geo_countries'] = self::countries($result['geo_countries']);
        $result['recurrence_until'] = $result['recurrence_until'] ? self::dateTimeWithOptionalTime($result['recurrence_until'], $result['recurrence_until_time'], true) : '';
        if ($result['recurrence_until'] === '') {
            $result['recurrence_until_time'] = '';
        }
        $result['date_format'] = self::format((string)$result['date_format'], 'd.m.Y');
        $result['time_format'] = self::format((string)$result['time_format'], 'H:i');
        $result['image'] = self::imagePath($result['image']);
        $result['remove_image'] = !empty($result['remove_image']) ? 1 : 0;
        $result['preset'] = in_array($result['preset'], array('elegant', 'minimal', 'bold'), true) ? $result['preset'] : 'elegant';
        foreach (array('accent_color', 'background_color', 'text_color', 'button_color', 'overlay_color') as $color) {
            $result[$color] = self::color($result[$color], $defaults[$color]);
        }
        $result['overlay_opacity'] = max(0, min(90, (int)$result['overlay_opacity']));
        $result['blur'] = max(0, min(12, (int)$result['blur']));
        $result['countdown'] = !empty($result['countdown']) ? 1 : 0;
        $result['button_url'] = self::url($result['button_url']);
        $result['button_target'] = $result['button_target'] === '_blank' ? '_blank' : '_self';
        $result['target_type'] = in_array($result['target_type'], array('all', 'categories', 'products'), true) ? $result['target_type'] : 'all';
        $result['target_categories'] = self::ids($result['target_categories']);
        $result['target_products'] = self::ids($result['target_products']);
        $result['target_product_labels'] = self::labels($result['target_product_labels'], $result['target_products']);

        $incoming = isset($campaign['content']) && is_array($campaign['content']) ? $campaign['content'] : array();
        $result['content'] = array();
        foreach ((array)$language_ids as $language_id) {
            $key = (string)(int)$language_id;
            $content = isset($incoming[$key]) && is_array($incoming[$key]) ? $incoming[$key] : array();
            $language_code = self::languageCode($language_id, $language_codes);
            $localized_defaults = self::defaultContent($language_code);
            if (self::languagePrefix($language_code) !== 'en' && self::isEnglishPlaceholder($content)) {
                $content = array();
            }
            $content = array_merge($localized_defaults, $content);
            $result['content'][$key] = array(
                'title' => self::plain($content['title'], 180),
                'message' => self::plain($content['message'], 1000),
                'submessage' => self::plain($content['submessage'], 1000),
                'thanks' => self::plain($content['thanks'], 500),
                'email_message' => self::plain($content['email_message'], 3000),
                'button_text' => self::plain($content['button_text'], 120),
                'countdown_label' => self::plain($content['countdown_label'], 120)
            );
        }

        return $result;
    }

    public static function activeOccurrence($campaign, $now) {
        if (empty($campaign['status'])) {
            return false;
        }

        try {
            $timezone = new DateTimeZone($campaign['timezone']);
            $current = $now instanceof DateTime ? clone $now : new DateTime((string)$now, $timezone);
            $current->setTimezone($timezone);
            $first_start = new DateTime($campaign['starts_at'], $timezone);
            $first_end = new DateTime($campaign['ends_at'], $timezone);
        } catch (Exception $e) {
            return false;
        }

        if ($first_end <= $first_start || $current < $first_start) {
            return false;
        }
        if (!empty($campaign['recurrence_until'])) {
            try {
                $until = new DateTime($campaign['recurrence_until'], $timezone);
                if ($current >= $until) {
                    return false;
                }
            } catch (Exception $e) {
                return false;
            }
        }

        $recurrence = isset($campaign['recurrence']) ? $campaign['recurrence'] : 'none';
        if ($recurrence === 'none') {
            return ($current >= $first_start && $current < $first_end) ? self::occurrence($first_start, $first_end) : false;
        }

        $duration = $first_end->getTimestamp() - $first_start->getTimestamp();
        if ($recurrence === 'weekly') {
            $elapsed_days = (int)$first_start->diff($current)->format('%a');
            $steps = max(0, (int)floor($elapsed_days / 7));
            $start = clone $first_start;
            $start->modify('+' . ($steps * 7) . ' days');
        } elseif ($recurrence === 'yearly') {
            $years = (int)$current->format('Y') - (int)$first_start->format('Y');
            $start = self::yearlyDate($first_start, max(0, $years));
            if ($start > $current && $years > 0) {
                $start = self::yearlyDate($first_start, $years - 1);
            }
        } else {
            $months = ((int)$current->format('Y') - (int)$first_start->format('Y')) * 12 + ((int)$current->format('n') - (int)$first_start->format('n'));
            $start = self::monthlyDate($first_start, max(0, $months));
            if ($start > $current && $months > 0) {
                $start = self::monthlyDate($first_start, $months - 1);
            }
        }
        $end = clone $start;
        $end->setTimestamp($start->getTimestamp() + $duration);

        return ($current >= $start && $current < $end) ? self::occurrence($start, $end) : false;
    }

    public static function replaceShortcodes($text, $campaign, $occurrence, $store_name, $now) {
        if (!$occurrence) {
            return (string)$text;
        }
        $start = $occurrence['start'];
        $end = $occurrence['end'];
        $current = $now instanceof DateTime ? clone $now : new DateTime((string)$now, $start->getTimezone());
        $current->setTimezone($start->getTimezone());
        $seconds = max(0, $end->getTimestamp() - $current->getTimestamp());
        $date_format = !empty($campaign['date_format']) ? $campaign['date_format'] : 'd.m.Y';
        $time_format = !empty($campaign['time_format']) ? $campaign['time_format'] : 'H:i';
        $values = array(
            'start_date' => $start->format($date_format),
            'start_time' => $start->format($time_format),
            'end_date' => $end->format($date_format),
            'end_time' => $end->format($time_format),
            'days_remaining' => (string)(int)ceil($seconds / 86400),
            'hours_remaining' => (string)(int)ceil($seconds / 3600),
            'countdown' => self::countdown($seconds),
            'store_name' => (string)$store_name,
            'campaign_name' => isset($campaign['name']) ? (string)$campaign['name'] : '',
            'year' => $current->format('Y')
        );
        foreach ($values as $key => $value) {
            $text = str_replace(array('{' . $key . '}', '[' . $key . ']'), $value, (string)$text);
        }
        return $text;
    }

    public static function sortByPriority($a, $b) {
        $priority_a = isset($a['priority']) ? (int)$a['priority'] : 0;
        $priority_b = isset($b['priority']) ? (int)$b['priority'] : 0;
        if ($priority_a === $priority_b) {
            return strcmp(isset($a['name']) ? $a['name'] : '', isset($b['name']) ? $b['name'] : '');
        }
        return $priority_a > $priority_b ? -1 : 1;
    }

    private static function occurrence($start, $end) {
        return array(
            'start' => $start,
            'end' => $end,
            'key' => $start->format('YmdHis')
        );
    }

    private static function yearlyDate($first, $years) {
        $year = (int)$first->format('Y') + (int)$years;
        $month = (int)$first->format('n');
        $month_start = new DateTime(sprintf('%04d-%02d-01 00:00:00', $year, $month), $first->getTimezone());
        $day = min((int)$first->format('j'), (int)$month_start->format('t'));
        $value = sprintf('%04d-%02d-%02d %s', $year, $month, $day, $first->format('H:i:s'));
        return new DateTime($value, $first->getTimezone());
    }

    private static function monthlyDate($first, $months) {
        $year = (int)$first->format('Y');
        $month = (int)$first->format('n') + (int)$months;
        $year += (int)floor(($month - 1) / 12);
        $month = (($month - 1) % 12) + 1;
        $month_start = new DateTime(sprintf('%04d-%02d-01 00:00:00', $year, $month), $first->getTimezone());
        $day = min((int)$first->format('j'), (int)$month_start->format('t'));
        $value = sprintf('%04d-%02d-%02d %s', $year, $month, $day, $first->format('H:i:s'));
        return new DateTime($value, $first->getTimezone());
    }

    private static function countdown($seconds) {
        $days = (int)floor($seconds / 86400);
        $hours = (int)floor(($seconds % 86400) / 3600);
        $minutes = (int)floor(($seconds % 3600) / 60);
        return sprintf('%02d:%02d:%02d', $days, $hours, $minutes);
    }

    private static function optionalTime($value) {
        $value = trim((string)$value);
        return preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $value) ? $value : '';
    }

    private static function extractTime($value) {
        $value = trim((string)$value);
        return preg_match('/^\d{4}-\d{2}-\d{2}[ T]((?:[01]\d|2[0-3]):[0-5]\d)(?::[0-5]\d)?$/', $value, $matches) ? $matches[1] : '';
    }

    private static function dateTimeWithOptionalTime($value, $time, $end_of_day) {
        $value = trim((string)$value);
        if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})(?:[ T]\d{2}:\d{2}(?::\d{2})?)?$/', $value, $matches)) {
            return '';
        }
        if (!checkdate((int)$matches[2], (int)$matches[3], (int)$matches[1])) {
            return '';
        }
        $time = self::optionalTime($time);
        if ($time !== '') {
            return $matches[1] . '-' . $matches[2] . '-' . $matches[3] . ' ' . $time . ':00';
        }
        return $matches[1] . '-' . $matches[2] . '-' . $matches[3] . ($end_of_day ? ' 23:59:59' : ' 00:00:00');
    }

    private static function format($value, $fallback) {
        $value = preg_replace('/[^dDjlNSwzWFmMntLoYyaABgGhHisueIOPTZcrU:\/\.\- _]/', '', $value);
        return $value !== '' ? substr($value, 0, 40) : $fallback;
    }

    private static function color($value, $fallback) {
        $value = trim((string)$value);
        return preg_match('/^#[0-9a-fA-F]{6}$/', $value) ? strtolower($value) : $fallback;
    }

    private static function imagePath($value) {
        $value = str_replace('\\', '/', trim((string)$value));
        $value = preg_replace('#\.\./|\./#', '', $value);
        return preg_match('#^[a-zA-Z0-9_\-/]+\.(jpg|jpeg|png|webp)$#i', $value) ? $value : '';
    }

    private static function url($value) {
        $value = trim((string)$value);
        if ($value === '') {
            return '';
        }
        if (strpos($value, '/') === 0 || strpos($value, '#') === 0 || strpos($value, '?') === 0) {
            return substr($value, 0, 1000);
        }
        return filter_var($value, FILTER_VALIDATE_URL) ? substr($value, 0, 1000) : '';
    }

    private static function ids($values) {
        $ids = array();
        foreach ((array)$values as $value) {
            $value = (int)$value;
            if ($value > 0) {
                $ids[$value] = $value;
            }
        }
        return array_values($ids);
    }

    private static function labels($labels, $allowed_ids) {
        $result = array();
        $allowed = array_flip(self::ids($allowed_ids));
        foreach ((array)$labels as $id => $label) {
            $id = (int)$id;
            if (isset($allowed[$id])) {
                $result[(string)$id] = self::plain($label, 200);
            }
        }
        return $result;
    }

    private static function languageCode($language_id, $language_codes) {
        $numeric_key = (int)$language_id;
        $string_key = (string)$numeric_key;
        if (isset($language_codes[$numeric_key])) {
            return (string)$language_codes[$numeric_key];
        }
        if (isset($language_codes[$string_key])) {
            return (string)$language_codes[$string_key];
        }
        return 'en';
    }

    private static function languagePrefix($language_code) {
        $language_code = strtolower(str_replace('_', '-', trim((string)$language_code)));
        $parts = explode('-', $language_code);
        return $parts[0] !== '' ? $parts[0] : 'en';
    }

    private static function isEnglishPlaceholder($content) {
        if (!$content) {
            return false;
        }

        $templates = array(
            array(
                'title' => 'Shipping notice',
                'message' => 'No deliveries are made between {start_date}, {start_time} and {end_date}, {end_time}.',
                'submessage' => 'Deliveries resume on {end_date} at {end_time}.',
                'thanks' => 'Thank you for your understanding!',
                'email_message' => 'Important: no deliveries are made between {start_date}, {start_time} and {end_date}, {end_time}. Deliveries resume on {end_date} at {end_time}.',
                'button_text' => 'Learn more',
                'countdown_label' => 'Time remaining'
            ),
            array(
                'title' => 'Shipping notice',
                'message' => 'No deliveries are made between {start_date}, {start_time} and {end_date}, {end_time}.',
                'submessage' => 'Deliveries resume on {end_date} at {end_time}.',
                'thanks' => 'Thank you for your understanding!',
                'email_message' => 'Important: no deliveries are made between {start_date}, {start_time} and {end_date}, {end_time}. Deliveries resume on {end_date} at {end_time}.',
                'button_text' => 'Learn more',
                'countdown_label' => 'Time remaining'
            ),
            array(
                'title' => 'Important announcement',
                'message' => 'Use this space for your scheduled announcement.',
                'submessage' => 'The popup automatically disappears after the end date.',
                'thanks' => 'Thank you for your understanding!',
                'email_message' => 'This message is added to order confirmation emails while the schedule is active.',
                'button_text' => 'Learn more',
                'countdown_label' => 'Time remaining'
            ),
            self::defaultContent('en')
        );

        foreach ($templates as $template) {
            $matches = true;
            foreach ($template as $key => $value) {
                if (isset($content[$key]) && (string)$content[$key] !== (string)$value) {
                    $matches = false;
                    break;
                }
            }
            if ($matches) {
                return true;
            }
        }
        return false;
    }

    private static function plain($value, $length) {
        $value = html_entity_decode(strip_tags((string)$value), ENT_QUOTES, 'UTF-8');
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);
        return function_exists('mb_substr') ? mb_substr(trim($value), 0, $length, 'UTF-8') : substr(trim($value), 0, $length);
    }

    private static function devices($values) {
        $allowed = array('desktop', 'tablet', 'mobile');
        $items = is_array($values) ? $values : preg_split('/[,\s;]+/', trim((string)$values));
        $result = array();
        foreach ((array)$items as $value) {
            $value = strtolower(trim((string)$value));
            if (in_array($value, $allowed, true)) {
                $result[$value] = $value;
            }
        }
        return array_values($result);
    }

    private static function countries($values) {
        if (is_array($values)) {
            $items = $values;
        } elseif (is_string($values)) {
            $items = preg_split('/[,\s;]+/', trim($values));
        } else {
            return array();
        }

        $result = array();
        foreach ($items as $value) {
            $code = strtoupper(preg_replace('/[^A-Za-z]/', '', (string)$value));
            if (strlen($code) === 2) {
                $result[$code] = $code;
            }
        }
        return array_values($result);
    }
}

