<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';


class Fitet_Monitor_Club_Data_Component extends Fitet_Monitor_Component {

	public function enqueue_scripts() {
		$file = plugin_dir_path(__FILE__) . 'autoComplete.min.js';
		Fitet_Monitor_Helper::enqueue_script('autoComplete.min.js', $file, [], $this->version, false);

		$file = (new ReflectionClass($this))->getFileName();
		$file = plugin_dir_path($file) . basename($file, '.php') . '.js';
		Fitet_Monitor_Helper::enqueue_script(get_class($this), $file, ['autoComplete.min.js', 'jquery', 'wp-api'], $this->version, false);
		wp_localize_script(get_class($this), 'FITET_MONITOR_CLUB_NO_LOGO', FITET_MONITOR_CLUB_NO_LOGO);
	}

	public function process_data($data) {


		$_data = [];
		$_data['clubCodeLabel'] = __('Club code', 'fitet-monitor');
		$_data['clubNameLabel'] = __('Club name', 'fitet-monitor');
		$_data['clubProvinceLabel'] = __('Club Province', 'fitet-monitor');
		$_data['clubLogoLabel'] = __('Club Logo', 'fitet-monitor');
		$_data['clubCronLabel'] = __('Auto Update', 'fitet-monitor');
		$_data['clubInfoLabel'] = __('Club information', 'fitet-monitor');
		$_data['placeholder'] = __('Search for a Club...', 'fitet-monitor');
		$_data['clubCode'] = $data && isset($data['clubCode']) ? $data['clubCode'] : '';
		$_data['clubName'] = $data && isset($data['clubName']) ? $data['clubName'] : '';
		$_data['clubProvince'] = $data && isset($data['clubProvince']) ? $data['clubProvince'] : '';
		$_data['clubCron'] = $data && isset($data['clubCron']) ? $data['clubCron'] : 'DEFAULT';
		$_data['clubNoLogo'] = FITET_MONITOR_CLUB_NO_LOGO;
		$_data['clubLogo'] = $data && isset($data['clubLogo']) ? $data['clubLogo'] : '';
		$_data['readonly'] = empty($_data['clubCode']) ? '' : 'readonly';
		$_data['action'] = $data && isset($data['clubCode']) ? 'edit' : 'add';

		$_data['manualConfigLabel'] = __('Manual Configuration', 'fitet-monitor');
		$_data['autoConfigLabel'] = __('Auto Configuration', 'fitet-monitor');
		$_data['submitButton'] = get_submit_button(__('Save', 'fitet-monitor'), 'primary large', 'submit', true, !($data && isset($data['clubCode'])) ? 'disabled' : '');


		return $_data;
	}
}
