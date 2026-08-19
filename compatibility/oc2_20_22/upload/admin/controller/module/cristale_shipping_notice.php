<?php
class ControllerModuleCristaleShippingNotice extends Controller {
    private $error = array();
    private $engine_loaded = false;

    private $legacy_defaults = array(
        'status' => 0,
        'timezone' => 'UTC',
        'starts_at' => '2026-01-01 00:00:00',
        'ends_at' => '2026-01-02 00:00:00',
        'banner_title' => 'Important announcement',
        'banner_message' => 'Use this space for your scheduled announcement.',
        'banner_submessage' => 'The popup automatically disappears after the end date.',
        'email_message' => 'This message is added to order confirmation emails while the schedule is active.'
    );

    public function index() {
        $this->loadEngine();
        $this->load->language('module/cristale_shipping_notice');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');

        $languages = $this->getLanguages();
        $language_ids = $this->languageIds($languages);
        $language_codes = $this->languageCodes($languages);
        $campaigns = $this->getCampaigns($language_ids, $language_codes);
        $templates = $this->getTemplates($language_ids, $language_codes);

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $campaigns = FurmediaScheduledPopupEngine::decodeCampaigns(
                isset($this->request->post['module_cristale_shipping_notice_campaigns_json']) ? html_entity_decode($this->request->post['module_cristale_shipping_notice_campaigns_json'], ENT_QUOTES, 'UTF-8') : '',
                $language_ids,
                array(),
                $language_codes
            );
            $campaigns = $this->processUploads($campaigns);
            $templates = $this->decodeTemplates(
                isset($this->request->post['module_cristale_shipping_notice_templates_json']) ? html_entity_decode($this->request->post['module_cristale_shipping_notice_templates_json'], ENT_QUOTES, 'UTF-8') : '',
                $language_ids
            );

            if ($this->validate($campaigns, $language_ids)) {
                $settings = array(
                    'module_cristale_shipping_notice_status' => !empty($this->request->post['module_cristale_shipping_notice_status']) ? 1 : 0,
                    'module_cristale_shipping_notice_campaigns_json' => FurmediaScheduledPopupEngine::encodeCampaigns($campaigns),
                    'module_cristale_shipping_notice_templates_json' => $this->encodeTemplates($templates)
                );
                $this->model_setting_setting->editSetting('module_cristale_shipping_notice', $settings);
                $this->createStatsTable();
                $this->session->data['success'] = $this->language->get('text_success');
                $this->response->redirect($this->url->link('module/cristale_shipping_notice', 'token=' . $this->session->data['token'], true));
                return;
            }
        }

        $data = $this->languageData();
        $data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
        if (!$data['error_warning'] && isset($this->session->data['error_warning'])) {
            $data['error_warning'] = $this->session->data['error_warning'];
            unset($this->session->data['error_warning']);
        }
        $data['success'] = isset($this->session->data['success']) ? $this->session->data['success'] : '';
        unset($this->session->data['success']);

        $token = 'token=' . $this->session->data['token'];
        $data['breadcrumbs'] = array(
            array('text' => $this->language->get('text_home'), 'href' => $this->url->link('common/dashboard', $token, true)),
            array('text' => $this->language->get('text_extension'), 'href' => $this->url->link('extension/extension', $token . '&type=module', true)),
            array('text' => $this->language->get('heading_title'), 'href' => $this->url->link('module/cristale_shipping_notice', $token, true))
        );
        $data['action'] = $this->url->link('module/cristale_shipping_notice', $token, true);
        $data['cancel'] = $this->url->link('extension/extension', $token . '&type=module', true);
        $data['clear_cache'] = $this->url->link('module/cristale_shipping_notice/clearCache', $token, true);
        $data['autocomplete_url'] = $this->url->link('module/cristale_shipping_notice/autocomplete', $token, true);
        $data['reset_stats_url'] = $this->url->link('module/cristale_shipping_notice/resetStats', $token, true);
        $data['autocomplete_url_b64'] = $this->base64Json(html_entity_decode($data['autocomplete_url'], ENT_QUOTES, 'UTF-8'));
        $data['reset_stats_url_b64'] = $this->base64Json(html_entity_decode($data['reset_stats_url'], ENT_QUOTES, 'UTF-8'));

        $data['module_cristale_shipping_notice_status'] = isset($this->request->post['module_cristale_shipping_notice_status'])
            ? (int)$this->request->post['module_cristale_shipping_notice_status']
            : (int)$this->config->get('module_cristale_shipping_notice_status');
        $data['campaigns_b64'] = $this->base64Json($campaigns);
        $data['languages_b64'] = $this->base64Json($languages);
        $data['categories_b64'] = $this->base64Json($this->getCategories());
        $data['timezones_b64'] = $this->base64Json($this->getTimezones());
        $data['stats_b64'] = $this->base64Json($this->getStats());
        $data['ui_b64'] = $this->base64Json($this->adminUi());
        $data['templates_b64'] = $this->base64Json($templates);
        $data['catalog_image_url'] = $this->catalogUrl() . 'image/';
        $data['default_image_url'] = $this->catalogUrl() . 'catalog/view/theme/default/image/cristale_shipping_notice/shipping-notice-background.webp';

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('module/cristale_shipping_notice', $data));
    }

    public function autocomplete() {
        $json = array();
        if ($this->user->hasPermission('access', 'module/cristale_shipping_notice')) {
            $filter = isset($this->request->get['filter_name']) ? trim($this->request->get['filter_name']) : '';
            $language_id = $this->storeLanguageId();
            $where = "(pd.name IS NOT NULL OR pd_default.name IS NOT NULL OR p.model IS NOT NULL)";
            $order = 'p.date_modified DESC, p.product_id DESC';
            if ($filter !== '') {
                $escaped = $this->db->escape($filter);
                $where .= " AND (pd.name LIKE '%" . $escaped . "%' OR pd_default.name LIKE '%" . $escaped . "%' OR p.model LIKE '%" . $escaped . "%' OR p.sku LIKE '%" . $escaped . "%'";
                if (ctype_digit($filter)) {
                    $where .= " OR p.product_id = '" . (int)$filter . "'";
                }
                $where .= ')';
                $order = "CASE WHEN pd.name = '" . $escaped . "' OR p.model = '" . $escaped . "' OR p.sku = '" . $escaped . "' THEN 0 ELSE 1 END, pd.name ASC";
            }
            $query = $this->db->query(
                "SELECT p.product_id, COALESCE(NULLIF(pd.name, ''), NULLIF(pd_default.name, ''), p.model) AS name, p.model, p.sku, p.quantity, p.status FROM `" . DB_PREFIX . "product` p "
                . "LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id AND pd.language_id = '" . (int)$language_id . "') "
                . "LEFT JOIN `" . DB_PREFIX . "product_description` pd_default ON (p.product_id = pd_default.product_id AND pd_default.language_id = '1') "
                . "WHERE " . $where . " ORDER BY " . $order . " LIMIT 30"
            );
            foreach ($query->rows as $row) {
                $json[] = array(
                    'product_id' => (int)$row['product_id'],
                    'name' => html_entity_decode($row['name'], ENT_QUOTES, 'UTF-8'),
                    'model' => $row['model'],
                    'sku' => $row['sku'],
                    'quantity' => (int)$row['quantity'],
                    'status' => (int)$row['status']
                );
            }
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function resetStats() {
        $this->load->language('module/cristale_shipping_notice');
        $this->createStatsTable();
        if (!$this->user->hasPermission('modify', 'module/cristale_shipping_notice')) {
            $this->session->data['error_warning'] = $this->language->get('error_permission');
        } else {
            $campaign_id = isset($this->request->get['campaign_id']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $this->request->get['campaign_id']) : '';
            if ($campaign_id !== '') {
                $this->db->query("DELETE FROM `" . DB_PREFIX . "cristale_shipping_notice_stat` WHERE campaign_id = '" . $this->db->escape($campaign_id) . "'");
            } else {
                $this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "cristale_shipping_notice_stat`");
            }
            $this->session->data['success'] = $this->language->get('text_stats_reset');
        }
        $this->response->redirect($this->url->link('module/cristale_shipping_notice', 'token=' . $this->session->data['token'], true));
    }

    public function clearCache() {
        $this->load->language('module/cristale_shipping_notice');
        if (!$this->user->hasPermission('modify', 'module/cristale_shipping_notice')) {
            $this->session->data['error_warning'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('module/cristale_shipping_notice', 'token=' . $this->session->data['token'], true));
            return;
        }
        $deleted = defined('DIR_CACHE') ? $this->clearCacheDirectory(DIR_CACHE) : 0;
        $warm = $this->warmHomepage();
        $this->session->data['success'] = sprintf($this->language->get('text_cache_success'), $deleted, $warm['status'], $warm['milliseconds']);
        $this->response->redirect($this->url->link('module/cristale_shipping_notice', 'token=' . $this->session->data['token'], true));
    }

    public function install() {
        $this->loadEngine();
        $this->load->language('module/cristale_shipping_notice');
        $this->load->model('setting/setting');
        $existing = $this->model_setting_setting->getSetting('module_cristale_shipping_notice');
        if (!isset($existing['module_cristale_shipping_notice_status'])) {
            $existing['module_cristale_shipping_notice_status'] = 0;
        }
        if (empty($existing['module_cristale_shipping_notice_campaigns_json'])) {
            $languages = $this->getLanguages();
            $campaigns = array(FurmediaScheduledPopupEngine::fromLegacy($this->languageIds($languages), $this->legacySettings(), $this->languageCodes($languages)));
            $existing['module_cristale_shipping_notice_campaigns_json'] = FurmediaScheduledPopupEngine::encodeCampaigns($campaigns);
        }
        if (!array_key_exists('module_cristale_shipping_notice_templates_json', $existing)) {
            $languages = $this->getLanguages();
            $language_ids = $this->languageIds($languages);
            $language_codes = $this->languageCodes($languages);
            $existing['module_cristale_shipping_notice_templates_json'] = $this->encodeTemplates($this->defaultTemplates($language_ids, $language_codes));
        }
        $this->model_setting_setting->editSetting('module_cristale_shipping_notice', $existing);
        $this->createStatsTable();
        $this->db->query("DELETE FROM `" . DB_PREFIX . "modification` WHERE `code` = 'cristale_shipping_notice'");
        $this->db->query("INSERT INTO `" . DB_PREFIX . "modification` SET `name` = '" . $this->db->escape($this->language->get('heading_title')) . "', `code` = 'cristale_shipping_notice', `author` = 'Furmedia', `version` = '2.0.7', `link` = 'https://github.com/furmedia/Scheduled-popup-notice-pro-opencart', `xml` = '" . $this->db->escape($this->getModificationXml()) . "', `status` = '1', `date_added` = NOW()");
    }

    public function uninstall() {
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('module_cristale_shipping_notice');
        $this->db->query("DELETE FROM `" . DB_PREFIX . "modification` WHERE `code` = 'cristale_shipping_notice'");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "cristale_shipping_notice_stat`");
    }

    protected function validate($campaigns, $language_ids) {
        if (!empty($this->error['warning'])) {
            return false;
        }
        if (!$this->user->hasPermission('modify', 'module/cristale_shipping_notice')) {
            $this->error['warning'] = $this->language->get('error_permission');
            return false;
        }
        if (!$campaigns || count($campaigns) > 50) {
            $this->error['warning'] = $this->language->get('error_campaign_limit');
            return false;
        }
        $identifiers = DateTimeZone::listIdentifiers(DateTimeZone::ALL_WITH_BC);
        if (!in_array('UTC', $identifiers, true)) {
            $identifiers[] = 'UTC';
        }
        foreach ($campaigns as $index => $campaign) {
            $number = $index + 1;
            if ($campaign['name'] === '') {
                $this->error['warning'] = sprintf($this->language->get('error_campaign_name'), $number);
                return false;
            }
            if (!in_array($campaign['timezone'], $identifiers, true)) {
                $this->error['warning'] = sprintf($this->language->get('error_campaign_timezone'), $number);
                return false;
            }
            $start = $this->parseDate($campaign['starts_at'], $campaign['timezone']);
            $end = $this->parseDate($campaign['ends_at'], $campaign['timezone']);
            if (!$start || !$end || $end <= $start) {
                $this->error['warning'] = sprintf($this->language->get('error_campaign_dates'), $number);
                return false;
            }
            if ($campaign['recurrence_until'] !== '' && !$this->parseDate($campaign['recurrence_until'], $campaign['timezone'])) {
                $this->error['warning'] = sprintf($this->language->get('error_campaign_recurrence'), $number);
                return false;
            }
            if (empty($campaign['devices'])) {
                $this->error['warning'] = sprintf($this->language->get('error_campaign_devices'), $number);
                return false;
            }
            if ($campaign['target_type'] === 'categories' && !$campaign['target_categories']) {
                $this->error['warning'] = sprintf($this->language->get('error_campaign_target'), $number);
                return false;
            }
            if ($campaign['target_type'] === 'products' && !$campaign['target_products']) {
                $this->error['warning'] = sprintf($this->language->get('error_campaign_target'), $number);
                return false;
            }
            foreach ($language_ids as $language_id) {
                $key = (string)$language_id;
                if (empty($campaign['content'][$key]['title']) || empty($campaign['content'][$key]['message'])) {
                    $this->error['warning'] = sprintf($this->language->get('error_campaign_content'), $number);
                    return false;
                }
            }
        }
        return empty($this->error['warning']);
    }

    private function getCampaigns($language_ids, $language_codes) {
        $json = $this->config->get('module_cristale_shipping_notice_campaigns_json');
        return FurmediaScheduledPopupEngine::decodeCampaigns($json, $language_ids, $this->legacySettings(), $language_codes);
    }

    private function getTemplates($language_ids, $language_codes) {
        $json = $this->config->get('module_cristale_shipping_notice_templates_json');
        $templates = $this->decodeTemplates($json, $language_ids);
        if ($templates) {
            return $templates;
        }
        return $this->defaultTemplates($language_ids, $language_codes);
    }

    private function buildTemplate($id, $name, $language_ids, $language_codes) {
        $content = array();
        foreach ((array)$language_ids as $language_id) {
            $content[(string)(int)$language_id] = FurmediaScheduledPopupEngine::defaultContent($this->languageCode($language_id, $language_codes));
        }
        return array(
            'id' => preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$id),
            'name' => $name,
            'content' => $content
        );
    }

    private function defaultTemplates($language_ids, $language_codes) {
        return array(
            $this->buildTemplate('delivery_default', $this->language->get('text_default_template_label'), $language_ids, $language_codes),
            $this->buildTemplate('holiday_default', 'Șablon zile libere', $language_ids, $language_codes)
        );
    }

    private function languageCode($language_id, $language_codes) {
        $numeric = (int)$language_id;
        $string = (string)$numeric;
        if (isset($language_codes[$numeric])) {
            return $language_codes[$numeric];
        }
        if (isset($language_codes[$string])) {
            return $language_codes[$string];
        }
        return 'en-gb';
    }

    private function decodeTemplates($json, $language_ids = array()) {
        $decoded = json_decode((string)$json, true);
        if (!is_array($decoded)) {
            return array();
        }
        $normalized = array();
        foreach ($decoded as $template) {
            if (!is_array($template)) {
                continue;
            }
            $name = trim((string)(isset($template['name']) ? $template['name'] : ''));
            $entry = array(
                'id' => preg_replace('/[^a-zA-Z0-9_-]/', '', (string)isset($template['id']) ? $template['id'] : 'template'),
                'name' => $name === '' ? $this->language->get('text_untitled_template') : $name,
                'content' => array()
            );
            $incoming = isset($template['content']) && is_array($template['content']) ? $template['content'] : array();
            foreach ((array)$language_ids as $language_id) {
                $key = (string)(int)$language_id;
                $content = (isset($incoming[$key]) && is_array($incoming[$key])) ? $incoming[$key] : array();
                $entry['content'][$key] = array(
                    'title' => isset($content['title']) ? (string)$content['title'] : '',
                    'message' => isset($content['message']) ? (string)$content['message'] : '',
                    'submessage' => isset($content['submessage']) ? (string)$content['submessage'] : '',
                    'thanks' => isset($content['thanks']) ? (string)$content['thanks'] : '',
                    'email_message' => isset($content['email_message']) ? (string)$content['email_message'] : '',
                    'button_text' => isset($content['button_text']) ? (string)$content['button_text'] : '',
                    'countdown_label' => isset($content['countdown_label']) ? (string)$content['countdown_label'] : ''
                );
            }
            $normalized[] = $entry;
        }
        return $normalized;
    }

    private function encodeTemplates($templates) {
        return json_encode(array_values((array)$templates), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function legacySettings() {
        $legacy = array();
        foreach ($this->legacy_defaults as $key => $default) {
            $value = $this->config->get('module_cristale_shipping_notice_' . $key);
            $legacy[$key] = $value !== null ? $value : $default;
        }
        return $legacy;
    }

    private function processUploads($campaigns) {
        foreach ($campaigns as $index => $campaign) {
            if (!empty($campaign['remove_image'])) {
                $this->deleteCampaignImage($campaign['image']);
                $campaigns[$index]['image'] = '';
            }
            $key = 'campaign_image_' . $campaign['id'];
            if (!isset($this->request->files[$key]) || $this->request->files[$key]['error'] == UPLOAD_ERR_NO_FILE) {
                $campaigns[$index]['remove_image'] = 0;
                continue;
            }
            $file = $this->request->files[$key];
            if ($file['error'] != UPLOAD_ERR_OK || $file['size'] > 5242880) {
                $this->error['warning'] = $this->language->get('error_upload');
                continue;
            }
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $image_info = @getimagesize($file['tmp_name']);
            $mimes = array('image/jpeg' => array('jpg', 'jpeg'), 'image/png' => array('png'), 'image/webp' => array('webp'));
            if (!$image_info || !isset($mimes[$image_info['mime']]) || !in_array($extension, $mimes[$image_info['mime']], true) || $image_info[0] < 1 || $image_info[1] < 1 || $image_info[0] > 10000 || $image_info[1] > 10000 || ($image_info[0] * $image_info[1]) > 20000000) {
                $this->error['warning'] = $this->language->get('error_upload');
                continue;
            }
            $directory = rtrim(DIR_IMAGE, '/\\') . DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'scheduled-popup-notice';
            if (!is_dir($directory) && !@mkdir($directory, 0755, true)) {
                $this->error['warning'] = $this->language->get('error_upload');
                continue;
            }
            $basename = $campaign['id'] . '-' . date('YmdHis') . '-' . mt_rand(1000, 9999);
            $filename = $this->storeCampaignImage($file['tmp_name'], $image_info['mime'], $image_info[0], $image_info[1], $directory, $basename, $extension);
            if (!$filename) {
                $this->error['warning'] = $this->language->get('error_upload');
                continue;
            }
            $this->deleteCampaignImage($campaign['image']);
            $campaigns[$index]['image'] = 'catalog/scheduled-popup-notice/' . $filename;
            $campaigns[$index]['remove_image'] = 0;
        }
        return $campaigns;
    }

    private function storeCampaignImage($temporary, $mime, $width, $height, $directory, $basename, $fallback_extension) {
        $webp_path = $directory . DIRECTORY_SEPARATOR . $basename . '.webp';
        $loaders = array('image/jpeg' => 'imagecreatefromjpeg', 'image/png' => 'imagecreatefrompng', 'image/webp' => 'imagecreatefromwebp');
        $can_optimize = function_exists('imagewebp') && function_exists('imagecreatetruecolor') && isset($loaders[$mime]) && function_exists($loaders[$mime]);

        if ($can_optimize) {
            return $this->createOptimizedWebp($temporary, $mime, $width, $height, $webp_path) ? $basename . '.webp' : '';
        }

        $filename = $basename . '.' . $fallback_extension;
        if (@move_uploaded_file($temporary, $directory . DIRECTORY_SEPARATOR . $filename)) {
            return $filename;
        }

        return '';
    }

    private function createOptimizedWebp($source_path, $mime, $width, $height, $destination_path) {
        if (!function_exists('imagewebp') || !function_exists('imagecreatetruecolor')) {
            return false;
        }

        $loaders = array(
            'image/jpeg' => 'imagecreatefromjpeg',
            'image/png' => 'imagecreatefrompng',
            'image/webp' => 'imagecreatefromwebp'
        );

        if (!isset($loaders[$mime]) || !function_exists($loaders[$mime])) {
            return false;
        }

        $width = (int)$width;
        $height = (int)$height;
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

    private function deleteCampaignImage($relative) {
        if (strpos((string)$relative, 'catalog/scheduled-popup-notice/') !== 0) {
            return;
        }
        $path = rtrim(DIR_IMAGE, '/\\') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
        $root = realpath(rtrim(DIR_IMAGE, '/\\') . DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'scheduled-popup-notice');
        $file = realpath($path);
        if ($root && $file && strpos($file, $root . DIRECTORY_SEPARATOR) === 0 && is_file($file)) {
            @unlink($file);
        }
    }

    private function getLanguages() {
        $rows = $this->db->query("SELECT language_id, name, code FROM `" . DB_PREFIX . "language` WHERE status = '1' ORDER BY sort_order, name")->rows;
        $languages = array();
        foreach ($rows as $row) {
            $languages[] = array('language_id' => (int)$row['language_id'], 'name' => $row['name'], 'code' => $row['code']);
        }
        return $languages;
    }

    private function languageIds($languages) {
        $ids = array();
        foreach ($languages as $language) {
            $ids[] = (int)$language['language_id'];
        }
        return $ids;
    }

    private function languageCodes($languages) {
        $codes = array();
        foreach ($languages as $language) {
            $codes[(int)$language['language_id']] = (string)$language['code'];
        }
        return $codes;
    }

    private function storeLanguageId() {
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

    private function getTimezones() {
        $identifiers = DateTimeZone::listIdentifiers(DateTimeZone::ALL_WITH_BC);
        if (!in_array('UTC', $identifiers, true)) {
            array_unshift($identifiers, 'UTC');
        }

        $groups = array();
        $now = new DateTime('now', new DateTimeZone('UTC'));
        foreach ($identifiers as $identifier) {
            try {
                $timezone = new DateTimeZone($identifier);
                $offset = $timezone->getOffset($now);
            } catch (Exception $e) {
                continue;
            }
            $sign = $offset < 0 ? '-' : '+';
            $absolute = abs($offset);
            $offset_label = sprintf('UTC%s%02d:%02d', $sign, floor($absolute / 3600), floor(($absolute % 3600) / 60));
            $parts = explode('/', $identifier, 2);
            $group = count($parts) > 1 ? $parts[0] : 'General';
            if (!isset($groups[$group])) {
                $groups[$group] = array();
            }
            $groups[$group][] = array('value' => $identifier, 'label' => '(' . $offset_label . ') ' . str_replace('_', ' ', $identifier));
        }

        ksort($groups, SORT_NATURAL | SORT_FLAG_CASE);
        $result = array();
        foreach ($groups as $label => $zones) {
            $result[] = array('label' => $label, 'zones' => $zones);
        }
        return $result;
    }

    private function getCategories() {
        $language_id = $this->storeLanguageId();
        $query = $this->db->query("SELECT c.category_id, cd.name FROM `" . DB_PREFIX . "category` c LEFT JOIN `" . DB_PREFIX . "category_description` cd ON (c.category_id = cd.category_id) WHERE cd.language_id = '" . $language_id . "' ORDER BY cd.name");
        $result = array();
        foreach ($query->rows as $row) {
            $result[] = array('category_id' => (int)$row['category_id'], 'name' => html_entity_decode($row['name'], ENT_QUOTES, 'UTF-8'));
        }
        return $result;
    }

    private function createStatsTable() {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "cristale_shipping_notice_stat` (`campaign_id` varchar(64) NOT NULL, `event_type` varchar(16) NOT NULL, `stat_date` date NOT NULL, `total` int(10) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`campaign_id`,`event_type`,`stat_date`)) ENGINE=MyISAM DEFAULT CHARSET=utf8");
    }

    private function getStats() {
        $stats = array();
        $this->createStatsTable();
        $query = $this->db->query("SELECT campaign_id, event_type, SUM(total) AS total FROM `" . DB_PREFIX . "cristale_shipping_notice_stat` GROUP BY campaign_id, event_type");
        foreach ($query->rows as $row) {
            if (!isset($stats[$row['campaign_id']])) {
                $stats[$row['campaign_id']] = array('impression' => 0, 'click' => 0, 'close' => 0);
            }
            if (isset($stats[$row['campaign_id']][$row['event_type']])) {
                $stats[$row['campaign_id']][$row['event_type']] = (int)$row['total'];
            }
        }
        return $stats;
    }

    private function languageData() {
        $keys = array(
            'heading_title', 'text_edit', 'text_enabled', 'text_disabled', 'text_signature', 'text_performance_tools', 'text_performance_note',
            'button_save', 'button_save_stay', 'button_cancel', 'button_clear_cache'
        );
        $data = array();
        foreach ($keys as $key) {
            $data[$key] = $this->language->get($key);
        }
        return $data;
    }

    private function adminUi() {
        $keys = array(
            'text_add_campaign', 'text_duplicate', 'text_delete', 'text_campaign', 'text_schedule', 'text_content', 'text_design', 'text_targeting', 'text_statistics',
            'entry_status', 'entry_campaign_name', 'entry_priority', 'entry_timezone', 'entry_starts_at', 'entry_ends_at', 'entry_recurrence', 'entry_recurrence_until', 'text_date', 'text_time_optional',
            'entry_date_format', 'entry_time_format', 'entry_trigger', 'entry_trigger_delay', 'entry_trigger_selector', 'entry_trigger_scroll_depth', 'entry_auto_close',
            'text_none', 'text_weekly', 'text_monthly',
            'text_trigger_on_load', 'text_trigger_on_delay', 'text_trigger_on_click', 'text_trigger_on_scroll', 'text_trigger_on_exit', 'text_trigger_on_inactivity',
            'text_template_library', 'text_template_name', 'text_template_save', 'text_template_apply', 'text_template_delete', 'text_default_template_label', 'text_untitled_template', 'text_template_none',
            'entry_title', 'entry_message', 'entry_submessage', 'entry_thanks',
            'entry_email_enabled', 'text_email_enabled_help', 'entry_email_message', 'entry_button_text', 'entry_button_url', 'entry_button_target', 'entry_countdown', 'entry_countdown_label', 'entry_image', 'text_remove_image',
            'entry_preset', 'text_preset_elegant', 'text_preset_minimal', 'text_preset_bold', 'entry_accent_color', 'entry_background_color', 'entry_text_color',
            'entry_button_color', 'entry_overlay_color', 'entry_overlay_opacity', 'entry_blur', 'entry_target_type', 'text_target_all', 'text_target_categories',
            'entry_position', 'entry_geo_countries', 'entry_devices', 'text_device_desktop', 'text_device_tablet', 'text_device_mobile', 'text_device_help', 'text_device_laptop',
            'text_responsive_studio', 'text_responsive_help', 'entry_popup_width', 'entry_popup_height', 'entry_breakpoint_mobile', 'entry_breakpoint_tablet', 'entry_breakpoint_laptop',
            'text_position_center', 'text_position_top_left', 'text_position_top_center', 'text_position_top_right', 'text_position_middle_left',
            'text_position_middle_center', 'text_position_middle_right', 'text_position_bottom_left', 'text_position_bottom_center', 'text_position_bottom_right',
            'text_target_products', 'entry_categories', 'entry_products', 'placeholder_product_search', 'text_impressions', 'text_clicks', 'text_closes', 'text_ctr', 'text_reset_stats',
            'text_template_library', 'text_template_name', 'text_template_save', 'text_template_apply', 'text_template_delete', 'text_template_import', 'text_template_export',
            'text_template_no_selection', 'text_template_delete_confirm', 'text_trigger_on_adblock',
            'text_preview', 'text_shortcodes', 'text_shortcode_help', 'text_upload_help', 'text_no_campaigns', 'text_confirm_delete', 'text_new_campaign', 'text_open_same', 'text_open_new',
            'text_apply_romanian_template', 'text_apply_english_template', 'text_template_help', 'text_product_loading', 'text_product_empty', 'text_product_error', 'text_product_suggestions', 'text_product_model', 'button_product_search'
        );
        $ui = array();
        foreach ($keys as $key) {
            $ui[$key] = $this->language->get($key);
        }
        $ui['shortcodes'] = array('{start_date}', '{start_time}', '{end_date}', '{end_time}', '{days_remaining}', '{hours_remaining}', '{countdown}', '{store_name}', '{campaign_name}', '{year}');
        $ui['content_templates'] = array(
            'ro' => FurmediaScheduledPopupEngine::defaultContent('ro'),
            'en' => FurmediaScheduledPopupEngine::defaultContent('en'),
            'en-gb' => FurmediaScheduledPopupEngine::defaultContent('en'),
            'de-de' => FurmediaScheduledPopupEngine::defaultContent('de'),
            'de' => FurmediaScheduledPopupEngine::defaultContent('de'),
            'fr-fr' => FurmediaScheduledPopupEngine::defaultContent('fr'),
            'fr' => FurmediaScheduledPopupEngine::defaultContent('fr'),
            'es-es' => FurmediaScheduledPopupEngine::defaultContent('es'),
            'es' => FurmediaScheduledPopupEngine::defaultContent('es'),
            'it-it' => FurmediaScheduledPopupEngine::defaultContent('it'),
            'it' => FurmediaScheduledPopupEngine::defaultContent('it'),
            'pt-br' => FurmediaScheduledPopupEngine::defaultContent('pt'),
            'pt' => FurmediaScheduledPopupEngine::defaultContent('pt')
        );
        $ui['holiday_presets'] = array(
            array('id' => 'new_year', 'name' => '1 Ianuarie', 'type' => 'fixed', 'month' => 1, 'day' => 1, 'days' => 1),
            array('id' => 'new_year_day_two', 'name' => '2 Ianuarie', 'type' => 'fixed', 'month' => 1, 'day' => 2, 'days' => 1),
            array('id' => 'state_unity', 'name' => '24 Ianuarie', 'type' => 'fixed', 'month' => 1, 'day' => 24, 'days' => 1),
            array('id' => 'orthodox_christmas', 'name' => '6 Ianuarie', 'type' => 'fixed', 'month' => 1, 'day' => 6, 'days' => 1),
            array('id' => 'saint_john', 'name' => '7 Ianuarie', 'type' => 'fixed', 'month' => 1, 'day' => 7, 'days' => 1),
            array('id' => 'easter', 'name' => 'Paște', 'type' => 'orthodox_easter', 'easter_offset' => 0, 'days' => 1),
            array('id' => 'good_friday', 'name' => 'Vinerea Mare', 'type' => 'orthodox_easter', 'easter_offset' => -2, 'days' => 1),
            array('id' => 'easter_monday', 'name' => 'A doua zi de Paste', 'type' => 'orthodox_easter', 'easter_offset' => 1, 'days' => 1),
            array('id' => 'labour_day', 'name' => '1 Mai', 'type' => 'fixed', 'month' => 5, 'day' => 1, 'days' => 1),
            array('id' => 'children_day', 'name' => '1 Iunie', 'type' => 'fixed', 'month' => 6, 'day' => 1, 'days' => 1),
            array('id' => 'rusalii', 'name' => 'Rusalii', 'type' => 'orthodox_easter', 'easter_offset' => 49, 'days' => 1),
            array('id' => 'rusalii_monday', 'name' => 'A doua zi de Rusalii', 'type' => 'orthodox_easter', 'easter_offset' => 50, 'days' => 1),
            array('id' => 'great_unity', 'name' => '15 August', 'type' => 'fixed', 'month' => 8, 'day' => 15, 'days' => 1),
            array('id' => 'saint_andrew', 'name' => '30 Noiembrie', 'type' => 'fixed', 'month' => 11, 'day' => 30, 'days' => 1),
            array('id' => 'national_day', 'name' => '1 Decembrie', 'type' => 'fixed', 'month' => 12, 'day' => 1, 'days' => 1),
            array('id' => 'christmas', 'name' => 'Crăciun', 'type' => 'fixed', 'month' => 12, 'day' => 25, 'days' => 2)
        );
        return $ui;
    }

    private function loadEngine() {
        if (!$this->engine_loaded) {
            require_once(DIR_SYSTEM . 'library/furmedia_scheduled_popup.php');
            $this->engine_loaded = true;
        }
    }

    private function parseDate($value, $timezone) {
        try {
            $date = DateTime::createFromFormat('Y-m-d H:i:s', $value, new DateTimeZone($timezone));
        } catch (Exception $e) {
            return false;
        }
        $errors = DateTime::getLastErrors();
        return $date && $date->format('Y-m-d H:i:s') === $value && ($errors === false || (!$errors['warning_count'] && !$errors['error_count'])) ? $date : false;
    }

    private function base64Json($value) {
        return base64_encode(json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private function catalogUrl() {
        return defined('HTTPS_CATALOG') ? HTTPS_CATALOG : (defined('HTTP_CATALOG') ? HTTP_CATALOG : '../');
    }

    private function clearCacheDirectory($path) {
        $root = realpath($path);
        if (!$root || !is_dir($root)) return 0;
        $deleted = 0;
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $item) {
            if ($item->isFile() && !in_array($item->getBasename(), array('index.html', '.htaccess'), true) && @unlink($item->getPathname())) $deleted++;
        }
        return $deleted;
    }

    private function warmHomepage() {
        $url = $this->catalogUrl();
        $started = microtime(true);
        $status = 0;
        if (function_exists('curl_init')) {
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3);
            curl_setopt($curl, CURLOPT_TIMEOUT, 8);
            curl_setopt($curl, CURLOPT_USERAGENT, 'Scheduled Popup Notice Pro cache warmer');
            curl_exec($curl);
            $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
        }
        return array('status' => $status, 'milliseconds' => (int)round((microtime(true) - $started) * 1000));
    }

    private function getModificationXml() {
        return '<?xml version="1.0" encoding="utf-8"?>
<modification>
            <name>Scheduled Popup &amp; Notice Pro</name><code>cristale_shipping_notice</code><version>2.0.7</version><author>Furmedia</author><link>https://github.com/furmedia/Scheduled-popup-notice-pro-opencart</link>
  <file path="catalog/controller/common/footer.php"><operation error="skip"><search><![CDATA[$data[\'powered\'] = sprintf($this->language->get(\'text_powered\'), $this->config->get(\'config_name\'), date(\'Y\', time()));]]></search><add position="after"><![CDATA[$data[\'cristale_shipping_notice\'] = $this->load->controller(\'module/cristale_shipping_notice\');]]></add></operation></file>
  <file path="catalog/view/theme/*/template/common/footer.tpl"><operation error="skip"><search><![CDATA[</body>]]></search><add position="before"><![CDATA[<?php if ($cristale_shipping_notice) { echo $cristale_shipping_notice; } ?>]]></add></operation></file>
  <file path="catalog/model/checkout/order.php"><operation error="skip"><search><![CDATA[$data[\'text_greeting\'] = sprintf($language->get(\'text_new_greeting\'), $order_info[\'store_name\']);]]></search><add position="after"><![CDATA[$cristale_shipping_notice_email = $this->load->controller(\'module/cristale_shipping_notice/getEmailMessage\', $order_info); if ($cristale_shipping_notice_email) { $data[\'text_greeting\'] .= "\n\n" . $cristale_shipping_notice_email; }]]></add></operation></file>
</modification>';
    }
}
