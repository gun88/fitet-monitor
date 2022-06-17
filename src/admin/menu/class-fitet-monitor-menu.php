<?php

class Fitet_Monitor_Menu {

	/**
	 * @var Fitet_Monitor_Router
	 */
	private $router;

	/**
	 * @var string
	 */
	private $plugin_name;

	public function __construct($router, $plugin_name) {
		$this->router = $router;
		$this->plugin_name = $plugin_name;
	}

	public function initialize() {
		$menu_icon = plugin_dir_url(__FILE__) . 'menu_icon.png';

		add_menu_page(
			__('Fitet Monitor', $this->plugin_name, 'fitet-monitor'),
			__('Fitet Monitor', $this->plugin_name, 'fitet-monitor'),
			'manage_options',
			$this->plugin_name,
			[$this->router, 'render_page'],
			$menu_icon
		);
	}
}
