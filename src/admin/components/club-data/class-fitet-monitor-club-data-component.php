<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';


class Fitet_Monitor_Club_Data_Component extends Fitet_Monitor_Component {

	public function enqueue_scripts() {
		$file = plugin_dir_path(__FILE__) . 'autoComplete.min.js';
		Fitet_Monitor_Helper::enqueue_script('autoComplete.min.js', $file, [], $this->version, false);

		$file = (new ReflectionClass($this))->getFileName();
		$file = plugin_dir_path($file) . basename($file, '.php') . '.js';
		Fitet_Monitor_Helper::enqueue_script(get_class($this), $file, ['autoComplete.min.js', 'jquery', 'wp-api'], $this->version, false);
	}

	public function process_data($data) {


		$_data = [];
		$_data['clubCodeLabel'] = __('Club code');
		$_data['clubNameLabel'] = __('Club name');
		$_data['clubProvinceLabel'] = __('Club Province');
		$_data['clubInfoLabel'] = __('Club information');
		$_data['placeholder'] = __('Search for a Club...');
		$_data['clubCode'] = $data && isset($data['clubCode']) ? $data['clubCode'] : '';
		$_data['clubName'] = $data && isset($data['clubName']) ? $data['clubName'] : '';
		$_data['clubProvince'] = $data && isset($data['clubProvince']) ? $data['clubProvince'] : '';
		$_data['clubNoLogo'] = FITET_MONITOR_CLUB_NO_LOGO;
		$_data['clubLogo'] = $data && isset($data['clubLogo']) ? 'http://portale.fitet.org/images/societa/' . $data['clubCode'] . '.jpg' : '';
		$_data['readonly'] = empty($_data['clubCode']) ? '' : 'readonly';


		return $_data;
	}
}
