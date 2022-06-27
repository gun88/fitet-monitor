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
	define('FITET_MONITOR_PLUGIN_DIR_REL_PATH', trailingslashit(plugin_dir_path(__FILE__)));
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

define('FITET_MONITOR_CLUB_NO_LOGO', plugin_dir_url(FITET_MONITOR_DIR . 'public/assets/fitet-monitor-no-logo.svg') . 'fitet-monitor-no-logo.svg');

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
