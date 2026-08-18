<?php
namespace Opencart\Catalog\Controller\Extension\FurmediaScheduledPopup\Module;

class ScheduledPopup extends \Opencart\System\Engine\Controller {
    private bool $engine_loaded = false;

    public function index(): string {
        $this->loadEngine();
        $this->load->language('extension/furmedia_scheduled_popup/module/scheduled_popup');

        if (!(int)$this->config->get('module_cristale_shipping_notice_status')) {
            return '';
        }

        $active = [];
        $now = new \DateTime('now');
        foreach ($this->campaigns() as $campaign) {
            $occurrence = \FurmediaScheduledPopupEngine::activeOccurrence($campaign, $now);
            if (!$occurrence || !$this->matchesPage($campaign)) {
                continue;
            }
            $active[] = $this->publicCampaign($campaign, $occurrence, $now);
        }

        if (!$active) {
            return '';
        }

        $data = [
            'campaigns_b64' => $this->base64Json($active),
            'track_url_b64' => base64_encode($this->url->link('extension/furmedia_scheduled_popup/module/scheduled_popup.track', '', true)),
            'labels_b64' => $this->base64Json([
                'close' => $this->language->get('text_close'),
                'days' => $this->language->get('text_days'),
                'hours' => $this->language->get('text_hours'),
                'minutes' => $this->language->get('text_minutes'),
                'seconds' => $this->language->get('text_seconds')
            ])
        ];

        return $this->load->view('extension/furmedia_scheduled_popup/module/scheduled_popup', $data);
    }

    public function footerAfter(string &$route, array &$data, string &$output): void {
        if (strpos($output, 'data-furmedia-scheduled-popup') !== false) {
            return;
        }

        $html = $this->index();
        if ($html === '') {
            return;
        }

        $position = strripos($output, '</body>');
        if ($position !== false) {
            $output = substr($output, 0, $position) . $html . substr($output, $position);
        } else {
            $output .= $html;
        }
    }

    public function mailInvoiceAfter(string &$route, array &$data, string &$output): void {
        if (strpos($output, 'data-furmedia-scheduled-popup-email') !== false || empty($data['order_id'])) {
            return;
        }

        $order_id = (int)$data['order_id'];
        $query = $this->db->query("SELECT order_id, language_id, store_name FROM `" . DB_PREFIX . "order` WHERE order_id = '" . $order_id . "' LIMIT 1");
        if (!$query->num_rows) {
            return;
        }

        $message = $this->getEmailMessage($query->row);
        if ($message === '') {
            return;
        }

        $block = '<div data-furmedia-scheduled-popup-email="1" style="margin:0 0 20px;padding:12px 15px;border-left:4px solid #713568;background:#fff7fb;color:#2f2934;font-size:13px;line-height:1.55">'
            . nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'))
            . '</div>';
        $position = strpos($output, '<table');

        if ($position !== false) {
            $output = substr($output, 0, $position) . $block . substr($output, $position);
        } else {
            $output .= $block;
        }
    }

    public function getEmailMessage(array $order_info = []): string {
        $this->loadEngine();
        if (!(int)$this->config->get('module_cristale_shipping_notice_status')) {
            return '';
        }

        $messages = [];
        $now = new \DateTime('now');
        $language_id = isset($order_info['language_id']) ? (int)$order_info['language_id'] : (int)$this->config->get('config_language_id');
        $store_name = !empty($order_info['store_name']) ? (string)$order_info['store_name'] : $this->storeName();

        foreach ($this->campaigns([$language_id]) as $campaign) {
            $occurrence = \FurmediaScheduledPopupEngine::activeOccurrence($campaign, $now);
            if (!$occurrence || !$this->matchesOrder($campaign, $order_info)) {
                continue;
            }

            $content = $this->content($campaign, $language_id);
            $message = \FurmediaScheduledPopupEngine::replaceShortcodes($content['email_message'], $campaign, $occurrence, $store_name, $now);
            if (trim($message) !== '') {
                $messages[] = trim($message);
            }
        }

        return implode("\n\n", array_unique($messages));
    }

    public function track(): void {
        $this->loadEngine();
        $this->createStatsTable();
        $json = ['success' => false];

        if (($this->request->server['REQUEST_METHOD'] ?? '') === 'POST') {
            $campaign_id = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($this->request->post['campaign_id'] ?? ''));
            $occurrence_key = preg_replace('/[^0-9]/', '', (string)($this->request->post['occurrence_key'] ?? ''));
            $event_type = (string)($this->request->post['event_type'] ?? '');

            if ($campaign_id !== '' && in_array($event_type, ['impression', 'click', 'close'], true) && $this->knownActiveCampaign($campaign_id, $occurrence_key)) {
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

    private function campaigns(array $language_ids = []): array {
        $legacy = [
            'status' => $this->config->get('module_cristale_shipping_notice_status'),
            'timezone' => $this->config->get('module_cristale_shipping_notice_timezone'),
            'starts_at' => $this->config->get('module_cristale_shipping_notice_starts_at'),
            'ends_at' => $this->config->get('module_cristale_shipping_notice_ends_at'),
            'banner_title' => $this->config->get('module_cristale_shipping_notice_banner_title'),
            'banner_message' => $this->config->get('module_cristale_shipping_notice_banner_message'),
            'banner_submessage' => $this->config->get('module_cristale_shipping_notice_banner_submessage'),
            'email_message' => $this->config->get('module_cristale_shipping_notice_email_message')
        ];

        if (!$language_ids) {
            $language_ids = [(int)$this->config->get('config_language_id')];
        }

        return \FurmediaScheduledPopupEngine::decodeCampaigns(
            (string)$this->config->get('module_cristale_shipping_notice_campaigns_json'),
            $language_ids,
            $legacy
        );
    }

    private function publicCampaign(array $campaign, array $occurrence, \DateTime $now): array {
        $content = $this->content($campaign);
        foreach ($content as $key => $value) {
            $content[$key] = \FurmediaScheduledPopupEngine::replaceShortcodes($value, $campaign, $occurrence, $this->storeName(), $now);
        }

        return [
            'id' => $campaign['id'],
            'occurrence_key' => $occurrence['key'],
            'title' => $content['title'],
            'message' => $content['message'],
            'submessage' => $content['submessage'],
            'thanks' => $content['thanks'],
            'button_text' => $content['button_text'],
            'countdown_label' => $content['countdown_label'],
            'image' => $campaign['image'] !== '' ? 'image/' . $campaign['image'] : 'extension/furmedia_scheduled_popup/catalog/view/image/shipping-notice-background.webp',
            'preset' => $campaign['preset'],
            'accent_color' => $campaign['accent_color'],
            'background_color' => $campaign['background_color'],
            'text_color' => $campaign['text_color'],
            'button_color' => $campaign['button_color'],
            'overlay_color' => $campaign['overlay_color'],
            'overlay_opacity' => (int)$campaign['overlay_opacity'],
            'blur' => (int)$campaign['blur'],
            'countdown' => !empty($campaign['countdown']) ? 1 : 0,
            'countdown_end' => $occurrence['end']->format(\DateTime::ATOM),
            'button_url' => $campaign['button_url'],
            'button_target' => $campaign['button_target']
        ];
    }

    private function content(array $campaign, ?int $language_id = null): array {
        $key = (string)($language_id ?? (int)$this->config->get('config_language_id'));
        if (isset($campaign['content'][$key])) {
            return $campaign['content'][$key];
        }

        $first = reset($campaign['content']);
        return is_array($first) ? $first : \FurmediaScheduledPopupEngine::defaultContent();
    }

    private function matchesPage(array $campaign): bool {
        if ($campaign['target_type'] === 'all') {
            return true;
        }

        $route = (string)($this->request->get['route'] ?? '');
        $product_id = (int)($this->request->get['product_id'] ?? 0);
        if ($campaign['target_type'] === 'products') {
            return $product_id > 0 && in_array($product_id, $campaign['target_products'], true);
        }

        $category_ids = [];
        if ($route === 'product/category' && !empty($this->request->get['path'])) {
            $category_ids = array_map('intval', explode('_', (string)$this->request->get['path']));
        }
        if ($product_id > 0) {
            foreach ($this->db->query("SELECT category_id FROM `" . DB_PREFIX . "product_to_category` WHERE product_id = '" . $product_id . "'")->rows as $row) {
                $category_ids[] = (int)$row['category_id'];
            }
        }

        return (bool)array_intersect($campaign['target_categories'], array_unique($category_ids));
    }

    private function matchesOrder(array $campaign, array $order_info): bool {
        if ($campaign['target_type'] === 'all') {
            return true;
        }
        if (empty($order_info['order_id'])) {
            return false;
        }

        $products = array_map(static fn(array $row): int => (int)$row['product_id'], $this->db->query("SELECT product_id FROM `" . DB_PREFIX . "order_product` WHERE order_id = '" . (int)$order_info['order_id'] . "'")->rows);
        if ($campaign['target_type'] === 'products') {
            return (bool)array_intersect($campaign['target_products'], $products);
        }
        if (!$products) {
            return false;
        }

        $categories = array_map(static fn(array $row): int => (int)$row['category_id'], $this->db->query("SELECT DISTINCT category_id FROM `" . DB_PREFIX . "product_to_category` WHERE product_id IN (" . implode(',', $products) . ")")->rows);
        return (bool)array_intersect($campaign['target_categories'], $categories);
    }

    private function knownActiveCampaign(string $campaign_id, string $occurrence_key): bool {
        $now = new \DateTime('now');
        foreach ($this->campaigns() as $campaign) {
            if ($campaign['id'] !== $campaign_id) {
                continue;
            }
            $occurrence = \FurmediaScheduledPopupEngine::activeOccurrence($campaign, $now);
            return (bool)($occurrence && $occurrence['key'] === $occurrence_key);
        }
        return false;
    }

    private function createStatsTable(): void {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "cristale_shipping_notice_stat` (`campaign_id` varchar(64) NOT NULL, `event_type` varchar(16) NOT NULL, `stat_date` date NOT NULL, `total` int(10) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`campaign_id`,`event_type`,`stat_date`)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4");
    }

    private function storeName(): string {
        return (string)($this->config->get('config_name') ?? '');
    }

    private function base64Json(mixed $value): string {
        return base64_encode(json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private function loadEngine(): void {
        if (!$this->engine_loaded) {
            require_once(DIR_EXTENSION . 'furmedia_scheduled_popup/system/library/furmedia_scheduled_popup.php');
            $this->engine_loaded = true;
        }
    }
}
