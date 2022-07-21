<?php

require_once FITET_MONITOR_DIR . 'admin/includes/class-fitet-monitor-page.php';

class Fitet_Monitor_Server_Status extends Fitet_Monitor_Page {

	public function __construct($version, $plugin_name) {
		parent::__construct($plugin_name, $version);
	}

	public function initialize_data() {
		return [
			'status' => $this->server_status()
		];
	}

	private function server_status() {
		set_time_limit(300);
		$status = [];
		$status['max_execution_time'] = ini_get('max_execution_time');
		$status['memory_limit'] = ini_get('memory_limit');
		$status['pid'] = getmypid();
		$status['inode'] = getmyinode();
		$status['uniqid'] = uniqid();
		$status['uniqid2'] = uniqid();
		$status['uniqid3'] = uniqid();
		return json_encode($status, 128);
	}

}
