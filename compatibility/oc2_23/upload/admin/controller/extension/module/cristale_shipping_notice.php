<?php
class ControllerExtensionModuleCristaleShippingNotice extends Controller {
    private $error = array();

    private $defaults = array(
        'module_cristale_shipping_notice_status' => 0,
        'module_cristale_shipping_notice_timezone' => 'UTC',
        'module_cristale_shipping_notice_starts_at' => '2026-01-01 00:00:00',
        'module_cristale_shipping_notice_ends_at' => '2026-01-02 00:00:00',
        'module_cristale_shipping_notice_banner_title' => 'Important announcement',
        'module_cristale_shipping_notice_banner_message' => 'Use this space for your scheduled announcement.',
        'module_cristale_shipping_notice_banner_submessage' => 'The popup automatically disappears after the end date.',
        'module_cristale_shipping_notice_email_message' => 'This message is added to order confirmation emails while the schedule is active.'
    );

    public function index() {
        $this->load->language('extension/module/cristale_shipping_notice');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_cristale_shipping_notice', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_signature'] = $this->language->get('text_signature');
        $data['text_banner_image_note'] = $this->language->get('text_banner_image_note');
        $data['text_preview_thanks'] = $this->language->get('text_preview_thanks');
        $data['text_performance_tools'] = $this->language->get('text_performance_tools');
        $data['text_performance_note'] = $this->language->get('text_performance_note');

        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_banner_preview'] = $this->language->get('entry_banner_preview');
        $data['entry_timezone'] = $this->language->get('entry_timezone');
        $data['entry_starts_at'] = $this->language->get('entry_starts_at');
        $data['entry_ends_at'] = $this->language->get('entry_ends_at');
        $data['entry_banner_title'] = $this->language->get('entry_banner_title');
        $data['entry_banner_message'] = $this->language->get('entry_banner_message');
        $data['entry_banner_submessage'] = $this->language->get('entry_banner_submessage');
        $data['entry_email_message'] = $this->language->get('entry_email_message');

        $data['help_datetime'] = $this->language->get('help_datetime');
        $data['help_ends_at'] = $this->language->get('help_ends_at');
        $data['help_email_message'] = $this->language->get('help_email_message');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_clear_cache'] = $this->language->get('button_clear_cache');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } elseif (isset($this->session->data['error_warning'])) {
            $data['error_warning'] = $this->session->data['error_warning'];
            unset($this->session->data['error_warning']);
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        if (isset($this->error['timezone'])) {
            $data['error_timezone'] = $this->error['timezone'];
        } else {
            $data['error_timezone'] = '';
        }

        if (isset($this->error['starts_at'])) {
            $data['error_starts_at'] = $this->error['starts_at'];
        } else {
            $data['error_starts_at'] = '';
        }

        if (isset($this->error['ends_at'])) {
            $data['error_ends_at'] = $this->error['ends_at'];
        } else {
            $data['error_ends_at'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/cristale_shipping_notice', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/module/cristale_shipping_notice', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
        $data['clear_cache'] = $this->url->link('extension/module/cristale_shipping_notice/clearCache', 'user_token=' . $this->session->data['user_token'], true);
        $catalog_url = defined('HTTPS_CATALOG') ? HTTPS_CATALOG : (defined('HTTP_CATALOG') ? HTTP_CATALOG : '../');
        $data['banner_preview'] = $catalog_url . 'catalog/view/theme/default/image/cristale_shipping_notice/shipping-notice-background.webp';

        foreach ($this->defaults as $key => $default) {
            if (isset($this->request->post[$key])) {
                $data[$key] = $this->request->post[$key];
            } elseif ($this->config->get($key) !== null) {
                $data[$key] = $this->config->get($key);
            } else {
                $data[$key] = $default;
            }
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/cristale_shipping_notice', $data));
    }

    public function clearCache() {
        $this->load->language('extension/module/cristale_shipping_notice');

        if (!$this->user->hasPermission('modify', 'extension/module/cristale_shipping_notice')) {
            $this->session->data['error_warning'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('extension/module/cristale_shipping_notice', 'user_token=' . $this->session->data['user_token'], true));
            return;
        }

        $deleted = 0;
        $paths = array();
        $cache_dir = defined('DIR_CACHE') ? DIR_CACHE : '';

        if ($cache_dir) {
            $paths[] = $cache_dir;
        }

        foreach (array_unique($paths) as $path) {
            $deleted += $this->clearCacheDirectory($path);
        }

        $warm = $this->warmHomepage();

        $this->session->data['success'] = sprintf($this->language->get('text_cache_success'), $deleted, $warm['status'], $warm['milliseconds']);
        $this->response->redirect($this->url->link('extension/module/cristale_shipping_notice', 'user_token=' . $this->session->data['user_token'], true));
    }

    public function install() {
        $this->load->language('extension/module/cristale_shipping_notice');
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('module_cristale_shipping_notice', $this->defaults);

        $this->db->query("DELETE FROM `" . DB_PREFIX . "modification` WHERE `code` = 'cristale_shipping_notice'");

        $this->db->query("INSERT INTO `" . DB_PREFIX . "modification` SET `extension_install_id` = '0', `name` = '" . $this->db->escape($this->language->get('heading_title')) . "', `code` = 'cristale_shipping_notice', `author` = 'Furmedia', `version` = '1.0.0', `link` = 'https://github.com/furmedia', `xml` = '" . $this->db->escape($this->getModificationXml()) . "', `status` = '1', `date_added` = NOW()");
    }

    public function uninstall() {
        $this->load->language('extension/module/cristale_shipping_notice');
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('module_cristale_shipping_notice');

        $this->db->query("DELETE FROM `" . DB_PREFIX . "modification` WHERE `code` = 'cristale_shipping_notice'");
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/cristale_shipping_notice')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!in_array($this->request->post['module_cristale_shipping_notice_timezone'], DateTimeZone::listIdentifiers())) {
            $this->error['timezone'] = $this->language->get('error_timezone');
        }

        $timezone = !empty($this->request->post['module_cristale_shipping_notice_timezone']) ? $this->request->post['module_cristale_shipping_notice_timezone'] : 'Europe/Bucharest';

        if (!$this->isValidDateTime($this->request->post['module_cristale_shipping_notice_starts_at'], $timezone)) {
            $this->error['starts_at'] = $this->language->get('error_datetime');
        }

        if (!$this->isValidDateTime($this->request->post['module_cristale_shipping_notice_ends_at'], $timezone)) {
            $this->error['ends_at'] = $this->language->get('error_datetime');
        }

        return !$this->error;
    }

    private function isValidDateTime($value, $timezone) {
        try {
            $date = DateTime::createFromFormat('Y-m-d H:i:s', $value, new DateTimeZone($timezone));
        } catch (Exception $e) {
            return false;
        }

        $errors = DateTime::getLastErrors();

        return $date && $date->format('Y-m-d H:i:s') === $value && ($errors === false || (!$errors['warning_count'] && !$errors['error_count']));
    }

    private function clearCacheDirectory($path) {
        $root = realpath($path);

        if (!$root || !is_dir($root)) {
            return 0;
        }

        $deleted = 0;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            $item_path = $item->getPathname();
            $basename = basename($item_path);

            if ($basename === 'index.html' || $basename === '.htaccess') {
                continue;
            }

            if ($item->isFile() && @unlink($item_path)) {
                $deleted++;
            }
        }

        return $deleted;
    }

    private function warmHomepage() {
        $url = defined('HTTPS_CATALOG') ? HTTPS_CATALOG : (defined('HTTP_CATALOG') ? HTTP_CATALOG : HTTP_SERVER);
        $started = microtime(true);
        $status = 0;

        if (function_exists('curl_init')) {
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3);
            curl_setopt($curl, CURLOPT_TIMEOUT, 8);
            curl_setopt($curl, CURLOPT_USERAGENT, 'Scheduled Popup Notice Pro cache warmer');
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_exec($curl);
            $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
        } else {
            $context = stream_context_create(array(
                'http' => array(
                    'method' => 'GET',
                    'timeout' => 8,
                    'header' => "User-Agent: Scheduled Popup Notice Pro cache warmer\r\n"
                )
            ));

            @file_get_contents($url, false, $context);

            if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $match)) {
                $status = (int)$match[1];
            }
        }

        return array(
            'status' => $status ? $status : 0,
            'milliseconds' => (int)round((microtime(true) - $started) * 1000)
        );
    }

    private function getModificationXml() {
        return '<?xml version="1.0" encoding="utf-8"?>
<modification>
  <name>Scheduled Popup &amp; Notice Pro</name>
  <code>cristale_shipping_notice</code>
  <version>1.0.0</version>
  <author>Furmedia</author>
  <link>https://github.com/furmedia</link>

  <file path="catalog/controller/common/footer.php">
    <operation error="skip">
      <search><![CDATA[$data[\'scripts\'] = $this->document->getScripts(\'footer\');]]></search>
      <add position="before"><![CDATA[
        $data[\'cristale_shipping_notice\'] = $this->load->controller(\'extension/module/cristale_shipping_notice\');
      ]]></add>
    </operation>
  </file>

  <file path="catalog/view/theme/*/template/common/footer.twig">
    <operation error="skip">
      <search><![CDATA[</body>]]></search>
      <add position="before"><![CDATA[
{% if cristale_shipping_notice %}{{ cristale_shipping_notice }}{% endif %}
      ]]></add>
    </operation>
  </file>

  <file path="catalog/controller/mail/order.php">
    <operation error="skip">
      <search><![CDATA[$data[\'text_greeting\'] = sprintf($language->get(\'text_greeting\'), $order_info[\'store_name\']);]]></search>
      <add position="after"><![CDATA[
            $cristale_shipping_notice_email = $this->load->controller(\'extension/module/cristale_shipping_notice/getEmailMessage\');

            if ($cristale_shipping_notice_email) {
                $data[\'text_greeting\'] .= "\n\n" . $cristale_shipping_notice_email;
            }
      ]]></add>
    </operation>
  </file>
</modification>';
    }
}
