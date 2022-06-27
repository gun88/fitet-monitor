<?php

class Fitet_Monitor_Router {

	private $plugin_name;
	private $version;
	private $page;
	/**
	 * @var Fitet_Monitor_Manager
	 */
	private $manager;


	public function __construct($plugin_name, $version, $manager) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->manager = $manager;

	}

	public function on_load() {

		$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;


		if ($action == 'add') {

			if (!$this->manager->club_exist($_POST['clubCode'])) {
				wp_safe_redirect(add_query_arg(['mode' => 'club', 'message' => 'invalid_club'], menu_page_url('fitet-monitor', false)));
				exit();
			}

			if ($this->manager->get_club($_POST['clubCode'])) {
				wp_safe_redirect(add_query_arg(['mode' => 'club', 'message' => 'already_exist'], menu_page_url('fitet-monitor', false)));
				exit();
			}

			$this->manager->add_club([
				'clubCode' => $_POST['clubCode'],
				'clubName' => $_POST['clubName'],
				'clubProvince' => $_POST['clubProvince'],
				'clubLogo' => $_POST['clubLogo'],
				'clubCron' => $_POST['clubCron']
			]);

			wp_safe_redirect(add_query_arg(['message' => 'added'], menu_page_url('fitet-monitor', false)));
			exit();
		}

		if ($action == 'edit') {
			$this->manager->edit_club([
				'clubCode' => $_POST['clubCode'],
				'clubName' => $_POST['clubName'],
				'clubProvince' => $_POST['clubProvince'],
				'clubLogo' => $_POST['clubLogo'],
				'clubCron' => $_POST['clubCron']
			]);
			wp_safe_redirect(add_query_arg(['message' => 'edited'], menu_page_url('fitet-monitor', false)));
			exit();
		}

		if ($action == 'delete') {
			$this->manager->delete_clubs($_REQUEST['clubCode']);
			wp_safe_redirect(add_query_arg(['message' => 'deleted'], menu_page_url('fitet-monitor', false)));
			exit();
		}


		$mode = isset($_GET['mode']) ? $_GET['mode'] : null;
		$club_code = isset($_GET['clubCode']) ? $_GET['clubCode'] : null;

		switch ($mode) {
			case 'advanced':
				require_once FITET_MONITOR_DIR . 'admin/pages/advanced/class-fitet-monitor-advanced-page.php';
				$this->page = new Fitet_Monitor_Advanced_Page($this->plugin_name, $this->version);
				break;
			case 'club':
				if ($club_code) {
					$club = $this->manager->get_club($club_code);
					$club['status'] = $this->manager->get_status($club_code)['status'];
				} else {
					$club = null;
				}
				require_once FITET_MONITOR_DIR . 'admin/pages/club/class-fitet-monitor-club-page.php';
				$this->page = new Fitet_Monitor_Club_Page($this->version, $this->plugin_name, $club);
				break;
			case 'summary':
			default:
				$clubs = $this->manager->get_clubs();
				foreach ($clubs as &$club) {
					$club['status'] = $this->manager->get_status($club['clubCode'])['status'];
				}

				require_once FITET_MONITOR_DIR . 'admin/pages/summary/class-fitet-monitor-summary-page.php';
				$this->page = new Fitet_Monitor_Summary_Page($this->plugin_name, $this->version, $clubs);
				break;
		}

		add_action('admin_enqueue_scripts', [$this->page, 'initialize']);

	}

	public function render_page() {
		$this->page->render_page();
	}
}
