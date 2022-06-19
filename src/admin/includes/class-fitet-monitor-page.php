<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';

class Fitet_Monitor_Page extends Fitet_Monitor_Component {

	public function render_page() {
		echo parent::render();
	}

	public function initialize_data() {
		return [];
	}

	protected final function process_data($data = []) {
		return $this->initialize_data();
	}
}
