<?php

require_once FITET_MONITOR_DIR . 'admin/includes/class-fitet-monitor-page.php';
require_once FITET_MONITOR_DIR . 'admin/components/club-data/class-fitet-monitor-club-data-component.php';
require_once FITET_MONITOR_DIR . 'admin/components/club-details/class-fitet-monitor-club-details-component.php';

class Fitet_Monitor_Detail_Page extends Fitet_Monitor_Page {

	private $club;

	public function __construct($version, $plugin_name, $club) {
		parent::__construct($plugin_name, $version);
		$this->club = $club;
	}

	public function components() {

		return [
			'clubDetailsComponent' => new Fitet_Monitor_Club_Details_Component($this->plugin_name, $this->version)
		];
	}

	public function initialize_data() {
		return [
			'title' => $this->club['clubName'],
			'clubDetailsComponent' => $this->club? $this->components['clubDetailsComponent']->render($this->club) : '',
		];
	}


}
