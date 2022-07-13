<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/gun88
 * @since      1.0.0
 *
 * @package    Fitet_Monitor
 * @subpackage Fitet_Monitor/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Fitet_Monitor
 * @subpackage Fitet_Monitor/admin
 * @author     tpomante <gun88@hotmail.it>
 */
class Fitet_Monitor_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Fitet_Monitor_Router.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Fitet_Monitor_Router $router Fitet_Monitor_Router instance.
	 */
	private $router;

	/**
	 * Fitet_Monitor_Menu.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Fitet_Monitor_Menu $menu Fitet_Monitor_Menu instance.
	 */
	private $menu;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name
	 * @param string $version
	 * @param Fitet_Monitor_Router $router
	 * @param Fitet_Monitor_Menu $menu
	 */
	public function __construct($plugin_name, $version, $router, $menu) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->router = $router;
		$this->menu = $menu;
	}


	public function start() {
		add_action('load-toplevel_page_' . $this->plugin_name, [$this->router, 'on_load']);
		add_action('admin_menu', [$this->menu, 'initialize']);
		//require_once FITET_MONITOR_DIR . 'common/blocks/sample-block.php';
	}


}
