<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/gun88
 * @since      1.0.0
 *
 * @package    Fitet_Monitor
 * @subpackage Fitet_Monitor/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Fitet_Monitor
 * @subpackage Fitet_Monitor/includes
 * @author     tpomante <gun88@hotmail.it>
 */
class Fitet_Monitor {


	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	private $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of the plugin.
	 */
	private $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct($version, $plugin_name) {

		$this->version = $version;
		$this->plugin_name = $plugin_name;


	}


	/**
	 * Plugin activation
	 *
	 * The code that runs during plugin activation.
	 * This action is documented in includes/class-fitet-monitor-activator.php
	 *
	 * @since    1.0.0
	 */
	public function activate() {
		error_log("################");
		error_log("#   ACTIVATE   #");
		error_log("################");
		update_option('fitet-monitor-club-code', 'my default value');
	}

	/**
	 * Plugin deactivation
	 *
	 * The code that runs during plugin deactivation.
	 * This action is documented in includes/class-fitet-monitor-deactivator.php
	 *
	 * @since    1.0.0
	 */
	public function deactivate() {
		error_log("################");
		error_log("#  DEACTIVATE  #");
		error_log("################");
		delete_option('fitet-monitor-demo');
	}

	/**
	 * @return Fitet_Monitor_Manager
	 */
	public function build_manager() {
		require_once FITET_MONITOR_DIR . 'admin/includes/class-fitet-monitor-manager-logger.php';
		$logger = new  Fitet_Monitor_Manager_Logger($this->plugin_name, $this->version);

		require_once FITET_MONITOR_DIR . 'admin/includes/class-fitet-portal-rest-http-service.php';
		$http_service = new Fitet_Portal_Rest_Http_Service();

		require_once FITET_MONITOR_DIR . 'admin/includes/class-fitet-portal-rest.php';
		$portal = new Fitet_Portal_Rest($http_service);

		require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-manager.php';
		$manager = new  Fitet_Monitor_Manager($this->plugin_name, $this->version, $logger, $portal);

		return $manager;
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Fitet_Monitor_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		add_action('plugins_loaded', [$this, 'load_text_domain']);
	}

	public function load_text_domain() {
		load_plugin_textdomain($this->plugin_name, false, FITET_MONITOR_DIR . 'languages/');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_admin($manager) {
		require_once FITET_MONITOR_DIR . 'admin/router/class-fitet-monitor-router.php';
		$router = new Fitet_Monitor_Router($this->plugin_name, $this->version, $manager);

		require_once FITET_MONITOR_DIR . 'admin/menu/class-fitet-monitor-menu.php';
		$menu = new Fitet_Monitor_Menu($router, $this->plugin_name);

		require_once FITET_MONITOR_DIR . 'admin/class-fitet-monitor-admin.php';

		$plugin_admin = new Fitet_Monitor_Admin($this->plugin_name, $this->version, $router, $menu);
		$plugin_admin->start();
	}

	private function load_rest_api($manager) {

		require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-api.php';
		$api = new Fitet_Monitor_Api($this->plugin_name, $this->plugin_name, $manager);

		add_action('rest_api_init', [$api, 'initialize']);
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_public($manager) {

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once FITET_MONITOR_DIR . 'public/class-fitet-monitor-public.php';

		$plugin_public = new Fitet_Monitor_Public($this->plugin_name, $this->version, $manager);
		$plugin_public->start();

	}

	/**
	 *
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 * Start the plugin
	 *
	 * @since    1.0.0
	 */
	public function start() {
		$manager = $this->build_manager();


		$this->set_locale();
		$this->load_rest_api($manager);
		if (is_admin()) {
			$this->load_admin($manager);
		} else {
			$this->load_public($manager);
		}

	}

}
