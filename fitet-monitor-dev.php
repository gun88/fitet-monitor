<?php
/**
 * Plugin Name:     Fitet Monitor [DEV]
 * Plugin URI:      https://github.com/gun88/fitet-monitor
 * Description:     Developing version of WordPress plugin for FITeT data
 * Author:          tpomante
 * Author URI:      https://github.com/gun88
 * Text Domain:     fitet-monitor
 * Domain Path:     /languages
 * Version:         0.0.0
 *
 * @package         Fitet_Monitor
 */

// overriding definitions for development environment
define('FITET_MONITOR_ROOT_FILE', __FILE__);
define('FITET_MONITOR_DIR', trailingslashit(plugin_dir_path(__FILE__)) . 'src/');

require_once FITET_MONITOR_DIR . 'fitet-monitor.php';




