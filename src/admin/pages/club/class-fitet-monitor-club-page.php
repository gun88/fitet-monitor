<?php

require_once FITET_MONITOR_DIR . 'admin/includes/class-fitet-monitor-page.php';

class Fitet_Monitor_Club_Page extends Fitet_Monitor_Page {

	private $club_code;
	private $plugin_name;

	public function __construct($version, $plugin_name, $club_code) {
		parent::__construct($version);
		$this->club_code = $club_code;
		$this->plugin_name = $plugin_name;
	}

	public function initialize_data() {
		return [
			'title' => __("Club Page", $this->plugin_name, 'fitet-monitor'),
			'custom' => __("Custom!", $this->plugin_name, 'fitet-monitor'),
			'clubCode' => $this->club_code,
			'saveLabel' => __("Save", 'fitet-monitor'),

		];
	}
}
