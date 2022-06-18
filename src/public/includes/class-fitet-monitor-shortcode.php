<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';

abstract class Fitet_Monitor_Shortcode extends Fitet_Monitor_Component {

	public abstract function render_shortcode($attributes, $content = null);

	/**
	 * @throws Exception
	 */
	protected final function render($data = []) {
		throw new Exception('Method not allowed on Fitet_Monitor_Shortcode');
	}
}
