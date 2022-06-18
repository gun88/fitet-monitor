<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-helper.php';

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/gun88
 * @since      1.0.0
 *
 * @package    Fitet_Monitor
 * @subpackage Fitet_Monitor/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Fitet_Monitor
 * @subpackage Fitet_Monitor/public
 * @author     tpomante <gun88@hotmail.it>
 */
class Fitet_Monitor_Public {

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
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 * @since 1.0.0
	 */
	public function __construct($plugin_name, $version) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 *  Initialize public components
	 *
	 * @return void
	 */
	public function start() {

		// load global assets
		add_action('wp_enqueue_scripts', [$this, 'load_assets']);

		require_once FITET_MONITOR_DIR . 'public/shortcodes/sample-shortcode/class-fitet-monitor-sample-shortcode.php';
		$shortcode = new Fitet_Monitor_Sample_Shortcode($this->version, $this->plugin_name);
		$shortcode->initialize();
		add_shortcode($shortcode->tag, [$shortcode, 'render_shortcode']);

		require_once FITET_MONITOR_DIR . 'common/blocks/sample-block.php';
	}

	public function load_assets() {
		Fitet_Monitor_Helper::enqueue_script($this->plugin_name, FITET_MONITOR_DIR . 'public/assets/fitet-monitor.js', ['jquery'], $this->version, false);
		Fitet_Monitor_Helper::enqueue_style($this->plugin_name, FITET_MONITOR_DIR . 'public/assets/fitet-monitor.css', [], $this->version, 'all');
	}

}
