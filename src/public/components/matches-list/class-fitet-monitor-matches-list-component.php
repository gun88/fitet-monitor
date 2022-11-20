<?php

require_once FITET_MONITOR_DIR . 'public/includes/class-fitet-monitor-shortcode.php';
require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';

class Fitet_Monitor_Matches_List_Component extends Fitet_Monitor_Component {

	protected function components() {
		return [
			/*'playerCard' => new Fitet_Monitor_Player_Card_Component($this->plugin_name, $this->version, []),*/
			];
	}

	protected function process_data($data) {

		return '<code>'.json_encode($data,128).'</code>';
	}

}
