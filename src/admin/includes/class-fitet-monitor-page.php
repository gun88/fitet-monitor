<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';

abstract class Fitet_Monitor_Page extends Fitet_Monitor_Component {

	public final function render_page() {
		echo parent::render();
	}

	public function initialize_data() {
		return [];
	}

	/**
	 * @throws Exception
	 */
	protected final function render($data = []) {
		throw new Exception('[render] - Method not allowed on Fitet_Monitor_Page');
	}

	protected final function process_data($data = []) {
		return $this->initialize_data();
	}
}
