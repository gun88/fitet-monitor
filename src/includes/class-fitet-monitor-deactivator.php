<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://github.com/gun88
 * @since      1.0.0
 *
 * @package    Fitet_Monitor
 * @subpackage Fitet_Monitor/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Fitet_Monitor
 * @subpackage Fitet_Monitor/includes
 * @author     tpomante <gun88@hotmail.it>
 */
class Fitet_Monitor_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		error_log("################");
		error_log("#  DEACTIVATE  #");
		error_log("################");
		delete_option('fitet-monitor-demo');

	}

}
