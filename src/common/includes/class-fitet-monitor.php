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

        global $wpdb;

        $table_name = $wpdb->prefix . "fitet_monitor_clubs";

        $charset_collate = $wpdb->get_charset_collate();
// todo sposta in datasource
        $sql = "CREATE TABLE $table_name (
  code int(10) NOT NULL,
  name varchar(255) NOT NULL,
  province varchar(2) NOT NULL,
  logo varchar(2048) NOT NULL,
  cron varchar(255) NOT NULL DEFAULT 'DEFAULT',
  last_update varchar(20) NULL,
  last_club_update varchar(20) NULL,
  last_players_update varchar(20) NULL,
  last_championships_update varchar(20) NULL,
  nationalTitles json DEFAULT '{}',
  regionalTitles json DEFAULT '{}',
  caps json DEFAULT '{}',
  players json DEFAULT '{}',
  championships json DEFAULT '{}',
  PRIMARY KEY  (code)
) $charset_collate;";

        // todo cambia i last update in timestamp
        // last_update timestamp NULL,
        // last_club_update timestamp NULL,
        // last_players_update timestamp NULL,
        // last_championships_update timestamp NULL,
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
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
        delete_option('fitet-monitor-demo'); // todo remove

        // todo sposta in manager
        global $wpdb;
        $array_values = $wpdb->get_col("SELECT * FROM {$wpdb->prefix}fitet_monitor_clubs");
        foreach ($array_values as $club_code) {
            wp_clear_scheduled_hook('fm_cron_update_club_hook', [$club_code]);
            wp_clear_scheduled_hook('fm_cron_update_players_hook', [$club_code]);
            wp_clear_scheduled_hook('fm_cron_update_championships_hook', [$club_code]);
        }




    }

    /**
     * Setting viewport initial-scale to 0.5 for mobile optimization in Fitet Monitor pages.
     *
     * @since    1.0.0
     * @access   private
     */
    private function viewport_fix() {
        add_action('wp', function () {
            global $post;
            if ($post != null && strpos($post->post_content, '[fitet-monitor-'))
                add_action('wp_head', function () {
                    echo '<meta name="viewport" content="width=device-width, initial-scale=.5">';
                }, '1');
        });
    }

    /**
     * Registering Fitet Monitor custom query vars.
     *
     * @since    1.0.0
     * @access   private
     */
    private function register_query_vars() {
        add_filter('query_vars', function ($vars) {
            $vars[] = "team";
            $vars[] = "season";
            $vars[] = "championship";
            $vars[] = "club";
            $vars[] = "player";
            $vars[] = "match";
            $vars[] = "mode";
            $vars[] = "filter";
            return $vars;
        });
    }

    /**
     * Registering Fitet Monitor activation and deactivation hooks.
     *
     * @since    1.0.0
     * @access   private
     */
    private function register_activation_hooks() {
        register_activation_hook(FITET_MONITOR_ROOT_FILE, [$this, 'activate']);
        register_deactivation_hook(FITET_MONITOR_ROOT_FILE, [$this, 'deactivate']);
    }


    /**
     * Building Fitet_Monitor_Manager and it's dependencies.
     *
     * @return Fitet_Monitor_Manager
     */
    public function build_manager() {
        require_once FITET_MONITOR_DIR . 'admin/includes/class-fitet-monitor-manager-logger.php';
        $logger = new  Fitet_Monitor_Manager_Logger($this->plugin_name, $this->version);

        require_once FITET_MONITOR_DIR . 'admin/includes/class-fitet-portal-rest-http-service.php';
        $http_service = new Fitet_Portal_Rest_Http_Service();

        require_once FITET_MONITOR_DIR . 'admin/includes/class-fitet-portal-rest.php';
        $portal = new Fitet_Portal_Rest($http_service);

        require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-repository.php';
        $repository = new Fitet_Monitor_Repository($this->plugin_name, $this->version);

        require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-manager.php';
        $manager = new  Fitet_Monitor_Manager($this->plugin_name, $this->version, $logger, $portal, $repository);
        $GLOBALS['fitet_monitor_manager'] = $manager;
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

    /**
     * Loading text_domain for i18n
     *
     * @since    1.0.0
     * @access   private
     */
    public function load_text_domain() {
        load_plugin_textdomain($this->plugin_name, false, FITET_MONITOR_PLUGIN_DIR_REL_PATH . 'languages');
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
        $this->register_activation_hooks();
        $this->viewport_fix();
        $this->register_query_vars();

        $manager = $this->build_manager();

        $this->set_locale();
        $this->load_rest_api($manager);
        $this->register_widget($manager);
        $this->schedule_cronjob($manager);
        if (is_admin()) {
            $this->load_admin($manager);
        } else {
            $this->load_public($manager);
        }

    }

    private function schedule_cronjob(Fitet_Monitor_Manager $manager) {
        $manager->schedule_cronjob();
    }

    private function register_widget(Fitet_Monitor_Manager $manager) {
        require_once FITET_MONITOR_DIR . 'common/widgets/class-fitet-monitor-calendar-widget.php';

        add_action('widgets_init', function () {
            register_widget('Fitet_Monitor_Calendar_Widget');
        });
    }


}
