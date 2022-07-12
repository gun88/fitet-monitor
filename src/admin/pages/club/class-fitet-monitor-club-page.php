<?php

require_once FITET_MONITOR_DIR . 'admin/includes/class-fitet-monitor-page.php';
require_once FITET_MONITOR_DIR . 'admin/components/club-data/class-fitet-monitor-club-data-component.php';
require_once FITET_MONITOR_DIR . 'admin/components/club-details/class-fitet-monitor-club-details-component.php';

class Fitet_Monitor_Club_Page extends Fitet_Monitor_Page {

	private $club;

	public function __construct($version, $plugin_name, $club) {
		parent::__construct($plugin_name, $version);
		$this->club = $club;
	}

	public function components() {

		return [
			'clubDataComponent' => new Fitet_Monitor_Club_Data_Component($this->plugin_name, $this->version),
		];
	}

	public function initialize_data() {
		return [
			'title' => __("Club Page", 'fitet-monitor'),
			'clubDataComponent' => $this->components['clubDataComponent']->render($this->club),
			'messagePool' => $this->prepare_messages(),
		];
	}


	public function prepare_messages() {
		$messagePool = '';
		if (isset($_GET['message']) && 'already_exist' === $_GET['message']) {
			$messagePool = '<div id="message" class="notice notice-error is-dismissible"><p>' . __('Chosen club already exist', 'fitet-monitor') . '</p></div>';
		}
		if (isset($_GET['message']) && 'invalid_club' === $_GET['message']) {
			$messagePool = '<div id="message" class="notice notice-error is-dismissible"><p>' . __('Invalid club code', 'fitet-monitor') . '</p></div>';
		}
		return $messagePool;
	}
}
