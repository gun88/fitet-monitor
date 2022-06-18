<?php

class Fitet_Monitor_Router {

	private $plugin_name;
	private $version;
	private $page;


	public function __construct($plugin_name, $version) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	public function on_load() {

		$action = isset($_POST['action']) ? $_POST['action'] : null;

		if ($action == 'save') {
			update_option('fitet-monitor-club-code', $_POST['clubCode']);
			wp_safe_redirect(add_query_arg(
				[
					'message' => 'saved',
					'mode' => 'club',
				], menu_page_url('fitet-monitor', false)));
			exit();


		}


		$mode = isset($_GET['mode']) ? $_GET['mode'] : null;
		$club_code = isset($_GET['clubCode']) ? $_GET['clubCode'] : null;

		switch ($mode) {
			case 'advanced':
				require_once FITET_MONITOR_DIR . 'admin/pages/advanced/class-fitet-monitor-advanced-page.php';
				$this->page = new Fitet_Monitor_Advanced_Page($this->version, $club_code);
				break;
			case 'club':
				require_once FITET_MONITOR_DIR . 'admin/pages/club/class-fitet-monitor-club-page.php';
				$club_code = get_option('fitet-monitor-club-code');
				$this->page = new Fitet_Monitor_Club_Page($this->version, $this->plugin_name, $club_code);
				break;
			case 'summary':
			default:
				require_once FITET_MONITOR_DIR . 'admin/pages/summary/class-fitet-monitor-summary-page.php';
				$this->page = new Fitet_Monitor_Summary_Page($this->version);
				break;
		}

		add_action('admin_enqueue_scripts', [$this->page, 'initialize']);

		if (isset($_GET['myVal'])) {
			update_option('my-op', $_GET['myVal']);
			wp_safe_redirect(add_query_arg(['message' => 'saved'], menu_page_url('fitet-monitor', false)));
			exit();
		}
	}

	public function render_page() {
		$this->page->render_page();
	}
}
