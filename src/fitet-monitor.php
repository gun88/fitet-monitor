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
    $search = str_replace('\\','/',trailingslashit(WP_PLUGIN_DIR));
    $subject = str_replace('\\','/',plugin_dir_path(__FILE__));
    define('FITET_MONITOR_PLUGIN_DIR_REL_PATH', str_replace($search, '', $subject));
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
define('FITET_MONITOR_ICON_CLOUD_ARROW', plugin_dir_url(FITET_MONITOR_DIR . "public/assets/icons/cloud-arrow-down-solid.svg") . "cloud-arrow-down-solid.svg");
define('FITET_MONITOR_ICON_ERASER', plugin_dir_url(FITET_MONITOR_DIR . "public/assets/icons/eraser-solid.svg") . "eraser-solid.svg");


define('FITET_MONITOR_UPLOAD_DIR', wp_upload_dir()['basedir']);
define('FITET_MONITOR_UPLOAD_URL', wp_upload_dir()['baseurl']);
define('TEMP_HIDDEN_PLAYERS', []);

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
	$fitet_monitor->start();
}

run_fitet_monitor();

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




// todo !!!!!!!!!!!!!!!!!!!!!!!!!!!
/*function theme_custom_rewrites() {
	foreach (get_pages() as $page) {
		if (strpos($page->post_content, "fitet-monitor-teams")) {
			add_rewrite_rule('^' . $page->post_name . '/([^/]+)/?$', 'index.php?page_id=' . $page->ID . '&team=$matches[1]', 'top');
		}
		if (strpos($page->post_content, "[fitet-monitor-players")) {
			add_rewrite_rule('^' . $page->post_name . '/([^/]+)/?$', 'index.php?page_id=' . $page->ID . '&player=$matches[1]', 'top');
			add_rewrite_rule('^' . $page->post_name . '/([^/]+)/?$', 'index.php?page_id=' . $page->ID . '&player=$matches[1]', 'top');
            add_rewrite_rule( '^.+player=421024-POMANTE-ROCCO', 'index.php?myparamname=$matches[1]', 'top' );

        }
	}
}

add_action('init', 'theme_custom_rewrites');*/


/*add_filter( 'document_title_parts', function( $title_parts_array ) {
	error_log(json_encode($title_parts_array));
	error_log( get_the_ID() );

	if (true || get_the_ID() == 2055 ) {
		$title_parts_array['title'] = 'Custom Page Title';
	}
	return $title_parts_array;
} );*/

$fm_page_id_list = ['player'=>[], 'match'=>[], 'team'=>[]];

foreach (get_pages() as $page) {

    if (strpos($page->post_content, "[fitet-monitor-players")) {
        $fm_page_id_list['player'][] = $page->ID;
    }
    if (strpos($page->post_content, "[fitet-monitor-teams")) {
        $fm_page_id_list['team'][] = $page->ID;
    }
    if (strpos($page->post_content, "[fitet-monitor-matches")) {
        $fm_page_id_list['match'][] = $page->ID;
    }

}




function change_custom_post_type_archive_title($title) {
	//error_log(json_encode($title));
    global $post;
    global $fm_page_id_list;
    // error_log(json_encode($post, 128));

    if (in_array($post->ID, $fm_page_id_list['player'])) {
        $playerSlug = get_query_var('player');
        if ($playerSlug) {
            $playerName = explode('-', $playerSlug, 2)[1];
            if ($playerName) {
                // todo db search
                $playerName = ucwords(strtolower(str_replace('-', ' ', $playerName)));
                $site_title = get_bloginfo('name');
                return "$playerName - $site_title";
            }
        }
    }


	return $title;
}



add_filter('wpseo_title', 'change_custom_post_type_archive_title');
add_filter('pre_get_document_title', 'change_custom_post_type_archive_title');


require_once FITET_MONITOR_DIR . 'public/includes/plugin-update-checker/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;


$resPongUpdateChecker = PucFactory::buildUpdateChecker(
    'https://raw.githubusercontent.com/gun88/fitet-monitor/refs/heads/main/release/plugin.json',
    __FILE__,
    FITET_MONITOR_NAME
);

