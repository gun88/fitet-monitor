<?php

require_once FITET_MONITOR_DIR . 'admin/includes/class-fitet-monitor-page.php';

class Fitet_Monitor_Club_Page extends Fitet_Monitor_Page {

	private $club_code;

	public function __construct($version, $plugin_name, $club_code) {
		parent::__construct($plugin_name, $version);
		$this->club_code = $club_code;
	}

	public function components() {
		/*return [
			'clubComponent' => new Fitet_Monitor_Table_Component($this->version, $this->club_code),
			'configurationComponent' => new Fitet_Monitor_Table_Component($this->version, $this->club_code)
		];*/
	}

	public function initialize_data() {
		return [
			'title' => __("Add Club", 'fitet-monitor'),
			'clubComponent' => '',
			'configurationComponent' => '',
			'submitButton' => get_submit_button(__('Save', 'fitet-monitor'))];
	}
}
