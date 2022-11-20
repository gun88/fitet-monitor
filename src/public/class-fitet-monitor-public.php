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
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Fitet_Monitor_Manager $manager
	 */
	private $manager;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 * @param Fitet_Monitor_Manager $manager .
	 * @since 1.0.0
	 */
	public function __construct($plugin_name, $version, $manager) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->manager = $manager;
	}

	/**
	 *  Initialize public components
	 *
	 * @return void
	 */
	public function start() {

		require_once FITET_MONITOR_DIR . 'public/shortcodes/class-fitet-monitor-teams-shortcode.php';
		require_once FITET_MONITOR_DIR . 'public/shortcodes/class-fitet-monitor-players-shortcode.php';
		require_once FITET_MONITOR_DIR . 'public/shortcodes/class-fitet-monitor-titles-shortcode.php';
		require_once FITET_MONITOR_DIR . 'public/shortcodes/class-fitet-monitor-matches-shortcode.php';

		$shortcodes = [
			new Fitet_Monitor_Teams_Shortcode($this->version, $this->plugin_name, $this->manager),
			new Fitet_Monitor_Players_Shortcode($this->version, $this->plugin_name, $this->manager),
			new Fitet_Monitor_Titles_Shortcode($this->version, $this->plugin_name, $this->manager),
			new Fitet_Monitor_Matches_Shortcode($this->version, $this->plugin_name, $this->manager),
		];

		foreach ($shortcodes as $shortcode) {
			$shortcode->initialize_rest_api();
			add_shortcode($shortcode->tag, [$shortcode, 'render_shortcode']);
		}

		//	require_once FITET_MONITOR_DIR . 'common/blocks/sample-block.php';
	}
}
