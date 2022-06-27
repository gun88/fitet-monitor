<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';

class Fitet_Monitor_Club_Config_Component extends Fitet_Monitor_Component {


	function process_data($data) {

		$_data = [];
		$_data['clubCronLabel'] = __('Auto update');
		$_data['clubCron'] = $data ? $data['clubCron'] : '';

		return $_data;
	}
}
