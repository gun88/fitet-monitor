<?php


class Fitet_Monitor_Manager_Logger {
	private $progress =0;
	private $plugin_name;
	private $version;

	/**
	 * @param string $plugin_name
	 * @param string $version
	 */
	public function __construct($plugin_name, $version) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}


	public function get_status($club_code) {
		$status_log = get_transient($this->plugin_name . $club_code);
		return !$status_log ? ['logs' => [], 'status' => 'ready'] : $status_log;
	}

	public function add_status($club_code, $message, $progress = 0, $status = 'updating') {

		$this->progress += $progress;
		$status_log = $this->get_status($club_code);
		$status_log['status'] = $status;
		$status_log['logs'][] = ['message' => $message, 'progress' => round($this->progress)];
		set_transient($this->plugin_name . $club_code, $status_log, 600);
	}

	public function reset_status($club_code) {
		$this->progress = 0;
		delete_transient($this->plugin_name . $club_code);
		$status_log = $this->get_status($club_code);
		$status_log['status'] = 'new';
		$status_log['logs'] = [];
		set_transient($this->plugin_name . $club_code, $status_log, 600);
	}

	public function set_completed($club_code, $message) {
		$this->progress = 100;
		$status_log = $this->get_status($club_code);
		$status_log['status'] = 'ready';
		$status_log['logs'][] = ['message' => $message, 'progress' => round($this->progress)];
		set_transient($this->plugin_name . $club_code, $status_log, 600);
	}

}
