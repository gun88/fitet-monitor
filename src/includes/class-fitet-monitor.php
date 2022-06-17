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
	 * @since    1.0.0
	 */
	public function __construct($version, $plugin_name) {

		$this->version = $version;
		$this->plugin_name = $plugin_name;

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Fitet_Monitor_i18n. Defines internationalization functionality.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once FITET_MONITOR_DIR . 'includes/class-fitet-monitor-i18n.php';

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

		$plugin_i18n = new Fitet_Monitor_i18n($this->plugin_name);

		add_action('plugins_loaded', [$plugin_i18n, 'load_plugin_textdomain']);

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_admin() {
		require_once FITET_MONITOR_DIR . 'admin/class-fitet-monitor-admin.php';
		$plugin_admin = new Fitet_Monitor_Admin($this->plugin_name, $this->version);
		$plugin_admin->start();
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_public() {


		add_shortcode('subscribe', function ($atts, $content = null) {
			$default = [
				'style' => '',
			];
			$style = shortcode_atts($default, $atts)['style'];
			$name = do_shortcode($content);
			$name = !empty($name) ? $name : 'NOT_SET';

			$from = get_option('fitet-monitor-club-code');
			$str = sprintf(__("Hello %s! Regards from %s", $this->plugin_name, 'fitet-monitor'), $name, $from);
			return "<p style='$style'>" . $str . "</p>";
		});


		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once FITET_MONITOR_DIR . 'public/class-fitet-monitor-public.php';

		$plugin_public = new Fitet_Monitor_Public($this->plugin_name, $this->version);

		add_action('wp_enqueue_scripts', [$plugin_public, 'enqueue_styles']);
		add_action('wp_enqueue_scripts', [$plugin_public, 'enqueue_scripts']);

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
		$this->load_dependencies();
		$this->set_locale();
		if (is_admin()) {
			$this->load_admin();
		} else {
			$this->load_public();
		}

	}

}
