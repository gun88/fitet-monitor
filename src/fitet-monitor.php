<?php


/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/gun88
 * @since             1.0.0
 * @package           Fitet_Monitor
 *
 * @wordpress-plugin
 * Plugin Name:       Fitet Monitor
 * Plugin URI:        https://github.com/gun88/fitet-monitor
 * Description:       WordPress plugin for Fitet data
 * Version:           0.0.0-DEV
 * Author:            tpomante
 * Author URI:        https://github.com/gun88
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       fitet-monitor
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

// Main file absolute plugin path
if (!defined('FITET_MONITOR_ROOT_FILE')) {
	define('FITET_MONITOR_ROOT_FILE', __FILE__);
}
// Main file absolute plugin path
if (!defined('FITET_MONITOR_DIR')) {
	define('FITET_MONITOR_DIR', trailingslashit(plugin_dir_path(__FILE__)));
}
// Main file relative path to WP_PLUGIN_DIR
if (!defined('FITET_MONITOR_PLUGIN_DIR_REL_PATH')) {
	define('FITET_MONITOR_PLUGIN_DIR_REL_PATH', str_replace(trailingslashit(WP_PLUGIN_DIR), '', plugin_dir_path(__FILE__)));
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('FITET_MONITOR_VERSION', '0.0.0-DEV');
/**
 * Currently plugin name.
 */
define('FITET_MONITOR_NAME', 'fitet-monitor');

if (!defined('FITET_MONITOR_IS_DEV')) {
	define('FITET_MONITOR_IS_DEV', false);
}

// todo - capire dove metterli
define('FITET_MONITOR_CLUB_NO_LOGO', plugin_dir_url(FITET_MONITOR_DIR . 'public/assets/fitet-monitor-no-club-image.svg') . 'fitet-monitor-no-club-image.svg');
define('FITET_MONITOR_PLAYER_NO_IMAGE', plugin_dir_url(FITET_MONITOR_DIR . 'public/assets/fitet-monitor-no-player-image.svg') . 'fitet-monitor-no-player-image.svg');

define('FITET_MONITOR_ICON_CALENDAR', plugin_dir_url(FITET_MONITOR_DIR . "public/assets/icons/calendar-regular.svg") . "calendar-regular.svg");
define('FITET_MONITOR_ICON_CHART', plugin_dir_url(FITET_MONITOR_DIR . "public/assets/icons/chart-line-solid.svg") . "chart-line-solid.svg");
define('FITET_MONITOR_ICON_PLAYER', plugin_dir_url(FITET_MONITOR_DIR . "public/assets/icons/player-solid.svg") . "player-solid.svg");
define('FITET_MONITOR_ICON_FILTER', plugin_dir_url(FITET_MONITOR_DIR . "public/assets/icons/filter-solid.svg") . "filter-solid.svg");
define('FITET_MONITOR_ICON_HASHTAG', plugin_dir_url(FITET_MONITOR_DIR . "public/assets/icons/hashtag-solid.svg") . "hashtag-solid.svg");
define('FITET_MONITOR_ICON_LIST', plugin_dir_url(FITET_MONITOR_DIR . "public/assets/icons/list-solid.svg") . "list-solid.svg");
define('FITET_MONITOR_ICON_TABLE', plugin_dir_url(FITET_MONITOR_DIR . "public/assets/icons/table-solid.svg") . "table-solid.svg");
define('FITET_MONITOR_ICON_TABLE_TENNIS', plugin_dir_url(FITET_MONITOR_DIR . "public/assets/icons/table-tennis-paddle-ball-solid.svg") . "table-tennis-paddle-ball-solid.svg");
define('FITET_MONITOR_ICON_TROPHY', plugin_dir_url(FITET_MONITOR_DIR . "public/assets/icons/trophy-solid.svg") . "trophy-solid.svg");

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_fitet_monitor() {
	$fitet_monitor = new Fitet_Monitor(FITET_MONITOR_VERSION, FITET_MONITOR_NAME);
	register_activation_hook(FITET_MONITOR_ROOT_FILE, [$fitet_monitor, 'activate']);
	register_deactivation_hook(FITET_MONITOR_ROOT_FILE, [$fitet_monitor, 'deactivate']);
	$fitet_monitor->start();
}

run_fitet_monitor();


add_action('wp', function () {

	global $post;
	if ($post!= null && strpos($post->post_content, '[fitet-monitor-'))
		add_action('wp_head', function () {
			// echo '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">';
			echo '<meta name="viewport" content="width=device-width, initial-scale=.5">';
		}, '1');
});


// todo !!!!!!!!!!!!!!!!!!!!!!!!!!!
function theme_custom_rewrites() {
	foreach (get_pages() as $page) {
		if (strpos($page->post_content, "fitet-monitor-teams")) {
			add_rewrite_rule('^' . $page->post_name . '/([^/]+)/?$', 'index.php?page_id=' . $page->ID . '&team=$matches[1]', 'top');
		}
		if (strpos($page->post_content, "[fitet-monitor-players")) {
			add_rewrite_rule('^' . $page->post_name . '/([^/]+)/?$', 'index.php?page_id=' . $page->ID . '&player=$matches[1]', 'top');
		}
	}
}

// add_action('init', 'theme_custom_rewrites');


add_filter('query_vars', function ($vars) {
	$vars[] = "team";
	$vars[] = "season";
	$vars[] = "championship";
	$vars[] = "club";
	$vars[] = "player";
	$vars[] = "mode";
	$vars[] = "filter";
	return $vars;
});

add_filter('cron_schedules', 'example_add_cron_interval');
function example_add_cron_interval($schedules) {
	$schedules['five_seconds'] = array(
		'interval' => 5,
		'display' => esc_html__('Every Five Seconds', 'fitet-monitor'),);
	return $schedules;
}

/*
add_action('bl_cron_hook', 'bl_cron_exec');


error_log("next " . wp_next_scheduled('bl_cron_hook') - time());

if (!wp_next_scheduled('bl_cron_hook')) {
	error_log("Scheduling");
	wp_schedule_event(time(), 'five_seconds', 'bl_cron_hook');
} else {
	error_log("already scheduled");
}

function bl_cron_exec() {
	error_log("running ");
	//sleep(300);
	error_log("next " . wp_next_scheduled('bl_cron_hook') - time());


}*/


$x = 0;

function memory_dump() {
	if (!FITET_MONITOR_IS_DEV)
		return;
	global $x;
	$x++;
	error_log("[$x]" . ' memory usage: ' . round(memory_get_usage() / (1024 * 1024)) .
		'M. Peak: ' . round(memory_get_peak_usage(false) / (1024 * 1024)) .
		' - ' . round(memory_get_peak_usage(true) / (1024 * 1024)));
}

