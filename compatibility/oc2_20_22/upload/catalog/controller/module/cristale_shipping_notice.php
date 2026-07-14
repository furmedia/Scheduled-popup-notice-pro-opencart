<?php
class ControllerModuleCristaleShippingNotice extends Controller {
    private $defaults = array(
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
        $this->load->language('module/cristale_shipping_notice');
        $this->addHttpsSecurityHeader();

        $notice = $this->getNotice();

        if (!$this->isActive($notice)) {
            return '';
        }

        $data = array(
            'image' => 'catalog/view/theme/default/image/cristale_shipping_notice/shipping-notice-background.webp',
            'title' => $notice['banner_title'],
            'message' => $notice['banner_message'],
            'submessage' => $notice['banner_submessage'],
            'close_label' => $this->language->get('text_close'),
            'thanks' => $this->language->get('text_thanks')
        );

        return $this->load->view('module/cristale_shipping_notice', $data);
    }

    public function getEmailMessage() {
        $notice = $this->getNotice();

        return $this->isActive($notice) ? $notice['email_message'] : '';
    }

    private function getNotice() {
        $notice = array();

        foreach ($this->defaults as $key => $default) {
            $setting_key = 'module_cristale_shipping_notice_' . $key;
            $value = $this->config->get($setting_key);

            $notice[$key] = $value !== null ? $value : $default;
        }

        return $notice;
    }

    private function isActive($notice) {
        if (empty($notice['status'])) {
            return false;
        }

        try {
            $timezone = new DateTimeZone($notice['timezone']);
            $now = new DateTime('now', $timezone);
            $starts = new DateTime($notice['starts_at'], $timezone);
            $ends = new DateTime($notice['ends_at'], $timezone);
        } catch (Exception $e) {
            return false;
        }

        return $now >= $starts && $now < $ends;
    }

    private function addHttpsSecurityHeader() {
        $this->response->addHeader('Strict-Transport-Security: max-age=31536000');
    }
}
