<?php

require_once FITET_MONITOR_DIR . 'admin/components/class-fitet-monitor-component.php';

abstract class Fitet_Monitor_Page extends Fitet_Monitor_Component {

	public abstract function render_page();

	/**
	 * @throws Exception
	 */
	protected final function render($data = []) {
		throw new Exception('Method not allowed on Fitet_Monitor_Page');
	}
}
