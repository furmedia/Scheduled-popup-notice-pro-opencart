<?php
namespace Opencart\Admin\Controller\Extension\FurmediaScheduledPopup\Module;

class ScheduledPopup extends \Opencart\System\Engine\Controller {
    private array $error = [];

    public function index(): void {
        $this->loadEngine();
        $this->load->language('extension/furmedia_scheduled_popup/module/scheduled_popup');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');
        $languages = $this->getLanguages();
        $language_ids = array_column($languages, 'language_id');
        $language_codes = $this->languageCodes($languages);
        $campaigns = $this->campaigns($language_ids, $language_codes);

        if ($this->request->server['REQUEST_METHOD'] === 'POST') {
            $campaigns = \FurmediaScheduledPopupEngine::decodeCampaigns(
                html_entity_decode((string)($this->request->post['module_cristale_shipping_notice_campaigns_json'] ?? ''), ENT_QUOTES, 'UTF-8'),
                $language_ids,
                [],
                $language_codes
            );
            $campaigns = $this->processUploads($campaigns);
            if ($this->validate($campaigns, $language_ids)) {
                $this->model_setting_setting->editSetting('module_cristale_shipping_notice', [
                    'module_cristale_shipping_notice_status' => !empty($this->request->post['module_cristale_shipping_notice_status']) ? 1 : 0,
                    'module_cristale_shipping_notice_campaigns_json' => \FurmediaScheduledPopupEngine::encodeCampaigns($campaigns)
                ]);
                $this->createStatsTable();
                $this->syncEvents();
                $this->session->data['success'] = $this->language->get('text_success');
                $this->response->redirect($this->url->link('extension/furmedia_scheduled_popup/module/scheduled_popup', 'user_token=' . $this->session->data['user_token'], true));
                return;
            }
        }

        $data = $this->languageData();
        $data['error_warning'] = $this->error['warning'] ?? ($this->session->data['error_warning'] ?? '');
        unset($this->session->data['error_warning']);
        $data['success'] = $this->session->data['success'] ?? '';
        unset($this->session->data['success']);
        $token = 'user_token=' . $this->session->data['user_token'];
        $route = 'extension/furmedia_scheduled_popup/module/scheduled_popup';
        $data['breadcrumbs'] = [
            ['text' => $this->language->get('text_home'), 'href' => $this->url->link('common/dashboard', $token, true)],
            ['text' => $this->language->get('text_extension'), 'href' => $this->url->link('marketplace/extension', $token . '&type=module', true)],
            ['text' => $this->language->get('heading_title'), 'href' => $this->url->link($route, $token, true)]
        ];
        $data['action'] = $this->url->link($route, $token, true);
        $data['cancel'] = $this->url->link('marketplace/extension', $token . '&type=module', true);
        $data['clear_cache'] = $this->url->link($route . '.clearCache', $token, true);
        $data['autocomplete_url'] = $this->url->link($route . '.autocomplete', $token, true);
        $data['reset_stats_url'] = $this->url->link($route . '.resetStats', $token, true);
        $data['module_cristale_shipping_notice_status'] = isset($this->request->post['module_cristale_shipping_notice_status']) ? (int)$this->request->post['module_cristale_shipping_notice_status'] : (int)$this->config->get('module_cristale_shipping_notice_status');
        $data['campaigns_b64'] = $this->base64Json($campaigns);
        $data['languages_b64'] = $this->base64Json($languages);
        $data['categories_b64'] = $this->base64Json($this->getCategories());
        $data['timezones_b64'] = $this->base64Json($this->getTimezones());
        $data['stats_b64'] = $this->base64Json($this->getStats());
        $data['ui_b64'] = $this->base64Json($this->adminUi());
        $catalog = defined('HTTPS_CATALOG') ? HTTPS_CATALOG : HTTP_CATALOG;
        $data['catalog_image_url'] = $catalog . 'image/';
        $data['default_image_url'] = $catalog . 'extension/furmedia_scheduled_popup/catalog/view/image/shipping-notice-background.webp';
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('extension/furmedia_scheduled_popup/module/scheduled_popup', $data));
    }

    public function autocomplete(): void {
        $json = [];
        if ($this->user->hasPermission('access', 'extension/furmedia_scheduled_popup/module/scheduled_popup')) {
            $filter = trim((string)($this->request->get['filter_name'] ?? ''));
            $language_id = $this->storeLanguageId();
            $where = "pd.language_id = '" . $language_id . "'";
            $order = 'p.date_modified DESC, p.product_id DESC';
            if ($filter !== '') {
                $escaped = $this->db->escape($filter);
                $where .= " AND (pd.name LIKE '%" . $escaped . "%' OR p.model LIKE '%" . $escaped . "%' OR p.sku LIKE '%" . $escaped . "%'";
                if (ctype_digit($filter)) {
                    $where .= " OR p.product_id = '" . (int)$filter . "'";
                }
                $where .= ')';
                $order = "CASE WHEN pd.name = '" . $escaped . "' OR p.model = '" . $escaped . "' OR p.sku = '" . $escaped . "' THEN 0 ELSE 1 END, pd.name ASC";
            }
            $query = $this->db->query("SELECT p.product_id, pd.name, p.model, p.sku, p.quantity, p.status FROM `" . DB_PREFIX . "product` p LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id) WHERE " . $where . " ORDER BY " . $order . " LIMIT 30");
            foreach ($query->rows as $row) {
                $json[] = [
                    'product_id' => (int)$row['product_id'],
                    'name' => html_entity_decode($row['name'], ENT_QUOTES, 'UTF-8'),
                    'model' => $row['model'],
                    'sku' => $row['sku'],
                    'quantity' => (int)$row['quantity'],
                    'status' => (int)$row['status']
                ];
            }
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function resetStats(): void {
        $this->load->language('extension/furmedia_scheduled_popup/module/scheduled_popup');
        $this->createStatsTable();
        if (!$this->user->hasPermission('modify', 'extension/furmedia_scheduled_popup/module/scheduled_popup')) {
            $this->session->data['error_warning'] = $this->language->get('error_permission');
        } else {
            $campaign_id = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($this->request->get['campaign_id'] ?? ''));
            if ($campaign_id) {
                $this->db->query("DELETE FROM `" . DB_PREFIX . "cristale_shipping_notice_stat` WHERE campaign_id = '" . $this->db->escape($campaign_id) . "'");
            } else {
                $this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "cristale_shipping_notice_stat`");
            }
            $this->session->data['success'] = $this->language->get('text_stats_reset');
        }
        $this->response->redirect($this->url->link('extension/furmedia_scheduled_popup/module/scheduled_popup', 'user_token=' . $this->session->data['user_token'], true));
    }

    public function clearCache(): void {
        $this->load->language('extension/furmedia_scheduled_popup/module/scheduled_popup');
        if (!$this->user->hasPermission('modify', 'extension/furmedia_scheduled_popup/module/scheduled_popup')) {
            $this->session->data['error_warning'] = $this->language->get('error_permission');
        } elseif (defined('DIR_CACHE') && is_dir(DIR_CACHE)) {
            $this->clearCacheDirectory(DIR_CACHE);
            $this->session->data['success'] = sprintf($this->language->get('text_cache_success'), 0, 0, 0);
        }
        $this->response->redirect($this->url->link('extension/furmedia_scheduled_popup/module/scheduled_popup', 'user_token=' . $this->session->data['user_token'], true));
    }

    public function install(): void {
        $this->loadEngine();
        $this->load->model('setting/setting');
        $existing = $this->model_setting_setting->getSetting('module_cristale_shipping_notice');
        $existing['module_cristale_shipping_notice_status'] = (int)($existing['module_cristale_shipping_notice_status'] ?? 0);
        if (empty($existing['module_cristale_shipping_notice_campaigns_json'])) {
            $languages = $this->getLanguages();
            $legacy = $this->legacySettings();
            $existing['module_cristale_shipping_notice_campaigns_json'] = \FurmediaScheduledPopupEngine::encodeCampaigns([\FurmediaScheduledPopupEngine::fromLegacy(array_column($languages, 'language_id'), $legacy, $this->languageCodes($languages))]);
        }
        $this->model_setting_setting->editSetting('module_cristale_shipping_notice', $existing);
        $this->createStatsTable();
        $this->syncEvents();
    }

    public function uninstall(): void {
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('module_cristale_shipping_notice');
        $this->load->model('setting/event');
        $this->model_setting_event->deleteEventByCode('furmedia_scheduled_popup_mail');
        foreach ($this->events() as $event) {
            $this->model_setting_event->deleteEventByCode($event['code']);
        }
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "cristale_shipping_notice_stat`");
    }

    protected function validate(array $campaigns, array $language_ids): bool {
        if (!empty($this->error['warning'])) {
            return false;
        }
        if (!$this->user->hasPermission('modify', 'extension/furmedia_scheduled_popup/module/scheduled_popup')) {
            $this->error['warning'] = $this->language->get('error_permission');
            return false;
        }
        if (!$campaigns || count($campaigns) > 50) {
            $this->error['warning'] = $this->language->get('error_campaign_limit');
            return false;
        }
        $timezones = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL_WITH_BC);
        if (!in_array('UTC', $timezones, true)) $timezones[] = 'UTC';
        foreach ($campaigns as $index => $campaign) {
            $number = $index + 1;
            if (!$campaign['name']) return $this->campaignError('error_campaign_name', $number);
            if (!in_array($campaign['timezone'], $timezones, true)) return $this->campaignError('error_campaign_timezone', $number);
            $start = $this->parseDate($campaign['starts_at'], $campaign['timezone']);
            $end = $this->parseDate($campaign['ends_at'], $campaign['timezone']);
            if (!$start || !$end || $end <= $start) return $this->campaignError('error_campaign_dates', $number);
            if ($campaign['recurrence_until'] && !$this->parseDate($campaign['recurrence_until'], $campaign['timezone'])) return $this->campaignError('error_campaign_recurrence', $number);
            if ($campaign['target_type'] === 'categories' && !$campaign['target_categories']) return $this->campaignError('error_campaign_target', $number);
            if ($campaign['target_type'] === 'products' && !$campaign['target_products']) return $this->campaignError('error_campaign_target', $number);
            foreach ($language_ids as $language_id) {
                if (empty($campaign['content'][(string)$language_id]['title']) || empty($campaign['content'][(string)$language_id]['message'])) return $this->campaignError('error_campaign_content', $number);
            }
        }
        return empty($this->error['warning']);
    }

    private function campaignError(string $key, int $number): bool {
        $this->error['warning'] = sprintf($this->language->get($key), $number);
        return false;
    }

    private function campaigns(array $language_ids, array $language_codes = []): array {
        return \FurmediaScheduledPopupEngine::decodeCampaigns((string)$this->config->get('module_cristale_shipping_notice_campaigns_json'), $language_ids, $this->legacySettings(), $language_codes);
    }

    private function legacySettings(): array {
        $defaults = ['status' => 0, 'timezone' => 'UTC', 'starts_at' => '2026-01-01 00:00:00', 'ends_at' => '2026-01-02 00:00:00', 'banner_title' => 'Important announcement', 'banner_message' => 'Use this space for your scheduled announcement.', 'banner_submessage' => 'The popup automatically disappears after the end date.', 'email_message' => 'This message is added to order confirmation emails while the schedule is active.'];
        foreach ($defaults as $key => $default) {
            $defaults[$key] = $this->config->get('module_cristale_shipping_notice_' . $key) ?? $default;
        }
        return $defaults;
    }

    private function processUploads(array $campaigns): array {
        foreach ($campaigns as $index => $campaign) {
            if (!empty($campaign['remove_image'])) {
                $this->deleteImage($campaign['image']);
                $campaigns[$index]['image'] = '';
            }
            $key = 'campaign_image_' . $campaign['id'];
            if (!isset($this->request->files[$key]) || $this->request->files[$key]['error'] === UPLOAD_ERR_NO_FILE) {
                $campaigns[$index]['remove_image'] = 0;
                continue;
            }
            $file = $this->request->files[$key];
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $info = @getimagesize($file['tmp_name']);
            $mimes = ['image/jpeg' => ['jpg', 'jpeg'], 'image/png' => ['png'], 'image/webp' => ['webp']];
            if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 5242880 || !$info || !isset($mimes[$info['mime']]) || !in_array($extension, $mimes[$info['mime']], true) || $info[0] < 1 || $info[1] < 1 || $info[0] > 10000 || $info[1] > 10000 || ($info[0] * $info[1]) > 20000000) {
                $this->error['warning'] = $this->language->get('error_upload');
                continue;
            }
            $directory = rtrim(DIR_IMAGE, '/\\') . '/catalog/scheduled-popup-notice';
            if (!is_dir($directory) && !@mkdir($directory, 0755, true)) {
                $this->error['warning'] = $this->language->get('error_upload');
                continue;
            }
            $basename = $campaign['id'] . '-' . date('YmdHis') . '-' . mt_rand(1000, 9999);
            $filename = $this->storeImage($file['tmp_name'], $info['mime'], (int)$info[0], (int)$info[1], $directory, $basename, $extension);
            if ($filename === '') {
                $this->error['warning'] = $this->language->get('error_upload');
                continue;
            }
            $this->deleteImage($campaign['image']);
            $campaigns[$index]['image'] = 'catalog/scheduled-popup-notice/' . $filename;
            $campaigns[$index]['remove_image'] = 0;
        }
        return $campaigns;
    }

    private function storeImage(string $temporary, string $mime, int $width, int $height, string $directory, string $basename, string $fallback_extension): string {
        $webp_path = $directory . '/' . $basename . '.webp';
        $loaders = ['image/jpeg' => 'imagecreatefromjpeg', 'image/png' => 'imagecreatefrompng', 'image/webp' => 'imagecreatefromwebp'];
        $can_optimize = function_exists('imagewebp') && function_exists('imagecreatetruecolor') && isset($loaders[$mime]) && function_exists($loaders[$mime]);
        if ($can_optimize) {
            return $this->createOptimizedWebp($temporary, $mime, $width, $height, $webp_path) ? $basename . '.webp' : '';
        }

        $filename = $basename . '.' . $fallback_extension;
        return @move_uploaded_file($temporary, $directory . '/' . $filename) ? $filename : '';
    }

    private function createOptimizedWebp(string $source_path, string $mime, int $width, int $height, string $destination_path): bool {
        if (!function_exists('imagewebp') || !function_exists('imagecreatetruecolor')) {
            return false;
        }

        $loaders = [
            'image/jpeg' => 'imagecreatefromjpeg',
            'image/png' => 'imagecreatefrompng',
            'image/webp' => 'imagecreatefromwebp'
        ];
        if (!isset($loaders[$mime]) || !function_exists($loaders[$mime])) {
            return false;
        }
        if ($width < 1 || $height < 1 || $width > 10000 || $height > 10000 || ($width * $height) > 20000000) {
            return false;
        }

        $source = @call_user_func($loaders[$mime], $source_path);
        if (!$source) {
            return false;
        }

        $scale = min(1, 1280 / $width, 960 / $height);
        $target_width = max(1, (int)round($width * $scale));
        $target_height = max(1, (int)round($height * $scale));
        $target = @imagecreatetruecolor($target_width, $target_height);
        if (!$target) {
            @imagedestroy($source);
            return false;
        }

        @imagealphablending($target, false);
        @imagesavealpha($target, true);
        $transparent = @imagecolorallocatealpha($target, 0, 0, 0, 127);
        if ($transparent !== false) {
            @imagefilledrectangle($target, 0, 0, $target_width, $target_height, $transparent);
        }

        $copied = @imagecopyresampled($target, $source, 0, 0, 0, 0, $target_width, $target_height, $width, $height);
        $saved = $copied ? @imagewebp($target, $destination_path, 82) : false;
        @imagedestroy($target);
        @imagedestroy($source);

        if (!$saved || !is_file($destination_path)) {
            if (is_file($destination_path)) {
                @unlink($destination_path);
            }
            return false;
        }

        return true;
    }

    private function deleteImage(string $relative): void {
        if (!str_starts_with($relative, 'catalog/scheduled-popup-notice/')) return;
        $root = realpath(rtrim(DIR_IMAGE, '/\\') . '/catalog/scheduled-popup-notice');
        $file = realpath(rtrim(DIR_IMAGE, '/\\') . '/' . $relative);
        if ($root && $file && str_starts_with($file, $root . DIRECTORY_SEPARATOR) && is_file($file)) @unlink($file);
    }

    private function getLanguages(): array {
        return array_map(static fn(array $row): array => ['language_id' => (int)$row['language_id'], 'name' => $row['name'], 'code' => $row['code']], $this->db->query("SELECT language_id, name, code FROM `" . DB_PREFIX . "language` WHERE status = '1' ORDER BY sort_order, name")->rows);
    }

    private function languageCodes(array $languages): array {
        $codes = [];
        foreach ($languages as $language) {
            $codes[(int)$language['language_id']] = (string)$language['code'];
        }
        return $codes;
    }

    private function storeLanguageId(): int {
        $language_id = (int)$this->config->get('config_language_id');
        if ($language_id > 0) {
            return $language_id;
        }

        $language_code = (string)$this->config->get('config_language');
        if ($language_code !== '') {
            $query = $this->db->query("SELECT language_id FROM `" . DB_PREFIX . "language` WHERE code = '" . $this->db->escape($language_code) . "' LIMIT 1");
            if (!empty($query->row['language_id'])) {
                return (int)$query->row['language_id'];
            }
        }

        $query = $this->db->query("SELECT language_id FROM `" . DB_PREFIX . "language` WHERE status = '1' ORDER BY sort_order, language_id LIMIT 1");
        return !empty($query->row['language_id']) ? (int)$query->row['language_id'] : 1;
    }

    private function getTimezones(): array {
        $identifiers = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL_WITH_BC);
        if (!in_array('UTC', $identifiers, true)) {
            array_unshift($identifiers, 'UTC');
        }

        $groups = [];
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        foreach ($identifiers as $identifier) {
            try {
                $timezone = new \DateTimeZone($identifier);
                $offset = $timezone->getOffset($now);
            } catch (\Throwable) {
                continue;
            }
            $sign = $offset < 0 ? '-' : '+';
            $absolute = abs($offset);
            $offset_label = sprintf('UTC%s%02d:%02d', $sign, floor($absolute / 3600), floor(($absolute % 3600) / 60));
            $parts = explode('/', $identifier, 2);
            $group = count($parts) > 1 ? $parts[0] : 'General';
            $groups[$group] ??= [];
            $groups[$group][] = ['value' => $identifier, 'label' => '(' . $offset_label . ') ' . str_replace('_', ' ', $identifier)];
        }
        ksort($groups, SORT_NATURAL | SORT_FLAG_CASE);
        $result = [];
        foreach ($groups as $label => $zones) {
            $result[] = ['label' => $label, 'zones' => $zones];
        }
        return $result;
    }

    private function getCategories(): array {
        $language_id = $this->storeLanguageId();
        return array_map(static fn(array $row): array => ['category_id' => (int)$row['category_id'], 'name' => html_entity_decode($row['name'], ENT_QUOTES, 'UTF-8')], $this->db->query("SELECT c.category_id, cd.name FROM `" . DB_PREFIX . "category` c LEFT JOIN `" . DB_PREFIX . "category_description` cd ON (c.category_id = cd.category_id) WHERE cd.language_id = '" . $language_id . "' ORDER BY cd.name")->rows);
    }

    private function createStatsTable(): void {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "cristale_shipping_notice_stat` (`campaign_id` varchar(64) NOT NULL, `event_type` varchar(16) NOT NULL, `stat_date` date NOT NULL, `total` int(10) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`campaign_id`,`event_type`,`stat_date`)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4");
    }

    private function getStats(): array {
        $this->createStatsTable();
        $stats = [];
        foreach ($this->db->query("SELECT campaign_id, event_type, SUM(total) AS total FROM `" . DB_PREFIX . "cristale_shipping_notice_stat` GROUP BY campaign_id, event_type")->rows as $row) {
            $stats[$row['campaign_id']] ??= ['impression' => 0, 'click' => 0, 'close' => 0];
            if (isset($stats[$row['campaign_id']][$row['event_type']])) $stats[$row['campaign_id']][$row['event_type']] = (int)$row['total'];
        }
        return $stats;
    }

    private function languageData(): array {
        $data = [];
        foreach (['heading_title', 'text_edit', 'text_enabled', 'text_disabled', 'text_signature', 'text_performance_tools', 'text_performance_note', 'button_save', 'button_cancel', 'button_clear_cache'] as $key) $data[$key] = $this->language->get($key);
        return $data;
    }

    private function adminUi(): array {
        $keys = ['text_add_campaign','text_duplicate','text_delete','text_campaign','text_schedule','text_content','text_design','text_targeting','text_statistics','entry_status','entry_campaign_name','entry_priority','entry_timezone','entry_starts_at','entry_ends_at','entry_recurrence','entry_recurrence_until','entry_date_format','entry_time_format','text_none','text_weekly','text_monthly','entry_title','entry_message','entry_submessage','entry_thanks','entry_email_message','entry_button_text','entry_button_url','entry_button_target','entry_countdown','entry_countdown_label','entry_image','text_remove_image','entry_preset','text_preset_elegant','text_preset_minimal','text_preset_bold','entry_accent_color','entry_background_color','entry_text_color','entry_button_color','entry_overlay_color','entry_overlay_opacity','entry_blur','entry_target_type','text_target_all','text_target_categories','text_target_products','entry_categories','entry_products','placeholder_product_search','text_impressions','text_clicks','text_closes','text_ctr','text_reset_stats','text_preview','text_shortcodes','text_shortcode_help','text_upload_help','text_no_campaigns','text_confirm_delete','text_new_campaign','text_open_same','text_open_new','text_apply_romanian_template','text_apply_english_template','text_template_help','text_product_loading','text_product_empty','text_product_error','text_product_suggestions','text_product_model','button_product_search'];
        $ui = [];
        foreach ($keys as $key) $ui[$key] = $this->language->get($key);
        $ui['shortcodes'] = ['{start_date}','{start_time}','{end_date}','{end_time}','{days_remaining}','{hours_remaining}','{countdown}','{store_name}','{campaign_name}','{year}'];
        $ui['content_templates'] = [
            'ro' => \FurmediaScheduledPopupEngine::defaultContent('ro'),
            'en' => \FurmediaScheduledPopupEngine::defaultContent('en')
        ];
        return $ui;
    }

    private function syncEvents(): void {
        $this->load->model('setting/event');
        $this->model_setting_event->deleteEventByCode('furmedia_scheduled_popup_mail');
        foreach ($this->events() as $event) {
            $this->model_setting_event->deleteEventByCode($event['code']);
            $this->model_setting_event->addEvent($event);
        }
    }

    private function events(): array {
        return [
            ['code'=>'furmedia_scheduled_popup_footer','description'=>'Scheduled Popup Notice Pro storefront popup','trigger'=>'catalog/view/common/footer/after','action'=>'extension/furmedia_scheduled_popup/module/scheduled_popup.footerAfter','status'=>1,'sort_order'=>0],
            ['code'=>'furmedia_scheduled_popup_mail_invoice','description'=>'Scheduled Popup Notice Pro order email message for OpenCart 4.0','trigger'=>'catalog/view/mail/order_invoice/after','action'=>'extension/furmedia_scheduled_popup/module/scheduled_popup.mailInvoiceAfter','status'=>1,'sort_order'=>0],
            ['code'=>'furmedia_scheduled_popup_mail_add','description'=>'Scheduled Popup Notice Pro order email message for OpenCart 4.1','trigger'=>'catalog/view/mail/order_add/after','action'=>'extension/furmedia_scheduled_popup/module/scheduled_popup.mailInvoiceAfter','status'=>1,'sort_order'=>0]
        ];
    }

    private function parseDate(string $value, string $timezone): \DateTime|false {
        try {$date = \DateTime::createFromFormat('Y-m-d H:i:s', $value, new \DateTimeZone($timezone));} catch (\Throwable) {return false;}
        $errors = \DateTime::getLastErrors();
        return $date && $date->format('Y-m-d H:i:s') === $value && ($errors === false || (!$errors['warning_count'] && !$errors['error_count'])) ? $date : false;
    }

    private function base64Json(mixed $value): string {return base64_encode(json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));}
    private function loadEngine(): void {require_once(DIR_EXTENSION . 'furmedia_scheduled_popup/system/library/furmedia_scheduled_popup.php');}
    private function clearCacheDirectory(string $path): void {$root=realpath($path);if(!$root)return;$it=new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root,\FilesystemIterator::SKIP_DOTS),\RecursiveIteratorIterator::CHILD_FIRST);foreach($it as $item)if($item->isFile()&&!in_array($item->getBasename(),['index.html','.htaccess'],true))@unlink($item->getPathname());}
}
