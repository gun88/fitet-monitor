<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';

class Fitet_Monitor_Component_Wrapper extends Fitet_Monitor_Component {

	private $instance;

	public static function render_wrapper($component, $data) {
		return $component->render($data);
	}

	public static function mock_render($component) {
		foreach ($component->components as $k=>$v) {
			$component->components[$k] = new Fitet_Monitor_Mock_Component($v->plugin_name,$v->version);
		}
	}

}

class Fitet_Monitor_Mock_Component extends Fitet_Monitor_Component {
	protected function process_data($data) {
		return '__mocked__';
	}


}
