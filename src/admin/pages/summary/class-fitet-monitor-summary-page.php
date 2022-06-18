<?php

require_once FITET_MONITOR_DIR . 'admin/includes/class-fitet-monitor-page.php';
require_once FITET_MONITOR_DIR . 'admin/components/alfa/class-fitet-monitor-alfa-component.php';

class Fitet_Monitor_Summary_Page extends Fitet_Monitor_Page {

	public function components() {
		return ['alfa' => new Fitet_Monitor_Alfa_Component($this->version)];
	}

	public function initialize_data() {
		return [
			'time' => date("h:i:sa"),
			'alfaContent' => $this->components['alfa']->render([
					'contentAlfa' => 'Alfettino!!! $$$',
					'betas' => 3,
				]
			)];
	}

}
