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

		// load global assets
		add_action('wp_enqueue_scripts', [$this, 'load_assets']);

		require_once FITET_MONITOR_DIR . 'public/shortcodes/athletes-list/class-fitet-monitor-athletes-list-shortcode.php';
		$athletes_list_shortcode = new Fitet_Monitor_Athletes_List_Shortcode($this->version, $this->plugin_name, $this->manager);
		$athletes_list_shortcode->initialize();
		add_shortcode($athletes_list_shortcode->tag, [$athletes_list_shortcode, 'render_shortcode']);

		require_once FITET_MONITOR_DIR . 'public/shortcodes/athletes-table/class-fitet-monitor-athletes-table-shortcode.php';
		$athletes_table_shortcode = new Fitet_Monitor_Athletes_Table_Shortcode($this->version, $this->plugin_name, $this->manager);
		$athletes_table_shortcode->initialize();
		add_shortcode($athletes_table_shortcode->tag, [$athletes_table_shortcode, 'render_shortcode']);

		require_once FITET_MONITOR_DIR . 'public/shortcodes/athlete-detail/class-fitet-monitor-athlete-detail-shortcode.php';
		$athlete_detail_shortcode = new Fitet_Monitor_Athlete_Detail_Shortcode($this->version, $this->plugin_name, $this->manager);
		$athlete_detail_shortcode->initialize();
		add_shortcode($athlete_detail_shortcode->tag, [$athlete_detail_shortcode, 'render_shortcode']);

		add_filter('the_title', function ($data)  {

			if ( $data == 'Atleta' && isset($_GET['atleta'])) {
				$player_code = explode('-', $_GET['atleta'])[0];
				$clubs = $this->manager->get_clubs();
				foreach ($clubs as $club) {
					foreach ($club['players'] as $player) {
						if ($player['code'] == $player_code) {
							return $player['name'];
						}
					}
				}
			}

			return  $data;
		});

		require_once FITET_MONITOR_DIR . 'common/blocks/sample-block.php';
	}

	public function load_assets() {
		Fitet_Monitor_Helper::enqueue_script($this->plugin_name, FITET_MONITOR_DIR . 'public/assets/fitet-monitor.js', ['jquery'], $this->version, false);
		Fitet_Monitor_Helper::enqueue_style($this->plugin_name, FITET_MONITOR_DIR . 'public/assets/fitet-monitor.css', [], $this->version, 'all');
	}

}
