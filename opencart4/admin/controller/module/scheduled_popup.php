<?php
namespace Opencart\Admin\Controller\Extension\FurmediaScheduledPopup\Module;

class ScheduledPopup extends \Opencart\System\Engine\Controller {
	private array $error = [];

	private array $defaults = [
		'module_cristale_shipping_notice_status' => 0,
		'module_cristale_shipping_notice_timezone' => 'UTC',
		'module_cristale_shipping_notice_starts_at' => '2026-01-01 00:00:00',
		'module_cristale_shipping_notice_ends_at' => '2026-01-02 00:00:00',
		'module_cristale_shipping_notice_banner_title' => 'Important announcement',
		'module_cristale_shipping_notice_banner_message' => 'Use this space for your scheduled announcement.',
		'module_cristale_shipping_notice_banner_submessage' => 'The popup automatically disappears after the end date.',
		'module_cristale_shipping_notice_email_message' => 'This message is added to order confirmation emails while the schedule is active.'
	];

	public function index(): void {
		$this->load->language('extension/furmedia_scheduled_popup/module/scheduled_popup');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('setting/setting');

		if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {
			$this->model_setting_setting->editSetting('module_cristale_shipping_notice', $this->request->post);
			$this->syncEvents();
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		$data = [];
		$language_keys = [
			'heading_title', 'text_edit', 'text_enabled', 'text_disabled', 'text_signature',
			'text_banner_image_note', 'text_preview_thanks', 'text_performance_tools', 'text_performance_note',
			'entry_status', 'entry_banner_preview', 'entry_timezone', 'entry_starts_at', 'entry_ends_at',
			'entry_banner_title', 'entry_banner_message', 'entry_banner_submessage', 'entry_email_message',
			'help_datetime', 'help_ends_at', 'help_email_message', 'button_save', 'button_cancel', 'button_clear_cache'
		];
		foreach ($language_keys as $key) {
			$data[$key] = $this->language->get($key);
		}

		$data['error_warning'] = $this->error['warning'] ?? ($this->session->data['error_warning'] ?? '');
		unset($this->session->data['error_warning']);
		$data['success'] = $this->session->data['success'] ?? '';
		unset($this->session->data['success']);
		$data['error_timezone'] = $this->error['timezone'] ?? '';
		$data['error_starts_at'] = $this->error['starts_at'] ?? '';
		$data['error_ends_at'] = $this->error['ends_at'] ?? '';

		$token = 'user_token=' . $this->session->data['user_token'];
		$data['breadcrumbs'] = [
			['text' => $this->language->get('text_home'), 'href' => $this->url->link('common/dashboard', $token, true)],
			['text' => $this->language->get('text_extension'), 'href' => $this->url->link('marketplace/extension', $token . '&type=module', true)],
			['text' => $this->language->get('heading_title'), 'href' => $this->url->link('extension/furmedia_scheduled_popup/module/scheduled_popup', $token, true)]
		];
		$data['action'] = $this->url->link('extension/furmedia_scheduled_popup/module/scheduled_popup', $token, true);
		$data['cancel'] = $this->url->link('marketplace/extension', $token . '&type=module', true);
		$data['clear_cache'] = $this->url->link('extension/furmedia_scheduled_popup/module/scheduled_popup.clearCache', $token, true);
		$data['banner_preview'] = (defined('HTTPS_CATALOG') ? HTTPS_CATALOG : HTTP_CATALOG) . 'extension/furmedia_scheduled_popup/catalog/view/image/shipping-notice-background.webp';

		foreach ($this->defaults as $key => $default) {
			$data[$key] = $this->request->post[$key] ?? ($this->config->get($key) ?? $default);
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		$this->response->setOutput($this->load->view('extension/furmedia_scheduled_popup/module/scheduled_popup', $data));
	}

	public function install(): void {
		$this->load->model('setting/setting');
		$this->model_setting_setting->editSetting('module_cristale_shipping_notice', $this->defaults);
		$this->syncEvents();
	}

	public function uninstall(): void {
		$this->load->model('setting/setting');
		$this->model_setting_setting->deleteSetting('module_cristale_shipping_notice');
		$this->load->model('setting/event');
		foreach ($this->getEvents() as $event) {
			$this->model_setting_event->deleteEventByCode($event['code']);
		}
	}

	public function clearCache(): void {
		if (!$this->user->hasPermission('modify', 'extension/furmedia_scheduled_popup/module/scheduled_popup')) {
			$this->session->data['error_warning'] = $this->language->get('error_permission');
		} elseif (defined('DIR_CACHE') && is_dir(DIR_CACHE)) {
			$this->clearCacheDirectory(DIR_CACHE);
			$this->session->data['success'] = sprintf($this->language->get('text_cache_success'), 0, 0, 0);
		}
		$this->response->redirect($this->url->link('extension/furmedia_scheduled_popup/module/scheduled_popup', 'user_token=' . $this->session->data['user_token'], true));
	}

	private function syncEvents(): void {
		$this->load->model('setting/event');
		foreach ($this->getEvents() as $event) {
			$this->model_setting_event->deleteEventByCode($event['code']);
			$this->model_setting_event->addEvent($event);
		}
	}

	private function getEvents(): array {
		return [
			[
				'code' => 'furmedia_scheduled_popup_footer',
				'description' => 'Scheduled Popup Notice Pro storefront popup',
				'trigger' => 'catalog/view/common/footer/after',
				'action' => 'extension/furmedia_scheduled_popup/module/scheduled_popup.footerAfter',
				'status' => 1,
				'sort_order' => 0
			],
			[
				'code' => 'furmedia_scheduled_popup_mail',
				'description' => 'Scheduled Popup Notice Pro order email message',
				'trigger' => 'catalog/controller/mail/order/add/before',
				'action' => 'extension/furmedia_scheduled_popup/module/scheduled_popup.mailAddBefore',
				'status' => 1,
				'sort_order' => 0
			]
		];
	}

	protected function validate(): bool {
		if (!$this->user->hasPermission('modify', 'extension/furmedia_scheduled_popup/module/scheduled_popup')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		$timezone = $this->request->post['module_cristale_shipping_notice_timezone'] ?? '';
		if (!in_array($timezone, \DateTimeZone::listIdentifiers(), true)) {
			$this->error['timezone'] = $this->language->get('error_timezone');
		}
		if (!$this->isValidDateTime($this->request->post['module_cristale_shipping_notice_starts_at'] ?? '', $timezone)) {
			$this->error['starts_at'] = $this->language->get('error_datetime');
		}
		if (!$this->isValidDateTime($this->request->post['module_cristale_shipping_notice_ends_at'] ?? '', $timezone)) {
			$this->error['ends_at'] = $this->language->get('error_datetime');
		}
		return !$this->error;
	}

	private function isValidDateTime(string $value, string $timezone): bool {
		try {
			$date = \DateTime::createFromFormat('Y-m-d H:i:s', $value, new \DateTimeZone($timezone));
		} catch (\Exception $exception) {
			return false;
		}
		$errors = \DateTime::getLastErrors();
		return $date && $date->format('Y-m-d H:i:s') === $value && ($errors === false || (!$errors['warning_count'] && !$errors['error_count']));
	}

	private function clearCacheDirectory(string $path): void {
		$root = realpath($path);
		if (!$root || !is_dir($root)) {
			return;
		}
		$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
		foreach ($iterator as $item) {
			if ($item->isFile() && !in_array($item->getBasename(), ['index.html', '.htaccess'], true)) {
				@unlink($item->getPathname());
			}
		}
	}
}
