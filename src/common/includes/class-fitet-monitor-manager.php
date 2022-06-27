<?php

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

	public function edit_club($club) {
		$this->save_club($club);
	}

	private function save_club($club) {

		if (empty($club['clubCode']))
			throw new Exception("empty club code");

		$club['clubName'] = stripslashes($club['clubName']);

		$club_code = $club['clubCode'];
		$club_codes = get_option($this->plugin_name . 'clubs', []);
		$club_codes[] = $club_code;
		update_option($this->plugin_name . 'clubs', array_filter(array_unique($club_codes)));
		update_option($this->plugin_name . $club_code, $club);

	}

	public function delete_clubs($club_codes) {
		if (!is_array($club_codes))
			$club_codes = [$club_codes];

		$all = get_option($this->plugin_name . 'clubs');
		$toRemove = $club_codes;
		$result = array_diff($all, $toRemove);

		update_option($this->plugin_name . 'clubs', $result);
		foreach ($toRemove as $club_code) {
			delete_option($this->plugin_name . $club_code);
		}
	}

	public function get_club($club_code) {
		return get_option($this->plugin_name . $club_code);
	}

	public function get_clubs() {
		$clubCodes = get_option($this->plugin_name . 'clubs');
		if (!$clubCodes) {
			return [];
		}
		return array_map(function ($clubCode) {
			return $this->get_club($clubCode);
		}, $clubCodes);
	}

	public function find_clubs($club_name_contains) {
		return $this->portal->find_clubs($club_name_contains);
	}

	public function get_status($club_code) {
		return $this->logger->get_status($club_code);
	}

	public function update($club_code) {

		$status_log = $this->logger->get_status($club_code);

		if ($status_log['status'] != 'updating') {

			try {
				$this->logger->reset_status($club_code);
				$club = $this->get_club($club_code);
				$new_club = $this->get_full_club($club_code);
				$club = $this->merge_clubs($club, $new_club);
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


	public function get_full_club($club_code, $home_teams_only = false, $history_length = 2) {
		if ($club_code == null)
			throw new Exception("Club code can not be null!");

		$this->logger->add_status($club_code, 'Start updating');

		$this->logger->add_status($club_code, "Getting info for club $club_code", 0);
		$club = $this->portal->get_club_info($club_code);


		$this->logger->add_status($club_code, "Getting details for club " . $club['clubCode'] . " " . $club['clubName'], 5);
		$club_details = $this->portal->get_club_details($club_code, $history_length);

		$club = array_merge($club, $club_details);

		for ($i = 0, $count = count($club['championships']); $i < $count; $i++) {
			$championship = $club['championships'][$i];
			$championship_id = $championship['id'];
			$championship_name = $championship['name'];
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
				$championship_id = $championship['id'];
				$championship_name = $championship['name'];
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
			return count(array_filter($c['standings'], function ($s) use ($club_code) {
				return $club_code == $s['clubCode'];
			}));
		}, $club['championships']));

		for ($i = 0, $_count = count($club['championships']); $i < $_count; $i++) {
			$championship = $club['championships'][$i];
			for ($j = 0, $count = count($championship['standings']); $j < $count; $j++) {
				$standing = $championship['standings'][$j];
				if ($club_code != $standing['clubCode']) {
					continue;
				}
				$championship_id = $championship['id'];
				$championship_name = $championship['name'];
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
			$championship_id = $championship['id'];
			$championship_name = $championship['name'];
			$season_id = $championship['seasonId'];
			$season_name = $championship['seasonName'];
			$team_names = [];
			if ($home_teams_only) {
				$team_names = array_filter($championship['standings'], function ($standing) use ($club_code) {
					return $standing['clubCode'] == $club_code;
				});
				$team_names = array_map(function ($standing) use ($club_code) {
					return $standing['teamName'];
				}, $team_names);
			}
			$this->logger->add_status($club_code, "Getting calendar (" . ($i + 1) . "/$count): $season_name - $championship_name", 8 / $count);
			$standings = $this->portal->get_championship_calendar($championship_id, $season_id, $team_names);
			$club['championships'][$i]['calendar'] = $standings;
		}

		$this->logger->add_status($club_code, "Getting ranking id list", 5);
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
				$this->logger->add_status($club_code, "Getting ranking (" . ++$standing_cursor . ",$total_rankings): $date - $type_name - $sex_name", 8 / $total_rankings);
				$players[] = $this->portal->get_ranking($last_ranking['id'], $sex['id'], $type['id'], $club_code);
			}
		}
		$players = array_merge(...$players);

		usort($players, function ($a, $b) {
			return $b['points'] - $a['points'];
		});

		for ($i = 0, $count = count($players); $i < $count; $i++) {
			$player = $players[$i];
			$player_name = $player['name'];
			$this->logger->add_status($club_code, "Getting player info (" . ($i + 1) . "/$count): $player_name", 20 / $count);
			$player_info = $this->portal->find_players($player['name'], $player['birthDate'])[0];
			$players[$i] = array_merge($player, $player_info);
		}

		for ($i = 0, $count = count($players); $i < $count; $i++) {
			$player = $players[$i];
			$player_name = $player['name'];
			$player_code = $player['code'];
			$this->logger->add_status($club_code, "Getting player season (" . ($i + 1) . "/$count): $player_name - $player_code", 10 / $count);
			$players[$i]['season'] = $this->portal->get_player_season($player['id'], $last_ranking['id']);
			// todo prova a usare $player
		}

		for ($i = 0, $count = count($players); $i < $count; $i++) {
			$player = $players[$i];
			$player_name = $player['name'];
			$player_code = $player['code'];
			$this->logger->add_status($club_code, "Getting player history (" . ($i + 1) . "/$count): $player_name - $player_code", 10 / $count);
			$players[$i]['history'] = $this->portal->get_player_history($player['id']);
			// todo prova a usare $player

		}

		$club['players'] = $players;
		$club['lastUpdate'] = date("Y-m-d H:i:s");
		return $club;

	}

	private function merge_clubs($club, array $new_club) {
		$new_club['clubLogo'] = $club['clubLogo'];
		$new_club['clubName'] = $club['clubName'];

		return $new_club;
	}




}
