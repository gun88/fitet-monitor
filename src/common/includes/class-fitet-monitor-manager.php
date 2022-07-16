<?php

define('FITET_MONITOR_MB_CONVERT_ENCODING_EXIST', function_exists('mb_convert_encoding'));
define('FITET_MONITOR_ICONV_EXIST', function_exists('iconv'));

class Fitet_Monitor_Manager {

	private $plugin_name;
	private $version;
	/**
	 * @var Fitet_Monitor_Manager_Logger
	 */
	protected $logger;
	/**
	 * @var Fitet_Portal_Rest
	 */
	protected $portal;


	/**
	 * @param string $plugin_name
	 * @param string $version
	 * @param Fitet_Monitor_Manager_Logger $logger
	 * @param Fitet_Portal_Rest $portal
	 */
	public function __construct($plugin_name, $version, $logger, $portal) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->logger = $logger;
		$this->portal = $portal;
	}

	public function add_club($club) {
		$this->save_club($club);
		$this->logger->reset_status($club['clubCode']);
	}

	public function edit_club($club_update) {
		$club = get_option($this->plugin_name . $club_update['clubCode'], []);
		$club['clubName'] = $club_update['clubName'];
		$club['clubProvince'] = $club_update['clubProvince'];
		$club['clubLogo'] = $club_update['clubLogo'];
		$club['clubCron'] = $club_update['clubCron'];
		$club['clubHistorySize'] = $club_update['clubHistorySize'];
		$this->save_club($club);
	}

	private function save_club($club) {

		if (empty($club['clubCode']))
			throw new Exception("empty club code");

		//$club['clubName'] = stripslashes($club['clubName']);

		$club_code = $club['clubCode'];
		$club_codes = get_option($this->plugin_name . 'clubs', []);
		$club_codes[] = $club_code;
		update_option($this->plugin_name . 'clubs', array_values(array_filter(array_unique($club_codes))));
		update_option($this->plugin_name . $club_code, $club);

	}

	public function delete_clubs($club_codes) {
		if (!is_array($club_codes))
			$club_codes = [$club_codes];

		$all = get_option($this->plugin_name . 'clubs', []);
		$toRemove = $club_codes;
		$result = array_diff($all, $toRemove);

		update_option($this->plugin_name . 'clubs', $result);
		foreach ($toRemove as $club_code) {
			delete_option($this->plugin_name . $club_code);
		}
	}

	public function get_club($club_code, $template = null) {
		return Fitet_Monitor_Utils::intersect_template(get_option($this->plugin_name . $club_code, []), $template);
	}

	public function club_exist($club_code) {
		$club_info = $this->portal->get_club_info($club_code);
		return $club_info['clubName'] != 'N/A';
	}

	public function get_clubs($template = null) {
		$club_codes = array_values(get_option($this->plugin_name . 'clubs', []));
		if (!$club_codes) {
			return [];
		}
		return array_map(function ($club_code) use ($template) {
			return $this->get_club($club_code, $template);
		}, $club_codes);
	}

	public function find_clubs($club_name_contains) {
		return $this->portal->find_clubs($club_name_contains);
	}

	public function get_status($club_code) {
		return $this->logger->get_status($club_code);
	}

	public function update($club_code, $mode = '') {
		set_time_limit(300);
		$status_log = $this->logger->get_status($club_code);

		if ($status_log['status'] != 'updating') {

			try {
				$this->logger->reset_status($club_code);

				if ($mode == 'full-history') {
					$club = $this->full_championships_history($club_code);

				} else {
					$club = $this->retrieve_club_data($club_code, $mode);
				}
				$this->logger->set_completed($club_code, 'Done');
				$this->save_club($club);

			} catch (Exception $e) {
				$this->logger->add_status($club_code, 'Fail: ' . $e->getMessage(), 0, 'fail');
				error_log($e);
			} catch (Throwable $e) {
				error_log($e);
				$this->logger->add_status($club_code, 'Fail: ' . $e->getMessage(), 0, 'fail');
			}

		}

	}


	public function retrieve_club_data($club_code, $mode, $home_teams_only = false) {
		if ($club_code == null)
			throw new Exception("Club code can not be null!");

		$this->logger->add_status($club_code, 'Start updating');

		$this->logger->add_status($club_code, "Getting info for club $club_code", 0);

		$club = $this->get_club($club_code);
		$history_size = $mode == 'full-history' ? null : $club['clubHistorySize'];

		$club_info = $this->portal->get_club_info($club_code);
		$club['email'] = $club_info['email'];
		$club['affiliationDate'] = $club_info['affiliationDate'];


		$this->logger->add_status($club_code, "Getting details for club " . $club['clubCode'] . " " . $club['clubName'], 5);
		$club_details = $this->portal->get_club_details($club_code, $history_size);

		usort($club_details['nationalTitles'], [$this, 'sort_titles']);
		usort($club_details['regionalTitles'], [$this, 'sort_titles']);

		$old_championships = isset($club['championships']) ? $club['championships'] : [];
		$club['championships'] = $club_details['championships'];
		$club['nationalTitles'] = $club_details['nationalTitles'];
		$club['regionalTitles'] = $club_details['regionalTitles'];
		$club['attendances'] = $club_details['attendances'];
		$club = array_merge($club, $club_details);


		for ($i = 0, $count = count($club['championships']); $i < $count; $i++) {
			$championship = $club['championships'][$i];
			$championship_id = $championship['championshipId'];
			$championship_name = $championship['championshipName'];
			$season_id = $championship['seasonId'];
			$season_name = $championship['seasonName'];
			$this->logger->add_status($club_code, "Getting standings (" . ($i + 1) . "/$count): $season_name - $championship_name", 8 / $count);
			$standings = $this->portal->get_championship_standings($championship_id, $season_id);
			$club['championships'][$i]['standings'] = $standings;
		}

		$total_standings = array_sum(array_map(function ($c) {
			return count($c['standings']);
		}, $club['championships']));
		$standing_cursor = 0;
		for ($i = 0, $count_i = count($club['championships']); $i < $count_i; $i++) {
			$championship = $club['championships'][$i];
			for ($j = 0, $count_j = count($championship['standings']); $j < $count_j; $j++) {
				$championship_id = $championship['championshipId'];
				$championship_name = $championship['championshipName'];
				$season_id = $championship['seasonId'];
				$season_name = $championship['seasonName'];

				$team_id = $championship['standings'][$j]['teamId'];
				$team_name = $championship['standings'][$j]['teamName'];
				$this->logger->add_status($club_code, "Getting team info (" . ++$standing_cursor . "/$total_standings): $season_name - $championship_name - $team_name", 18 / $total_standings);
				$team_info = $this->portal->get_team_info($team_id, $championship_id, $season_id);
				$club['championships'][$i]['standings'][$j] = array_merge($championship['standings'][$j], $team_info);
			}
		}

		$standing_cursor = 0;
		$total_standings = array_sum(array_map(function ($c) use ($club_code) {
			return count(array_values(array_filter($c['standings'], function ($s) use ($club_code) {
				return $club_code == $s['clubCode'];
			})));
		}, $club['championships']));

		for ($i = 0, $_count = count($club['championships']); $i < $_count; $i++) {
			$championship = $club['championships'][$i];
			for ($j = 0, $count = count($championship['standings']); $j < $count; $j++) {
				$standing = $championship['standings'][$j];
				if ($club_code != $standing['clubCode']) {
					continue;
				}
				$championship_id = $championship['championshipId'];
				$championship_name = $championship['championshipName'];
				$season_id = $championship['seasonId'];
				$season_name = $championship['seasonName'];

				$team_id = $standing['teamId'];
				$team_name = $standing['teamName'];
				$this->logger->add_status($club_code, "Getting team details (" . ++$standing_cursor . "/$total_standings): $season_name - $championship_name - $team_name", 8 / $total_standings);
				$team_details = $this->portal->get_team_details($team_id, $championship_id, $season_id);
				$club['championships'][$i]['standings'][$j] = array_merge($standing, $team_details);
			}
		}

		for ($i = 0, $count = count($club['championships']); $i < $count; $i++) {
			$championship = $club['championships'][$i];
			$championship_id = $championship['championshipId'];
			$championship_name = $championship['championshipName'];
			$season_id = $championship['seasonId'];
			$season_name = $championship['seasonName'];
			$team_names = [];
			if ($home_teams_only) {
				$team_names = array_values(array_filter($championship['standings'], function ($standing) use ($club_code) {
					return $standing['clubCode'] == $club_code;
				}));
				$team_names = array_map(function ($standing) use ($club_code) {
					return $standing['teamName'];
				}, $team_names);
			}
			$this->logger->add_status($club_code, "Getting calendar (" . ($i + 1) . "/$count): $season_name - $championship_name", 8 / $count);

			if ($championship_id == 85 && $season_id == 31) {
				$standings = $this->fixed_85_31();
			} else if ($championship_id == 44 && $season_id == 36) {
				$standings = $this->fixed_44_36();
			} else {
				$standings = $this->portal->get_championship_calendar($championship_id, $season_id, $team_names);
			}
			$club['championships'][$i]['calendar'] = $standings;
		}

		$merged_championship = $club['championships'];
		foreach ($old_championships as $old_championship) {
			$season_id = $old_championship['seasonId'];
			$championship_id = $old_championship['championshipId'];
			$common = array_values(array_filter($club['championships'], function ($championship) use ($season_id, $championship_id) {
				return $championship['seasonId'] == $season_id && $championship['championshipId'] == $championship_id;
			}));
			if ((empty($common))) {
				$merged_championship[] = $old_championship;
			}
		}
		$club['championships'] = $merged_championship;


		$this->logger->add_status($club_code, "Getting ranking list", 5);
		$players = $this->portal->find_rankings();
		$last_ranking = $players[0];

		$total_rankings = count(Fitet_Portal_Rest::$ranking_types) * count(Fitet_Portal_Rest::$ranking_sex);
		$standing_cursor = 0;

		$players = [];
		for ($i = 0, $count_i = count(Fitet_Portal_Rest::$ranking_types); $i < $count_i; $i++) {
			$type = Fitet_Portal_Rest::$ranking_types[$i];
			for ($j = 0, $count_j = count(Fitet_Portal_Rest::$ranking_sex); $j < $count_j; $j++) {
				$sex = Fitet_Portal_Rest::$ranking_sex[$j];
				$type_name = $type['name'];
				$sex_name = $sex['name'];
				$date = $last_ranking['date'];
				$this->logger->add_status($club_code, "Getting ranking (" . ++$standing_cursor . "/$total_rankings): $date - $type_name - $sex_name", 8 / $total_rankings);
				$players[] = $this->portal->get_ranking($last_ranking['rankingId'], $sex, $type, $club_code);
			}
		}
		$players = array_merge(...$players);

		usort($players, function ($a, $b) {
			return $b['points'] - $a['points'];
		});

		for ($i = 0, $count = count($players); $i < $count; $i++) {
			$player = $players[$i];
			$player_name = $player['playerName'];
			$this->logger->add_status($club_code, "Getting player info (" . ($i + 1) . "/$count): $player_name", 20 / $count);

			$player_infos = $this->portal->find_players($player['playerName'], $player['birthDate']);
			$player_info = $player_infos[0];
			if (count($player_infos) > 1) {
				foreach ($player_infos as $info) {
					$ranking = $this->portal->get_player_history($info['playerId'])['ranking'];
					if (empty($ranking)) {
						continue;
					}
					if ($ranking[0]['position'] == $player['rank']) {
						$player_info = $info;
						break;
					}
				}
			}
			$players[$i] = array_merge($player, $player_info);
		}

		for ($i = 0, $count = count($players); $i < $count; $i++) {
			$player = $players[$i];
			$player_name = $player['playerName'];
			$player_code = $player['playerCode'];
			$this->logger->add_status($club_code, "Getting player season (" . ($i + 1) . "/$count): $player_name - $player_code", 10 / $count);
			$players[$i]['season'] = $this->portal->get_player_season($player['playerId'], $last_ranking['rankingId']);
		}

		for ($i = 0, $count = count($players); $i < $count; $i++) {
			$player = $players[$i];
			$player_name = $player['playerName'];
			$player_code = $player['playerCode'];
			$this->logger->add_status($club_code, "Getting player history (" . ($i + 1) . "/$count): $player_name - $player_code", 10 / $count);
			$players[$i]['history'] = $this->portal->get_player_history($player['playerId']);
		}

		foreach ($players as &$player) {
			$player['best'] = self::calculate_best_ranking(isset($player['history']) ? $player['history']['ranking'] : []);
		}


		foreach ($players as &$player) {
			$player['caps'] = ['tournaments' => 0, 'championships' => 0];
			$caps = array_values(array_filter($club['attendances'], function ($attendance) use ($player) {
				return $attendance['playerCode'] == $player['playerCode'];
			}));
			if (!empty($caps)) {
				$player['caps']['tournaments'] = $caps[0]['count'];
			} else {
				$player['caps']['tournaments'] = 0;
			}
			$player['caps']['championships'] =  0;//$this->calculate_championship_caps($club['championships'],$club_code,$player['playerId']);
		}

		unset($club['attendances']);

		$club['players'] = $players;

		$last_update = new DateTime("now", new DateTimeZone('Europe/Rome')); //first argument "must" be a string
		$last_update->setTimestamp(time()); //adjust the object to correct timestamp
		$club['lastUpdate'] = $last_update->format('d/m/Y H:i:s');

		$club = $this->all_to_utf8($club);

		return $club;

	}


	private static function calculate_championship_caps($resources, $club_code, $player_id) {
		$resources = array_map(function ($championship) {
			return $championship['standings'];
		}, $resources);

		$resources = array_merge(...$resources);

		$resources = array_values(array_filter($resources, function ($standing) use ($club_code) {
			return $standing['clubCode'] == $club_code;
		}));


		$resources = array_map(function ($standing) {
			return $standing['players'];
		}, $resources);

		$resources = array_merge(...$resources);

		$resources = array_values(array_filter($resources, function ($player) use ($player_id) {
			return $player['playerId'] == $player_id;
		}));

		$resources = array_map(function ($player) {
			return $player['pd'];
		}, $resources);

		return array_sum($resources);
	}

	private static function calculate_best_ranking($rankings) {
		if (empty($rankings)) {
			return null;
		}

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

	public static function to_utf8($text) {
		if (FITET_MONITOR_MB_CONVERT_ENCODING_EXIST) {
			return mb_convert_encoding($text, "UTF-8", "ISO-8859-15");
		}
		if (FITET_MONITOR_ICONV_EXIST) {
			return iconv("ISO-8859-15", "UTF-8", $text);
		}
		return utf8_encode($text);

	}

	private function sort_titles($a, $b): int {
		foreach (['season', 'tournament', 'competition', 'player'] as $field) {
			if ($a[$field] != $b[$field]) {
				return strcmp($b[$field], $a[$field]);
			}
		}
		return 0;
	}

	public function all_to_utf8($object) {
		if (is_string($object)) {
			if (empty(json_encode($object))) {
				$to_utf8 = Fitet_Monitor_Manager::to_utf8($object);
				error_log("not encodable  => $to_utf8");
				return $to_utf8;
			}
		}
		if (is_array($object) || is_object($object)) {
			$object = (array)$object;
			foreach (array_keys($object) as $array_key) {
				$object[$array_key] = $this->all_to_utf8($object[$array_key]);
			}
		}
		return $object;

	}

	private function full_championships_history($club_code, $home_teams_only = false) {
		if ($club_code == null)
			throw new Exception("Club code can not be null!");

		$this->logger->add_status($club_code, 'Start updating full championships history');

		$this->logger->add_status($club_code, "Getting info for club $club_code", 0);

		$club = $this->get_club($club_code);
		$this->logger->add_status($club_code, "Getting details for club " . $club['clubCode'] . " " . $club['clubName'], 5);

		$championships = $this->portal->get_club_details($club_code)['championships'];

		for ($i = 0, $count = count($championships); $i < $count; $i++) {
			$championship = $championships[$i];
			$championship_id = $championship['championshipId'];
			$championship_name = $championship['championshipName'];
			$season_id = $championship['seasonId'];
			$season_name = $championship['seasonName'];
			$this->logger->add_status($club_code, "Getting standings (" . ($i + 1) . "/$count): $season_name - $championship_name", 15 / $count);
			$standings = $this->portal->get_championship_standings($championship_id, $season_id);
			$championships[$i]['standings'] = $standings;
		}

		$total_standings = array_sum(array_map(function ($c) {
			return count($c['standings']);
		}, $championships));
		$standing_cursor = 0;
		for ($i = 0, $count_i = count($championships); $i < $count_i; $i++) {
			$championship = $championships[$i];
			for ($j = 0, $count_j = count($championship['standings']); $j < $count_j; $j++) {
				$championship_id = $championship['championshipId'];
				$championship_name = $championship['championshipName'];
				$season_id = $championship['seasonId'];
				$season_name = $championship['seasonName'];

				$team_id = $championship['standings'][$j]['teamId'];
				$team_name = $championship['standings'][$j]['teamName'];
				$this->logger->add_status($club_code, "Getting team info (" . ++$standing_cursor . "/$total_standings): $season_name - $championship_name - $team_name", 50 / $total_standings);
				$team_info = $this->portal->get_team_info($team_id, $championship_id, $season_id);
				$championships[$i]['standings'][$j] = array_merge($championship['standings'][$j], $team_info);
			}
		}

		$standing_cursor = 0;
		$total_standings = array_sum(array_map(function ($c) use ($club_code) {
			return count(array_values(array_filter($c['standings'], function ($s) use ($club_code) {
				return $club_code == $s['clubCode'];
			})));
		}, $championships));

		for ($i = 0, $_count = count($championships); $i < $_count; $i++) {
			$championship = $championships[$i];
			for ($j = 0, $count = count($championship['standings']); $j < $count; $j++) {
				$standing = $championship['standings'][$j];
				if ($club_code != $standing['clubCode']) {
					continue;
				}
				$championship_id = $championship['championshipId'];
				$championship_name = $championship['championshipName'];
				$season_id = $championship['seasonId'];
				$season_name = $championship['seasonName'];

				$team_id = $standing['teamId'];
				$team_name = $standing['teamName'];
				$this->logger->add_status($club_code, "Getting team details (" . ++$standing_cursor . "/$total_standings): $season_name - $championship_name - $team_name", 15 / $total_standings);
				$team_details = $this->portal->get_team_details($team_id, $championship_id, $season_id);
				$championships[$i]['standings'][$j] = array_merge($standing, $team_details);
			}
		}

		for ($i = 0, $count = count($championships); $i < $count; $i++) {
			$championship = $championships[$i];
			$championship_id = $championship['championshipId'];
			$championship_name = $championship['championshipName'];
			$season_id = $championship['seasonId'];
			$season_name = $championship['seasonName'];
			$team_names = [];
			if ($home_teams_only) {
				$team_names = array_values(array_filter($championship['standings'], function ($standing) use ($club_code) {
					return $standing['clubCode'] == $club_code;
				}));
				$team_names = array_map(function ($standing) use ($club_code) {
					return $standing['teamName'];
				}, $team_names);
			}
			$this->logger->add_status($club_code, "Getting calendar (" . ($i + 1) . "/$count): $season_name - $championship_name", 15 / $count);


			if ($championship_id == 85 && $season_id == 31) {
				$standings = $this->fixed_85_31();
			} else if ($championship_id == 44 && $season_id == 36) {
				$standings = $this->fixed_44_36();
			} else {
				$standings = $this->portal->get_championship_calendar($championship_id, $season_id, $team_names);
			}
			$championships[$i]['calendar'] = $standings;
		}

		$championships = $this->all_to_utf8($championships);
		$club['championships'] = $championships;

		$last_update = new DateTime("now", new DateTimeZone('Europe/Rome'));
		$last_update->setTimestamp(time());
		$club['lastUpdate'] = $last_update->format('d/m/Y H:i:s');

		return $club;
	}

	private function fixed_44_36() {
		return json_decode(file_get_contents(__DIR__ . '/44-36.json'));
	}

	private function fixed_85_31() {
		return json_decode(file_get_contents(__DIR__ . '/85-31.json'));
	}


}
