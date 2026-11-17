<?php

require_once FITET_MONITOR_DIR . 'public/utils/class-fitet-monitor-utils.php';

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

		add_filter('query_vars', function ($vars) {
			$vars[] = "team";
			$vars[] = "player";
			$vars[] = "championship";
			$vars[] = "clubName";
			return $vars;
		});

	}

	public function on_load() {

		$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;

		if ($action === 'saveProxy' && $_SERVER['REQUEST_METHOD'] === 'POST') {
			check_admin_referer('fitet_monitor_save_proxy');
			if (!current_user_can('edit_pages')) {
				wp_die(__('You are not allowed to edit these settings.', 'fitet-monitor'));
			}
			$url = trim(sanitize_text_field(wp_unslash($_POST['proxyUrl'] ?? '')));
			$parts = wp_parse_url($url);
			$valid = $url === '' || (is_array($parts)
				&& in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
				&& !empty($parts['host'])
				&& empty($parts['user']) && empty($parts['pass'])
				&& empty($parts['query']) && empty($parts['fragment'])
				&& (!isset($parts['path']) || $parts['path'] === '/'));
			if ($valid) {
				update_option('fitet_monitor_proxy_url', rtrim($url, '/'));
			}
			wp_safe_redirect(add_query_arg(['message' => $valid ? 'proxy_saved' : 'invalid_proxy'], menu_page_url('fitet-monitor', false)));
			exit();
		}


		if ($action == 'add') {

			if (!$this->manager->club_exist($_POST['clubCode'])) {
				wp_safe_redirect(add_query_arg(['mode' => 'club', 'message' => 'invalid_club'], menu_page_url('fitet-monitor', false)));
				exit();
			}

			if ($this->manager->club_already_stored($_POST['clubCode'])) {
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

		if ($action == 'resetStatus') {
			$this->manager->resetStatus($_REQUEST['clubCode']);
			wp_safe_redirect(add_query_arg(['message' => 'reset'], menu_page_url('fitet-monitor', false)));
			exit();
		}


		$mode = isset($_GET['mode']) ? $_GET['mode'] : null;
		$club_code = isset($_GET['clubCode']) ? $_GET['clubCode'] : null;

		switch ($mode) {
			case 'club':
				if ($club_code) {
					$template = [
						'clubCode' => '',
						'clubProvince' => '',
						'clubName' => '',
						'clubLogo' => '',
						'lastUpdate' => '',
						'clubCron' => '',
					];
					$club = $this->manager->get_club($club_code, $template);
					$club['status'] = $this->manager->get_status($club_code)['status'];
				} else {
					$club = null;
				}
				require_once FITET_MONITOR_DIR . 'admin/pages/club/class-fitet-monitor-club-page.php';
				$this->page = new Fitet_Monitor_Club_Page($this->version, $this->plugin_name, $club);
				break;
			case 'detail':
				if ($club_code) {
					$club = $this->manager->get_club($club_code);
					$club['status'] = $this->manager->get_status($club_code)['status'];
				} else {
					$club = null;
				}
				require_once FITET_MONITOR_DIR . 'admin/pages/detail/class-fitet-monitor-detail-page.php';
				$this->page = new Fitet_Monitor_Detail_Page($this->version, $this->plugin_name, $club);
				break;
			case 'summary':
			default:
				$template = [
					'clubCode' => '',
					'clubProvince' => '',
					'clubName' => '',
					'clubLogo' => '',
					'lastUpdate' => '',
				];
				$clubs = $this->manager->get_clubs($template);
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
		memory_dump();
	}
}

