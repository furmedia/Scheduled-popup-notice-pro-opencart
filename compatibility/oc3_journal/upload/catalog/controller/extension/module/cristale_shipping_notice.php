<?php
class ControllerExtensionModuleCristaleShippingNotice extends Controller {
    private $engine_loaded = false;

    public function index() {
        $this->loadEngine();
        $this->load->language('extension/module/cristale_shipping_notice');
        if (!(int)$this->config->get('module_cristale_shipping_notice_status')) {
            return '';
        }

        $campaigns = $this->campaigns();
        $active = array();
        $now = new DateTime('now');
        foreach ($campaigns as $campaign) {
            $occurrence = FurmediaScheduledPopupEngine::activeOccurrence($campaign, $now);
            if (!$occurrence || !$this->matchesPage($campaign)) {
                continue;
            }
            $active[] = $this->publicCampaign($campaign, $occurrence, $now);
        }
        if (!$active) {
            return '';
        }

        $data = array(
            'campaigns_b64' => base64_encode(json_encode($active, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)),
            'track_url_b64' => base64_encode($this->url->link('extension/module/cristale_shipping_notice/track', '', true)),
            'labels_b64' => base64_encode(json_encode(array(
                'close' => $this->language->get('text_close'),
                'days' => $this->language->get('text_days'),
                'hours' => $this->language->get('text_hours'),
                'minutes' => $this->language->get('text_minutes'),
                'seconds' => $this->language->get('text_seconds')
            ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE))
        );
        return $this->load->view('extension/module/cristale_shipping_notice', $data);
    }

    public function getEmailMessage($order_info = array()) {
        $this->loadEngine();
        if (!(int)$this->config->get('module_cristale_shipping_notice_status')) {
            return '';
        }
        $messages = array();
        $now = new DateTime('now');
        $language_id = isset($order_info['language_id']) ? (int)$order_info['language_id'] : (int)$this->config->get('config_language_id');
        $store_name = !empty($order_info['store_name']) ? $order_info['store_name'] : $this->storeName();
        foreach ($this->campaigns(array($language_id)) as $campaign) {
            $occurrence = FurmediaScheduledPopupEngine::activeOccurrence($campaign, $now);
            if (!$occurrence || !$this->matchesOrder($campaign, $order_info)) {
                continue;
            }
            $content = $this->content($campaign, $language_id);
            $message = FurmediaScheduledPopupEngine::replaceShortcodes($content['email_message'], $campaign, $occurrence, $store_name, $now);
            if (trim($message) !== '') {
                $messages[] = trim($message);
            }
        }
        return implode("\n\n", array_unique($messages));
    }

    public function track() {
        $this->loadEngine();
        $this->createStatsTable();
        $json = array('success' => false);
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $campaign_id = isset($this->request->post['campaign_id']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $this->request->post['campaign_id']) : '';
            $occurrence_key = isset($this->request->post['occurrence_key']) ? preg_replace('/[^0-9]/', '', $this->request->post['occurrence_key']) : '';
            $event_type = isset($this->request->post['event_type']) ? $this->request->post['event_type'] : '';
            if ($campaign_id && in_array($event_type, array('impression', 'click', 'close'), true) && $this->knownActiveCampaign($campaign_id, $occurrence_key)) {
                $session_key = 'spn_stat_' . $campaign_id . '_' . $occurrence_key . '_' . $event_type;
                if (empty($this->session->data[$session_key])) {
                    $this->db->query("INSERT INTO `" . DB_PREFIX . "cristale_shipping_notice_stat` SET campaign_id = '" . $this->db->escape($campaign_id) . "', event_type = '" . $this->db->escape($event_type) . "', stat_date = CURDATE(), total = '1' ON DUPLICATE KEY UPDATE total = total + 1");
                    $this->session->data[$session_key] = 1;
                }
                $json['success'] = true;
            }
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function campaigns($language_ids = array()) {
        $json = $this->config->get('module_cristale_shipping_notice_campaigns_json');
        if (!$language_ids) {
            $language_ids = array((int)$this->config->get('config_language_id'));
        }
        $legacy = array(
            'status' => $this->config->get('module_cristale_shipping_notice_status'),
            'timezone' => $this->config->get('module_cristale_shipping_notice_timezone'),
            'starts_at' => $this->config->get('module_cristale_shipping_notice_starts_at'),
            'ends_at' => $this->config->get('module_cristale_shipping_notice_ends_at'),
            'banner_title' => $this->config->get('module_cristale_shipping_notice_banner_title'),
            'banner_message' => $this->config->get('module_cristale_shipping_notice_banner_message'),
            'banner_submessage' => $this->config->get('module_cristale_shipping_notice_banner_submessage'),
            'email_message' => $this->config->get('module_cristale_shipping_notice_email_message')
        );
        return FurmediaScheduledPopupEngine::decodeCampaigns($json, $language_ids, $legacy, $this->languageCodes($language_ids));
    }

    private function languageCodes($language_ids) {
        $ids = array();
        foreach ((array)$language_ids as $language_id) {
            if ((int)$language_id > 0) {
                $ids[] = (int)$language_id;
            }
        }
        if (!$ids) {
            return array();
        }

        $codes = array();
        $query = $this->db->query("SELECT language_id, code FROM `" . DB_PREFIX . "language` WHERE language_id IN (" . implode(',', array_unique($ids)) . ")");
        foreach ($query->rows as $row) {
            $codes[(int)$row['language_id']] = (string)$row['code'];
        }
        return $codes;
    }

    private function publicCampaign($campaign, $occurrence, $now) {
        $content = $this->content($campaign);
        foreach ($content as $key => $value) {
            $content[$key] = FurmediaScheduledPopupEngine::replaceShortcodes($value, $campaign, $occurrence, $this->storeName(), $now);
        }
        return array(
            'id' => $campaign['id'],
            'occurrence_key' => $occurrence['key'],
            'title' => $content['title'],
            'message' => $content['message'],
            'submessage' => $content['submessage'],
            'thanks' => $content['thanks'],
            'button_text' => $content['button_text'],
            'countdown_label' => $content['countdown_label'],
            'image' => $campaign['image'] ? 'image/' . $campaign['image'] : 'catalog/view/theme/default/image/cristale_shipping_notice/shipping-notice-background.webp',
            'preset' => $campaign['preset'],
            'accent_color' => $campaign['accent_color'],
            'background_color' => $campaign['background_color'],
            'text_color' => $campaign['text_color'],
            'button_color' => $campaign['button_color'],
            'overlay_color' => $campaign['overlay_color'],
            'overlay_opacity' => (int)$campaign['overlay_opacity'],
            'blur' => (int)$campaign['blur'],
            'countdown' => !empty($campaign['countdown']) ? 1 : 0,
            'countdown_end' => $occurrence['end']->format(DateTime::ATOM),
            'button_url' => $campaign['button_url'],
            'button_target' => $campaign['button_target']
        );
    }

    private function content($campaign, $language_id = null) {
        $language_key = (string)($language_id === null ? (int)$this->config->get('config_language_id') : (int)$language_id);
        if (isset($campaign['content'][$language_key])) {
            return $campaign['content'][$language_key];
        }
        $first = reset($campaign['content']);
        return is_array($first) ? $first : FurmediaScheduledPopupEngine::defaultContent();
    }

    private function matchesPage($campaign) {
        if ($campaign['target_type'] === 'all') {
            return true;
        }
        $route = isset($this->request->get['route']) ? $this->request->get['route'] : '';
        $product_id = isset($this->request->get['product_id']) ? (int)$this->request->get['product_id'] : 0;
        if ($campaign['target_type'] === 'products') {
            return $product_id > 0 && in_array($product_id, $campaign['target_products']);
        }
        $category_ids = array();
        if ($route === 'product/category' && !empty($this->request->get['path'])) {
            foreach (explode('_', $this->request->get['path']) as $category_id) {
                $category_ids[] = (int)$category_id;
            }
        }
        if ($product_id > 0) {
            $query = $this->db->query("SELECT category_id FROM `" . DB_PREFIX . "product_to_category` WHERE product_id = '" . $product_id . "'");
            foreach ($query->rows as $row) {
                $category_ids[] = (int)$row['category_id'];
            }
        }
        return (bool)array_intersect($campaign['target_categories'], array_unique($category_ids));
    }

    private function matchesOrder($campaign, $order_info) {
        if ($campaign['target_type'] === 'all') {
            return true;
        }
        if (empty($order_info['order_id'])) {
            return false;
        }
        $products = array();
        $query = $this->db->query("SELECT product_id FROM `" . DB_PREFIX . "order_product` WHERE order_id = '" . (int)$order_info['order_id'] . "'");
        foreach ($query->rows as $row) {
            $products[] = (int)$row['product_id'];
        }
        if ($campaign['target_type'] === 'products') {
            return (bool)array_intersect($campaign['target_products'], $products);
        }
        if (!$products) {
            return false;
        }
        $categories = array();
        $query = $this->db->query("SELECT DISTINCT category_id FROM `" . DB_PREFIX . "product_to_category` WHERE product_id IN (" . implode(',', array_map('intval', $products)) . ")");
        foreach ($query->rows as $row) {
            $categories[] = (int)$row['category_id'];
        }
        return (bool)array_intersect($campaign['target_categories'], $categories);
    }

    private function knownActiveCampaign($campaign_id, $occurrence_key) {
        $now = new DateTime('now');
        foreach ($this->campaigns() as $campaign) {
            if ($campaign['id'] !== $campaign_id) continue;
            $occurrence = FurmediaScheduledPopupEngine::activeOccurrence($campaign, $now);
            return $occurrence && $occurrence['key'] === $occurrence_key;
        }
        return false;
    }

    private function createStatsTable() {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "cristale_shipping_notice_stat` (`campaign_id` varchar(64) NOT NULL, `event_type` varchar(16) NOT NULL, `stat_date` date NOT NULL, `total` int(10) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`campaign_id`,`event_type`,`stat_date`)) ENGINE=MyISAM DEFAULT CHARSET=utf8");
    }

    private function storeName() {
        $name = $this->config->get('config_name');
        return $name ? $name : '';
    }

    private function loadEngine() {
        if (!$this->engine_loaded) {
            require_once(DIR_SYSTEM . 'library/furmedia_scheduled_popup.php');
            $this->engine_loaded = true;
        }
    }
}
