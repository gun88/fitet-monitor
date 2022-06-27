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
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 * @since 1.0.0
	 */
	public function __construct($plugin_name, $version) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}


	public function start() {
		require_once FITET_MONITOR_DIR . 'admin/includes/class-fitet-monitor-manager-logger.php';
		$logger = new  Fitet_Monitor_Manager_Logger($this->plugin_name, $this->version);

		require_once FITET_MONITOR_DIR . 'admin/services/class-fitet-monitor-manager.php';
		$manager = new  Fitet_Monitor_Manager($this->plugin_name, $this->version, $logger);

		require_once FITET_MONITOR_DIR . 'admin/router/class-fitet-monitor-router.php';
		$router = new Fitet_Monitor_Router($this->plugin_name, $this->version, $manager);
		add_action('load-toplevel_page_' . $this->plugin_name, [$router, 'on_load']);

		require_once FITET_MONITOR_DIR . 'admin/menu/class-fitet-monitor-menu.php';
		$menu = new Fitet_Monitor_Menu($router, $this->plugin_name);
		add_action('admin_menu', [$menu, 'initialize']);

		require_once FITET_MONITOR_DIR . 'common/blocks/sample-block.php';
	}


}
