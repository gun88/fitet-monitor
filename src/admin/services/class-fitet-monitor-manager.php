<?php

class Fitet_Monitor_Manager {

	private $plugin_name;
	private $version;
	/**
	 * @var Fitet_Monitor_Manager_Logger
	 */
	protected $logger;


	/**
	 * @param string $plugin_name
	 * @param string $version
	 * @param Fitet_Monitor_Manager_Logger $version
	 */
	public function __construct($plugin_name, $version, $logger) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->logger = $logger;
	}

	public function add_club($club) {
		$this->save_club($club);
		$this->logger->reset_status($club['clubCode']);
	}

	public function edit_club($club) {
		$this->save_club($club);
	}

	private function save_club($club) {

		if (empty($club['clubCode']))
			throw new Exception("empty club code");

		$club['clubName'] = stripslashes($club['clubName']);

		$club_code = $club['clubCode'];
		$club_codes = get_option($this->plugin_name . 'clubs', []);
		$club_codes[] = $club_code;
		update_option($this->plugin_name . 'clubs', array_filter(array_unique($club_codes)));
		update_option($this->plugin_name . $club_code, $club);

	}

	public function delete_clubs($club_codes) {
		if (!is_array($club_codes))
			$club_codes = [$club_codes];

		$all = get_option($this->plugin_name . 'clubs');
		$toRemove = $club_codes;
		$result = array_diff($all, $toRemove);

		update_option($this->plugin_name . 'clubs', $result);
		foreach ($toRemove as $club_code) {
			delete_option($this->plugin_name . $club_code);
		}
	}

	public function get_club($club_code) {
		$club = get_option($this->plugin_name . $club_code);
		$club['status'] = $this->logger->get_status($club_code)['status'];
		return $club;
	}

	public function get_clubs() {
		$clubCodes = get_option($this->plugin_name . 'clubs');
		if (!$clubCodes) {
			return [];
		}
		return array_map(function ($clubCode) {
			return $this->get_club($clubCode);
		}, $clubCodes);
	}

}
