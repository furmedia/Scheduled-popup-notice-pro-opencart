<?php
/**
 * Shared, framework-neutral scheduling engine for Scheduled Popup Notice Pro.
 * Kept compatible with PHP 5.6 so the same source can serve OpenCart 2.x-4.x.
 */
class FurmediaScheduledPopupEngine {
    const VERSION = '2.0.2';

    public static function defaultContent() {
        return array(
            'title' => 'Important announcement',
            'message' => 'Our schedule changes between {start_date} and {end_date}.',
            'submessage' => 'Normal service resumes after {end_date}.',
            'thanks' => 'Thank you for your understanding!',
            'email_message' => 'Important: our schedule changes between {start_date} and {end_date}.',
            'button_text' => 'Learn more',
            'countdown_label' => 'Time remaining'
        );
    }

    public static function defaultCampaign($language_ids) {
        $content = array();
        foreach ((array)$language_ids as $language_id) {
            $content[(string)(int)$language_id] = self::defaultContent();
        }

        return array(
            'id' => self::newId(),
            'name' => 'First campaign',
            'status' => 0,
            'priority' => 10,
            'timezone' => 'UTC',
            'starts_at' => date('Y-m-d') . ' 00:00:00',
            'ends_at' => date('Y-m-d', strtotime('+1 day')) . ' 00:00:00',
            'recurrence' => 'none',
            'recurrence_until' => '',
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
            'button_url' => '',
            'button_target' => '_self',
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

    public static function decodeCampaigns($json, $language_ids, $legacy) {
        $campaigns = json_decode((string)$json, true);
        if (!is_array($campaigns) || !$campaigns) {
            $campaigns = array(self::fromLegacy($language_ids, $legacy));
        }

        $normalized = array();
        foreach ($campaigns as $campaign) {
            if (is_array($campaign)) {
                $normalized[] = self::normalizeCampaign($campaign, $language_ids);
            }
        }

        if (!$normalized) {
            $normalized[] = self::defaultCampaign($language_ids);
        }

        usort($normalized, array(__CLASS__, 'sortByPriority'));
        return $normalized;
    }

    public static function encodeCampaigns($campaigns) {
        return json_encode(array_values((array)$campaigns), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public static function fromLegacy($language_ids, $legacy) {
        $campaign = self::defaultCampaign($language_ids);
        $campaign['name'] = 'Imported campaign';
        $campaign['status'] = !empty($legacy['status']) ? 1 : 0;
        foreach (array('timezone', 'starts_at', 'ends_at') as $key) {
            if (isset($legacy[$key]) && $legacy[$key] !== '') {
                $campaign[$key] = (string)$legacy[$key];
            }
        }

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

    public static function normalizeCampaign($campaign, $language_ids) {
        $defaults = self::defaultCampaign($language_ids);
        $result = array_merge($defaults, (array)$campaign);

        $result['id'] = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$result['id']);
        if ($result['id'] === '') {
            $result['id'] = self::newId();
        }
        $result['name'] = self::plain($result['name'], 120);
        $result['status'] = !empty($result['status']) ? 1 : 0;
        $result['priority'] = max(-9999, min(9999, (int)$result['priority']));
        $result['timezone'] = (string)$result['timezone'];
        $result['starts_at'] = self::dateTime($result['starts_at']);
        $result['ends_at'] = self::dateTime($result['ends_at']);
        $result['recurrence'] = in_array($result['recurrence'], array('none', 'weekly', 'monthly'), true) ? $result['recurrence'] : 'none';
        $result['recurrence_until'] = $result['recurrence_until'] ? self::dateTime($result['recurrence_until']) : '';
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
            $content = array_merge(self::defaultContent(), $content);
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

    private static function dateTime($value) {
        $value = trim((string)$value);
        return preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value) ? $value : '';
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

    private static function plain($value, $length) {
        $value = html_entity_decode(strip_tags((string)$value), ENT_QUOTES, 'UTF-8');
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);
        return function_exists('mb_substr') ? mb_substr(trim($value), 0, $length, 'UTF-8') : substr(trim($value), 0, $length);
    }
}
