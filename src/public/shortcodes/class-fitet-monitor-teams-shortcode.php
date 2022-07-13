<?php

require_once FITET_MONITOR_DIR . 'public/utils/class-fitet-monitor-utils.php';
require_once FITET_MONITOR_DIR . 'public/includes/class-fitet-monitor-shortcode.php';
require_once FITET_MONITOR_DIR . 'public/components/teams-list/class-fitet-monitor-teams-list-component.php';
require_once FITET_MONITOR_DIR . 'public/components/team-detail/class-fitet-monitor-team-detail-component.php';


class Fitet_Monitor_Teams_Shortcode extends Fitet_Monitor_Shortcode {

	/**
	 * @var Fitet_Monitor_Manager
	 */
	private $manager;

	public function __construct($version, $plugin_name, $manager) {
		parent::__construct($version, $plugin_name, 'fitet-monitor-teams');
		$this->manager = $manager;

	}

	private function last_season_id(array $resources) {
		$resources = array_map(function ($club) {
			return $club['championships'];
		}, $resources);
		$resources = array_merge(...$resources);
		$resources = array_map(function ($championship) {
			return intval($championship['seasonId']);
		}, $resources);

		$resources = array_unique($resources);
		rsort($resources);
		$resources = $resources[0];
		return $resources;
	}

	protected function attributes() {
		return ['club', 'team', 'season', 'championship', 'mode', 'players-page-id'];
	}

	protected function process_attributes($attributes) {

		if (!empty($attributes['championship']) && !empty($attributes['season']) && !empty($attributes['team'])) {
			return ['mode' => 'single', 'data' => $this->single($attributes)];
		}

		return ['mode' => 'list', 'data' => $this->list($attributes)];
	}

	public function wrapped_component($mode) {
		switch ($mode) {
			case 'single':
				return new Fitet_Monitor_Team_Detail_Component($this->plugin_name, $this->version);
			default:
				return new Fitet_Monitor_Teams_List_Component($this->plugin_name, $this->version);
		}
	}

	private function filter_championships($championships, $season_id = '', $championship_id = '') {

		$championships = array_filter($championships, function ($championship) use ($championship_id) {
			return empty($championship_id) || $championship['championshipId'] == $championship_id;
		});

		$championships = array_filter($championships, function ($championship) use ($season_id) {
			return empty($season_id) || $championship['seasonId'] == $season_id;
		});

		return array_values($championships);
	}


	private function flat_to_teams($championships, $main_team_club_code = '', $main_team_id = '', $add_calendar = false, $add_standings = false) {

		global $post;
		$standings = array_map(function ($championship) use ($add_calendar, $add_standings, $post, $main_team_club_code, $main_team_id) {

			return array_map(function ($standing) use ($championship, $add_calendar, $add_standings, $post, $main_team_club_code, $main_team_id) {


				$standing['seasonId'] = $championship['seasonId'];
				$standing['seasonName'] = $championship['seasonName'];
				$standing['championshipId'] = $championship['championshipId'];
				$standing['championshipName'] = $championship['championshipName'];
				//$standing['ranking']  = array_search($standing['teamId'], array_column($championship['standings'], 'teamId'));

				if ($add_calendar) {
					$standing['calendar'] = $championship['calendar'];

				}

				if ($add_standings) {
					$standing['standings'] = array_map(function ($standing) use ($championship, $post, $main_team_club_code, $main_team_id) {
						$club_code = Fitet_Monitor_Utils::club_code_by_team_id($championship['championshipId'], $championship['seasonId'], $standing['teamId']);
						$standing['clubLogo'] = Fitet_Monitor_Utils::club_logo_by_code($club_code);
						$loaded = Fitet_Monitor_Utils::team_loaded($championship['championshipId'], $championship['seasonId'], $standing['teamId']);

						$standing['mainTeam'] = ($standing['teamId'] == $main_team_id);

						if ($loaded && (empty($main_team_club_code) || $main_team_club_code == $club_code)) {
							$championship_id = $championship['championshipId'];
							$season_id = $championship['seasonId'];
							$team_id = $standing['teamId'];
							$standing['teamPageUrl'] = "index.php?page_id=$post->ID&championship=$championship_id&season=$season_id&team=$team_id";
						} else {
							$standing['teamPageUrl'] = '';
						}
						return $standing;
					}, $championship['standings']);

				}

				return $standing;
			}, $championship['standings']);

		}, $championships);

		$standings = array_merge(...$standings);


		$standings = array_filter($standings, function ($standing) {
			return isset($standing['players']);
		});

		return array_values($standings);

	}

	private function extract_championships($clubs) {
		$championships = array_map(function ($club) {
			return $club['championships'];
		}, $clubs);
		return array_merge(...$championships);

	}

	private function sort_teams($teams) {
		usort($teams, function ($t1, $t2) {
			if ($t1['seasonId'] == $t2['seasonId']) {
				return $t1['championshipId'] - $t2['championshipId'];
			}
			return $t2['seasonId'] - $t1['seasonId'];
		});
		return $teams;
	}


	private function filter_empty_teams($teams) {
		return array_values(array_filter($teams, function ($team) {
			return !empty($team['players']);
		}));
	}

	private function fill_team_rankings($championships) {
		// todo sposta in update
		return array_map(function ($championship) {
			return Fitet_Monitor_Utils::fill_team_rankings($championship);
		}, $championships);

	}

	private function fill_players_data($teams, $player_base_url, $multi_club) {
		foreach ($teams as &$team) {
			foreach ($team['players'] as &$player) {
				$player['multiClub'] = $multi_club;
				$player['playerImageUrl'] = Fitet_Monitor_Utils::player_image_url($player['playerId']);
				$p = Fitet_Monitor_Utils::player_by_id($player['playerId']);
				if ($p == null)
					continue;
				$player = array_merge($player, $p);
				$player['playerUrl'] = Fitet_Monitor_Utils::player_page_url($player_base_url, $player['playerCode'], $player['playerName']);
			}
		}
		return $teams;
	}

	private function fill_teams_data(array $teams) {
		global $post;
		foreach ($teams as &$team) {

			$championship_id = $team['championshipId'];
			$season_id = $team['seasonId'];
			$team_id = $team['teamId'];
			$team['teamPageUrl'] = "index.php?page_id=$post->ID&championship=$championship_id&season=$season_id&team=$team_id";
		}
		return $teams;
	}

	private function single($attributes) {

		$template = ['championships' => ''];
		$multi_club = empty($attributes['club']);
		if ($multi_club) {
			// no club found - keeping all
			$club_code = '';
			$resources = $this->manager->get_clubs($template);
		} else {
			$club_code = $attributes['club'];
			$resources = [$this->manager->get_club($club_code, $template)];
		}

		$resources = array_values(array_filter($resources, function ($club) {
			return !empty($club);
		}));

		$resources = $this->extract_championships($resources);

		$resources = $this->filter_championships($resources, $attributes['season'], $attributes['championship']);
		file_put_contents("/var/www/html/wp-content/plugins/fitet-monitor/src/public/shortcodes/tmp.json", json_encode($resources, 128));
		$resources = $this->fill_team_rankings($resources);
		$resources = $this->flat_to_teams($resources, $club_code, $attributes['team'], true, true);

		$resources = $this->filter_teams($resources, $attributes['team']);
		$resources = $this->sort_teams($resources);
		$resources = $this->filter_empty_teams($resources);
		$resources = $this->fill_players_data($resources, "index.php?page_id=" . $attributes['players-page-id'], $multi_club);

		$resources = $this->fill_teams_data($resources);


		if (empty($resources)) {
			return [
				'teamName' => 'N/A',
				'championshipName' => 'N/A',
				'seasonName' => 'N/A',
				'ranking' => 'N/A',
				'teamStatus' => 'neutral',
				'calendar' => [],
				'standings' => [],
			];
		}

		return $resources[0];

	}

	private function list($attributes) {
		$template = ['championships' => ''];
		$multi_club = empty($attributes['club']);
		if ($multi_club) {
			// no club found - keeping all
			$resources = $this->manager->get_clubs($template);
		} else {
			$resources = [$this->manager->get_club($attributes['club'], $template)];
		}

		if (empty($attributes['season'])) {
			$attributes['season'] = $this->last_season_id($resources);
		}

		$resources = array_values(array_filter($resources, function ($club) {
			return !empty($club);
		}));

		$resources = $this->extract_championships($resources);
		$seasons = $this->extract_seasons($resources);
		$resources = $this->filter_championships($resources, $attributes['season']);
		$resources = $this->fill_team_rankings($resources);
		$resources = $this->flat_to_teams($resources);
		$resources = $this->sort_teams($resources);
		$resources = $this->filter_empty_teams($resources);
		$resources = $this->fill_players_data($resources, "index.php?page_id=" . $attributes['players-page-id'], $multi_club);

		$resources = $this->fill_teams_data($resources);

		return [
			'teams' => $resources,
			'seasons' => $seasons,
			'multiClub' => $multi_club,
			'seasonId' => $attributes['season'],
		];
	}

	private function extract_seasons($resources) {
		$resources = array_unique(array_map(function ($championship) {
			return ['seasonId' => $championship['seasonId'], 'seasonName' => $championship['seasonName']];
		}, $resources), SORT_REGULAR);

		usort($resources, function ($s1, $s2) {
			return strcmp($s2['seasonName'], $s1['seasonName']);
		});

		return $resources;
	}

	private function filter_teams($teams, $team_id) {
		return array_values(array_filter($teams, function ($team) use ($team_id) {
			return $team['teamId'] == $team_id;
		}));
	}


}
