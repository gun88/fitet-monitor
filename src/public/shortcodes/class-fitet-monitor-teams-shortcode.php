<?php

require_once FITET_MONITOR_DIR . 'public/utils/class-fitet-monitor-utils.php';
require_once FITET_MONITOR_DIR . 'public/includes/class-fitet-monitor-shortcode.php';
require_once FITET_MONITOR_DIR . 'public/components/teams-list/class-fitet-monitor-teams-list-component.php';
require_once FITET_MONITOR_DIR . 'public/components/teams-table/class-fitet-monitor-teams-table-component.php';
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

	protected function attributes() {
		return ['club', 'team', 'season', 'championship', 'mode', 'players-page-id'];
	}

	protected function process_attributes($attributes) {
		$template = [];
		$multi_club = empty($attributes['club']);
		if ($multi_club) {
			// no club found - keeping all
			$resources = $this->manager->get_clubs($template);
		} else {
			$resources = [$this->manager->get_club($attributes['club'], $template)];
		}

		$resources = array_values(array_filter($resources, function ($club) {
			return !empty($club);
		}));

		$resources = $this->extract_championships($resources);
		$resources = $this->filter_championships($resources, $attributes['team'], $attributes['season'], $attributes['championship']);
		$resources = $this->fill_team_rankings($resources);
		$resources = $this->flat_to_teams($resources);
		$resources = $this->sort_teams($resources);
		$resources = $this->filter_empty_teams($resources);
		if (!empty($configuration['players-page-id'])) {
			$resources = $this->fill_players_data($resources, "index.php?page_id=" . $attributes['players-page-id']);
		}
		$resources = $this->fill_teams_data($resources);


		if (empty($resources)) {
			return ['mode' => 'noTeam', 'data' => []];
		}

		if (!empty($configuration['team']) &&
			!empty($configuration['season']) &&
			!empty($configuration['championship'])) {
			return ['mode' => 'single', 'data' => $this->find_team($configuration['team'], $resources)];
		}

		return ['mode' => $configuration['mode'], 'data' => $resources];
	}

	public function wrapped_component($mode) {
		switch ($mode) {
			case 'single':
				return new Fitet_Monitor_Team_Detail_Component($this->plugin_name, $this->version);
			case 'table':
				return new Fitet_Monitor_Teams_Table_Component($this->plugin_name, $this->version); // todo ???? cos'e??
			default:
				return new Fitet_Monitor_Teams_List_Component($this->plugin_name, $this->version);
		}
	}


	private function filter_championships($championships, $team_id = '', $season_id = '', $championship_id = '') {

		$championships = array_filter($championships, function ($championship) use ($championship_id) {
			return empty($championship_id) || $championship['championshipId'] == $championship_id;
		});

		$championships = array_filter($championships, function ($championship) use ($season_id) {
			return empty($season_id) || $championship['seasonId'] == $season_id;
		});

		$championships = array_filter($championships, function ($championship) use ($team_id) {
			return empty($team_id) || !empty(array_filter($championship['standings'], function ($standing) use ($team_id) {
					return $standing['teamId'] == $team_id;
				}));
		});

		return array_values($championships);
	}

	private function flat_to_teams($championships) {

		$standings = array_map(function ($championship) {

			return array_map(function ($standing) use ($championship) {


				$standing['seasonId'] = $championship['seasonId'];
				$standing['seasonName'] = $championship['seasonName'];
				$standing['championshipId'] = $championship['championshipId'];
				$standing['championshipName'] = $championship['championshipName'];
				//$standing['ranking']  = array_search($standing['teamId'], array_column($championship['standings'], 'teamId'));

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

	private function find_team($team_id, $teams) {
		$teams = array_values(array_filter($teams, function ($team) use ($team_id) {
			return $team['teamId'] == $team_id;
		}));
		return empty($teams) ? null : $teams[0];
	}

	private function fill_team_rankings($championships) {
		// todo sposta in update
		return array_map(function ($championship) {
			return Fitet_Monitor_Utils::fill_team_rankings($championship);
		}, $championships);

	}

	private function fill_players_data($teams, $player_base_url) {
		foreach ($teams as &$team) {
			foreach ($team['players'] as &$player) {
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
			$team['teamUrl'] = "index.php?page_id=$post->ID&championship=$championship_id&season=$season_id&team=$team_id";
		}
		return $teams;
	}


}



