<?php

require_once FITET_MONITOR_DIR . 'admin/includes/class-fitet-monitor-manager-logger.php';

class Fitet_Monitor_Utils {

	public static $clubs = null;


	// ok

	public static function player_url($player_page_id, $player_code, $player_name) {
		$player_slug = $player_code . '-' . urlencode(str_replace(" ", "-", $player_name));
		return "index.php?page_id=$player_page_id&player=$player_slug";
	}

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
		return "http://portale.fitet.org/images/atleti/$player_id.jpg";
	}

	public static function club_name_by_code($club_code) {
		foreach (self::clubs() as $club) {
			if ($club['clubCode'] == $club_code && isset($club['clubName']))
				return $club['clubName'];
		}
		return null;
	}


	public static function club_logo_by_code($club_code) {
		foreach (self::clubs() as $club) {
			if ($club['clubCode'] == $club_code && isset($club['clubLogo']))
				return $club['clubLogo'];
		}
		return '';
	}


	public static function player_cell_by_name_and_club($player_name, $club_code, $player_base_urlase_url = null) {

		return "__implement__";
		$player = self::player_id_by_name($player_name);
		$player_url = self::player_page_url($player_base_urlase_url, $player['playerCode'], $player['playerName'],);
		return self::player_cell($player['playerId'], $player_url, $player_name);
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


	public static function player_image($player_id, $player_url = null, $player_name = 'N/A') {
		$player_image_url = self::player_image_url($player_id);
		$image = "<img src='$player_image_url' alt='$player_name'  onError='this.onerror=null;this.src=\"" . FITET_MONITOR_PLAYER_NO_IMAGE . "\";'>";
		if ($player_url != null) {
			$image = "<a class='fm-player-image' href='$player_url'>$image</a>";
		} else {
			$image = "<span class='fm-player-image'>$image</span>";
		}
		return $image;
	}


	public static function club_image_url($club_code) {
		return "http://portale.fitet.org/images/societa/$club_code.jpg";
	}

	public static function team_image_url($team) {
		return self::club_image_url($team);
	}

	public static function player_by_code($player_code) {
		foreach (self::clubs() as $club) {
			foreach ($club['players'] as $player) {
				if ($player['code'] == $player_code) {
					return $player;
				}
			}
		}
		return null;
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

	public static function team_page_url($season_id, $championship_id, $team_id, $team_base_url) {
		return "$team_base_url&season=$season_id&championship=$championship_id&team=$team_id";

	}


	public static function player_cell($player_id, $player_url = null, $player_name = 'N/A') {

		$image = self::player_image($player_id, $player_url, $player_name);

		if ($player_url != null) {
			$text = "<a class='fm-player-name' href='$player_url'>$player_name</a>";
		} else {
			$text = "<span class='fm-player-name'>$player_name</span>";
		}

		return "<div class='fm-player-cell'>$image$text</div>";
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

	public static function filter_loaded_clubs($clubs) {
		return array_values(array_filter($clubs, function ($club) {
			return isset($club['lastUpdate']);
		}));
	}

	public static function intersect_template_for_all(array $arrays, $template) {
		return array_map(function ($array) use ($template) {
			return self::intersect_template($array, $template);
		}, $arrays);
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


	private
	static function club($club_code) {

		foreach (self::clubs() as $club) {
			if ($club['clubCode'] == $club_code) {
				return $club;
			}
		}
		return null;
	}

}
