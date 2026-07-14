<?php
namespace Opencart\Catalog\Controller\Extension\FurmediaScheduledPopup\Module;

class ScheduledPopup extends \Opencart\System\Engine\Controller {
	private array $defaults = [
		'status' => 0,
		'timezone' => 'UTC',
		'starts_at' => '2026-01-01 00:00:00',
		'ends_at' => '2026-01-02 00:00:00',
		'banner_title' => 'Important announcement',
		'banner_message' => 'Use this space for your scheduled announcement.',
		'banner_submessage' => 'The popup automatically disappears after the end date.',
		'email_message' => 'This message is added to order confirmation emails while the schedule is active.'
	];

	public function index(): string {
		$this->load->language('extension/furmedia_scheduled_popup/module/scheduled_popup');
		$notice = $this->getNotice();
		if (!$this->isActive($notice)) {
			return '';
		}
		$data = [
			'image' => 'extension/furmedia_scheduled_popup/catalog/view/image/shipping-notice-background.webp',
			'title' => $notice['banner_title'],
			'message' => $notice['banner_message'],
			'submessage' => $notice['banner_submessage'],
			'close_label' => $this->language->get('text_close'),
			'thanks' => $this->language->get('text_thanks')
		];
		return $this->load->view('extension/furmedia_scheduled_popup/module/scheduled_popup', $data);
	}

	public function footerAfter(string &$route, array &$data, string &$output): void {
		if (strpos($output, 'data-furmedia-scheduled-popup') !== false) {
			return;
		}
		$html = $this->index();
		if (!$html) {
			return;
		}
		$position = strripos($output, '</body>');
		if ($position !== false) {
			$output = substr($output, 0, $position) . $html . substr($output, $position);
		} else {
			$output .= $html;
		}
	}

	public function mailAddBefore(string &$route, array &$args): void {
		$notice = $this->getNotice();
		if (!$this->isActive($notice) || !isset($args[2])) {
			return;
		}
		$message = trim((string)$notice['email_message']);
		if ($message && strpos((string)$args[2], $message) === false) {
			$args[2] = trim((string)$args[2] . "\n\n" . $message);
		}
	}

	private function getNotice(): array {
		$notice = [];
		foreach ($this->defaults as $key => $default) {
			$value = $this->config->get('module_cristale_shipping_notice_' . $key);
			$notice[$key] = $value !== null ? $value : $default;
		}
		return $notice;
	}

	private function isActive(array $notice): bool {
		if (empty($notice['status'])) {
			return false;
		}
		try {
			$timezone = new \DateTimeZone($notice['timezone']);
			$now = new \DateTime('now', $timezone);
			$starts = new \DateTime($notice['starts_at'], $timezone);
			$ends = new \DateTime($notice['ends_at'], $timezone);
		} catch (\Exception $exception) {
			return false;
		}
		return $now >= $starts && $now < $ends;
	}
}
