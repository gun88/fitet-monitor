<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';

class Fitet_Monitor_Component_Wrapper extends Fitet_Monitor_Component {

	private $instance;

	public static function render_wrapper($component, $data) {
		return $component->render($data);
	}
}
