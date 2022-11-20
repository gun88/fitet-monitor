<?php

require_once FITET_MONITOR_DIR . 'admin/includes/class-fitet-monitor-manager-logger.php';

class Fitet_Monitor_Utils {

	public static $clubs = null;


	// ok

	public static function player_code_by_id($player_id, $club_code = '') {
		foreach (self::clubs() as $club) {
			foreach ($club['players'] as $player) {
				if ($player['playerId'] == $player_id && (empty($club_code) || $club_code == $player['clubCode'])) {
					return $player['playerCode'];
				}
			}
		}
		return '';
	}

	public static function player_id_by_code($player_code, $club_code = '') {
		foreach (self::clubs() as $club) {
			foreach ($club['players'] as $player) {
				if ($player['playerCode'] == $player_code && (empty($club_code) || $club_code == $player['clubCode'])) {
					return $player['playerId'];
				}
			}
		}
		return '';
	}

	public static function player_id_by_name_in_standings($player_name, $club_code) {

		foreach (self::clubs() as $club) {
			if ($club['clubCode'] != $club_code) {
				continue;
			}
			foreach ($club['championships'] as $championship) {
				foreach ($championship['standings'] as $standing) {
					if ($standing['clubCode'] != $club_code) {
						continue;
					}
					foreach ($standing['players'] as $player) {
						if ($player_name == $player['playerName'])
							return $player['playerId'];
					}

				}
			}
		}
		return '';
	}

	public static function team_id_by_name($championship_id, $season_id, $team_name) {
		foreach (self::clubs() as $club) {
			foreach ($club['championships'] as $championship) {
				foreach ($championship['standings'] as $standing) {
					if (trim($standing['teamName']) == trim($team_name) &&
						$championship['seasonId'] == $season_id &&
						$championship['championshipId'] == $championship_id) {
						return $standing['teamId'];
					}
				}
			}
		}
		return '';
	}


	// fine ok

	public static function player_image_url($player_id) {
		if (empty($player_id)) {
			return FITET_MONITOR_PLAYER_NO_IMAGE;
		}
		foreach (['svg', 'SVG', 'png', 'PNG', 'jpg', 'JPG', 'jpeg', 'JPEG'] as $extension) {
			if (file_exists(FITET_MONITOR_UPLOAD_DIR . "/fitet-monitor/players/$player_id.$extension"))
				return FITET_MONITOR_UPLOAD_URL . "/fitet-monitor/players/$player_id.$extension";
		}
		return "http://portale.fitet.org/images/atleti/$player_id.jpg";
	}


	public static function club_logo_by_code($club_code) {
		foreach (['svg', 'SVG', 'png', 'PNG', 'jpg', 'JPG', 'jpeg', 'JPEG'] as $extension) {
			if (file_exists(FITET_MONITOR_UPLOAD_DIR . "/fitet-monitor/clubs/$club_code.$extension"))
				return FITET_MONITOR_UPLOAD_URL . "/fitet-monitor/clubs/$club_code.$extension";
		}
		foreach (self::clubs() as $club) {
			if ($club['clubCode'] == $club_code && isset($club['clubLogo']))
				return $club['clubLogo'];
		}
		return '';
	}

	public static function clubs() {
		// todo deve diventare private
		if (self::$clubs == null) {
			$manager = new Fitet_Monitor_Manager(FITET_MONITOR_NAME, FITET_MONITOR_VERSION, null, null);


			self::$clubs = $manager->get_clubs([
				'clubCode' => '',
				'clubName' => '',
				'clubLogo' => '',
				'lastUpdate' => '',
				'players' => [
					'playerId' => '',
					'playerCode' => '',
					'playerName' => '',
					'clubCode' => '',
				],
				'championships' => [
					'championshipId' => '',
					'seasonId' => '',
					'standings' => '',
				]
			]);


			// remove not loaded teams
			self::$clubs = array_values(array_filter(self::$clubs, function ($club) {
				return isset($club['lastUpdate']);
			}));


		}

		return self::$clubs;
	}


	public static function player_by_id($player_id) {
		foreach (self::clubs() as $club) {
			foreach ($club['players'] as $player) {
				if ($player['playerId'] == $player_id) {
					return $player;
				}
			}
		}
		return null;
	}

	public static function player_id_by_name($player_name) {
		$player_id_list = [];
		foreach (self::clubs() as $club) {
			foreach ($club['players'] as $player) {
				if (trim($player['playerName']) == trim($player_name)) {
					$player_id_list[] = $player['playerId'];
				}
			}
		}

		if (empty($player_id_list))
			return '';
		if (count($player_id_list) > 1)
			error_log("WARNING: multiple player found with name '$player_name' - returning first with id: " . $player_id_list[0]);


		return $player_id_list[0];
	}


	public static function player_page_url($player_base_url, $player_code, $player_name = 'N/A') {
		if ($player_base_url == null || $player_code == null) {
			return null;
		}
		$slug = urlencode(str_replace(" ", "-", $player_name));
		$slug = "$player_code-$slug";
		return "$player_base_url&player=$slug";

	}

	public static function match_page_url($match_base_url, $match_id) {
		if ($match_base_url == null || $match_id == null) {
			return null;
		}
		return "$match_base_url&match=$match_id";

	}


	public static function club_code_by_team_id($championship_id, $season_id, $team_id) {
		foreach (self::clubs() as $club) {
			foreach ($club['championships'] as $championship) {
				foreach ($championship['standings'] as $standing) {
					if (trim($standing['teamId']) == trim($team_id) &&
						$championship['seasonId'] == $season_id &&
						$championship['championshipId'] == $championship_id) {
						return $standing['clubCode'];
					}
				}
			}
		}
		return '';
	}


	public static function fill_team_rankings($championship) {
		for ($i = 0; $i < count($championship['standings']); $i++) {
			if ($i > 0 && $championship['standings'][$i - 1]['points'] == $championship['standings'][$i]['points']) {
				$championship['standings'][$i]['ranking'] = $championship['standings'][$i - 1]['ranking'];
			} else {
				$championship['standings'][$i]['ranking'] = $i + 1;
			}
		}
		usort($championship['standings'], function ($t1, $t2) {
			if ($t1['ranking'] != $t2['ranking']) {
				return $t1['ranking'] - $t2['ranking'];
			}
			return Fitet_Monitor_Utils::team_status_to_int($t2['teamStatus']) - Fitet_Monitor_Utils::team_status_to_int($t1['teamStatus']);
		});
		return $championship;
	}

	public static function intersect_template($array, $template) {
		$original_template = $template;
		if ($template == null)
			return $array;
		foreach ($template as $key => $value) {

			if (!isset($array[$key])) {
				continue;
			}

			if (empty($value)) {
				$template[$key] = $array[$key];
			} /*else if (null == ($array[$key])) {
				$template[$key] = null;
			}*/ else if (is_scalar($array[$key])) {
				$template[$key] = $array[$key];
			} else if (self::is_associative_array($array[$key])) {
				$template[$key] = self::intersect_template($array[$key], $value);
			} else {
				$template[$key] = [];
				foreach ($array[$key] as $item) {
					if (is_scalar($item)) {
						$template[$key][] = $item;
					} else {
						$template[$key][] = self::intersect_template($item, $value);
					}
				}

			}


		}
		if ($template == $original_template)
			return [];
		return $template;
	}


	private static function is_associative_array($arr) {
		if (array() === $arr) return false;
		return array_keys($arr) !== range(0, count($arr) - 1);
	}


	// PRIVATE!!!!!!!!
	public static function team_loaded($championship_id, $season_id, $team_id) {
		foreach (self::clubs() as $club) {
			foreach ($club['championships'] as $championship) {
				if ($championship['championshipId'] != $championship_id) continue;
				if ($championship['seasonId'] != $season_id) continue;

				foreach ($championship['standings'] as $standing) {
					if ($standing['teamId'] != $team_id) continue;
					if ($standing['clubCode'] != $club['clubCode']) continue;
					return true;
				}

			}

		}
		return false;
	}

	public static function team_status_to_int($team_status) {
		switch ($team_status) {
			case 'promotion':
				return 5;
			case 'playoff':
				return 4;
			default:
			case 'neutral':
				return 3;
			case 'playout':
				return 2;
			case 'relegation':
				return 1;
		}
	}

	public static function is_hidden($player_code): bool {
		return in_array($player_code, [515982]);
	}

	public static function belongs_to_club($player_code, $club_code) {
		foreach (self::clubs() as $club) {
			if (!empty($club_code) && $club['clubCode'] != $club_code) {
				continue;
			}
			foreach ($club['players'] as $player) {
				if ($player['playerCode'] == $player_code)
					return true;
			}
		}
		return false;
	}

	/**
	 * @param array $old_championships
	 * @param array $championships
	 * @return mixed
	 */
	public static function merge_championships($old_championships, $championships) {

		foreach ($championships as $championship) {
			$index = self::index_of_championship($old_championships, $championship['seasonId'], $championship['championshipId']);
			if ($index == -1) {
				$old_championships[] = $championship;
			} else {
				//$championship['standings'] = Fitet_Monitor_Utils::merge_standings($old_championships[$index]['standings'], $championship['standings']);
				$old_championships[$index] = $championship;
			}
		}
		usort($old_championships, function ($c1, $c2) {
			if ($c2['seasonId'] == $c1['seasonId']) {
				return $c2['championshipId'] - $c1['championshipId'];
			}
			return $c2['seasonId'] - $c1['seasonId'];
		});

		return $old_championships;
	}

	private static function index_of_championship($championships, $season_id, $championship_id) {
		for ($i = 0; $i < count($championships); $i++) {
			if ($championships[$i]['seasonId'] == $season_id && $championships[$i]['championshipId'] == $championship_id) {
				return $i;
			}
		}
		return -1;
	}
	private static function index_of_player($players, $player_id) {
		for ($i = 0; $i < count($players); $i++) {
			if ($players[$i]['playerId'] == $player_id) {
				return $i;
			}
		}
		return -1;
	}

	private static function merge_standings($old_standings, $standings) {

		return $standings;

		// todo terminare
		if (!isset($old_championship['players'])) {
			$old_championship['players'] = [];
		}

			foreach ($championship['players'] as $player) {
				$index = self::index_of_player($old_championship, $player['playerId']);
				if ($index == -1) {
					$old_championship['players'][] = $player;
				} else {
					$old_championship['players'][$index] = $player;
					// $old_championships[$index] = $championship;
				}
			}
			usort($old_championship['players'], function ($c1, $c2) {
				return strcmp($c1,$c2);
			});

			return $old_championship;

	}



}
