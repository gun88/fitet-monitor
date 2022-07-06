<?php


class Fitet_Monitor_Utils {

	public static $clubs = null;
	public static $player_code_to_id_map = null;

	public static function club_cell($club_code, $club_fallback_name = 'N/A') {

		$club = self::club($club_code);

		$club_page_url = self::club_page_url($club);

		$image = self::club_image($club, $club_page_url);

		$text = $club != null ? $club['clubName'] : $club_fallback_name;
		if ($club_page_url != null) {
			$text = "<a class='fm-club-name' href='$club_page_url'>$text</a>";
		} else {
			$text = "<span class='fm-club-name'>$text</span>";
		}

		return "<div class='fm-club-cell'>$image$text</div>";
	}


	public static function player_cell_by_name_and_club($player_name, $club_code, $base_url = null) {

		$player = self::player_by_name_and_club($player_name, $club_code);

		return self::player_cell($player, $base_url, $player_name);
	}

	public static function player_cell_by_code($player_code, $base_url = null, $player_fallback_name = 'N/A') {

		$player = self::player_by_code($player_code);

		return self::player_cell($player, $base_url, $player_fallback_name);
	}


	public static function clubs() {
		if (self::$clubs == null) {
			$manager = new Fitet_Monitor_Manager(FITET_MONITOR_NAME, FITET_MONITOR_VERSION, null, null);
			self::$clubs = $manager->get_clubs();
		}
		return self::$clubs;
	}

	public static function player_image($player, $player_page_url, $player_fallback_name = 'N/A') {
		$player_image_url = self::player_image_url($player);
		$player_name = $player == null ? $player_fallback_name : $player['name'];
		$image = "<img src='$player_image_url' alt='$player_name'  onError='this.onerror=null;this.src=\"" . FITET_MONITOR_PLAYER_NO_IMAGE . "\";'>";
		if ($player_page_url != null) {
			$image = "<a class='fm-player-image' href='$player_page_url'>$image</a>";
		} else {
			$image = "<span class='fm-player-image'>$image</span>";
		}
		return $image;
	}

	public static function team_image($team, $team_page_url, $team_fallback_name = 'N/A') {
		$team_image_url = self::team_image_url($team);
		$team_name = $team == null ? $team_fallback_name : $team['teamName'];
		$image = "<img src='$team_image_url' alt='$team_name'  onError='this.onerror=null;this.src=\"" . FITET_MONITOR_CLUB_NO_LOGO . "\";'>";
		if ($team_page_url != null) {
			$image = "<a class='fm-team-image' href='$team_page_url'>$image</a>";
		} else {
			$image = "<span class='fm-team-image'>$image</span>";
		}
		return $image;
	}

	public static function club_image($club, $club_page_url) {
		$club_image_url = self::club_image_url($club);
		$club_name = $club['clubName'];
		$image = "<img src='$club_image_url' alt='$club_name'  onError='this.onerror=null;this.src=\"" . FITET_MONITOR_CLUB_NO_LOGO . "\";'>";
		if ($club_page_url != null) {
			$image = "<a class='fm-club-image' href='$club_page_url'>$image</a>";
		} else {
			$image = "<span class='fm-club-image'>$image</span>";
		}
		return $image;
	}

	public static function player_image_url($player) {
		if ($player == null) {
			return FITET_MONITOR_PLAYER_NO_IMAGE;
		}
		$player_id = $player['id'];
		return "http://portale.fitet.org/images/atleti/$player_id.jpg";
	}

	public static function club_image_url($club) {
		if ($club == null) {
			return FITET_MONITOR_CLUB_NO_LOGO;
		}
		if (isset($club['clubLogo'])) {
			return $club['clubLogo'];
		}
		$club_code = $club['clubCode'];
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

	public static function player_by_name_and_club($player_name, $club_code) {
		$players = [];
		foreach (self::clubs() as $club) {
			foreach ($club['players'] as $player) {
				if ($player['name'] == $player_name) {
					$players[] = $player;
				}
			}
		}
		if (count($players) > 1) {
			$players = array_values(array_filter($players, function ($player) use ($club_code) {
				return $player['clubCode'] == $club_code;
			}));
		}
		return empty($players) ? null : $players[0];
	}

	public static function club($club_code) {
		foreach (self::clubs() as $club) {
			if ($club['clubCode'] == $club_code) {
				return $club;
			}
		}
		return null;
	}

	public static function player_page_url($player, $base_url) {
		if ($base_url == null || $player == null) {
			return null;
		}
		$player_code = $player['code'];
		$player_name_slug = str_replace(" ", "-", $player['name']);
		return "$base_url&player=$player_code-$player_name_slug";

	}

	public static function team_page_url($team, $season_id, $championship_id, $base_url) {
		if ($team['teamName'] == 'ASD ANTONIANA TT PESCARA')
			error_log("asddsada" . ($team['loaded'] ? 1 : 444));

		if ($base_url == null || $team == null || !$team['loaded']) {
			return null;
		}
		$team_code = $team['teamId'];
		$team_name_slug = str_replace(" ", "-", $team['teamName']);
		return "$base_url&season=$season_id&championship=$championship_id&team=$team_code"; // todo restore -$team_name_slug

	}

	private static function club_page_url($club) {
		return isset($club['pageUrl']) ? $club['pageUrl'] : null;
	}


	public static function player_cell($player, $base_url, $player_fallback_name): string {
		$player_page_url = self::player_page_url($player, $base_url);

		$image = self::player_image($player, $player_page_url, $player_fallback_name);

		$text = $player != null ? $player['name'] : $player_fallback_name;
		if ($player_page_url != null) {
			$text = "<a class='fm-player-name' href='$player_page_url'>$text</a>";
		} else {
			$text = "<span class='fm-player-name'>$text</span>";
		}

		return "<div class='fm-player-cell'>$image$text</div>";
	}


	public static function team_cell_by_name($team_name, $season_id, $championship_id, $base_url) {
		$team = self::team_by_name($team_name, $season_id, $championship_id);
		$team_page_url = self::team_page_url($team, $season_id, $championship_id, $base_url);
		$image = self::team_image($team, $team_page_url, $team_name);

		$text = $team != null ? $team['teamName'] : $team_name;
		if ($team_page_url != null) {
			$text = "<a class='fm-team-name' href='$team_page_url'>$text</a>";
		} else {
			$text = "<span class='fm-team-name'>$text</span>";
		}

		return "<div class='fm-team-cell'>$image$text</div>";
	}

	private static function team_by_name($team_name, $season_id, $championship_id) {

		$teams = [];
		foreach (self::clubs() as $club) {
			foreach ($club['championships'] as $championship) {
				foreach ($championship['standings'] as $standing) {
					if ($standing['teamName'] == $team_name &&
						$championship['seasonId'] == $season_id &&
						$championship['id'] == $championship_id) {
						$team = [
							'teamId' => $standing['teamId'],
							'teamName' => $standing['teamName'],
							'clubCode' => $standing['clubCode'],
							'clubName' => $standing['clubName'],
							'seasonId' => $championship['seasonId'],
							'seasonName' => $championship['seasonName'],
							'championshipName' => $championship['name'],
							'championshipId' => $championship['id'],
							'loaded' => isset($standing['players']),
						];
						if ($team['loaded'])
							return $team;
						$teams[] = $team;
					}
				}
			}
		}
		return empty($teams) ? null : $teams[0];
	}

	public static function calculate_best_ranking($rankings) {
		// todo to utils class

		$rankings = array_map(function ($ranking) {
			if (empty($ranking['position'])) {
				return null;
			} else {
				return ['position' => $ranking['position'], 'date' => $ranking['date']];
			}
		}, $rankings);

		$rankings = array_values(array_filter($rankings, function ($ranking) {
			return $ranking != null;
		}));
		usort($rankings, function ($r1, $r2) {
			return intval($r1['position']) - intval($r2['position']);

		});
		return isset($rankings[0]) ? $rankings[0] : null;
	}


}
