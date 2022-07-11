<?php


define('FITET_MONITOR_DIR', dirname(dirname(dirname(__DIR__))) . '/src/');
if (!defined('FITET_MONITOR_CLUB_NO_LOGO'))
	define('FITET_MONITOR_CLUB_NO_LOGO',  '/fitet-monitor-no-club-image.svg');
if (!defined('FITET_MONITOR_PLAYER_NO_IMAGE'))
	define('FITET_MONITOR_PLAYER_NO_IMAGE',  '/fitet-monitor-no-player-image.svg');
define('TEST_DIR', dirname(dirname(dirname(__DIR__))) . '/tests/');

require_once dirname(dirname(__FILE__)) . '/Fitet_Monitor_Test_Case.php';
