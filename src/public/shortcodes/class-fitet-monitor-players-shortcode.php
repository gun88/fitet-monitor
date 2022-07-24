<?php

require_once FITET_MONITOR_DIR . 'public/includes/class-fitet-monitor-shortcode.php';
require_once FITET_MONITOR_DIR . 'public/components/players-list/class-fitet-monitor-players-list-component.php';
require_once FITET_MONITOR_DIR . 'public/components/players-table/class-fitet-monitor-players-table-component.php';
require_once FITET_MONITOR_DIR . 'public/components/players-caps/class-fitet-monitor-players-caps-component.php';
require_once FITET_MONITOR_DIR . 'public/components/player-detail/class-fitet-monitor-player-detail-component.php';


class Fitet_Monitor_Players_Shortcode extends Fitet_Monitor_Shortcode {

	/**
	 * @var Fitet_Monitor_Manager
	 */
	private $manager;

	public function __construct($version, $plugin_name, $manager) {
		parent::__construct($version, $plugin_name, 'fitet-monitor-players');
		$this->manager = $manager;
	}

	public function attributes(): array {
		return ['player', 'mode', 'club', 'filter', 'teams-page-id'];
	}



	public function devSeasonFix($resources_) {

		foreach ($resources_ as &$resources) {

			$resources['season'] = [];
			for ($i = 0; $i < 20; $i++) {
				$doubleval = doubleval(rand(-220, 220) . '.' . rand(0, 2000));
				$resources['season'][] = [
					'opponent' => ['MASSARELLI MAURIZIO', 'ZAPPACOSTA GIANCARLO', 'PIPPO BAUDO'][rand(0, 2)],
					'date' => ['01-02-2021', '05-12-2021', '29-04-2022'][rand(0, 2)],
					'match' => ['C2', 'TN Lungo TN Lungo TN Lungo TN Lungo TN Lungo', 'Torneo 1'][rand(0, 2)],
					'win' => $doubleval > 0,
					'points' => $doubleval,
				];
			}
		}

		return $resources_;
	}

	protected function process_attributes($attributes) {

		if (!empty($attributes['player'])) {
			return ['mode' => 'single', 'data' => $this->single($attributes)];
		}
		if ($attributes['mode'] == 'table') {
			return ['mode' => 'table', 'data' => $this->table($attributes)];
		}
		if ($attributes['mode'] == 'caps') {
			return ['mode' => 'caps', 'data' => $this->caps($attributes)];
		}

		return ['mode' => 'list', 'data' => $this->list($attributes)];
	}

	public function wrapped_component($mode) {
		switch ($mode) {
			case 'single':
				return new Fitet_Monitor_Player_Detail_Component($this->plugin_name, $this->version);
			case 'table':
				return new Fitet_Monitor_Players_Table_Component($this->plugin_name, $this->version);
			case 'caps':
				return new Fitet_Monitor_Players_Caps_Component($this->plugin_name, $this->version);
			default:
				return new Fitet_Monitor_Players_List_Component($this->plugin_name, $this->version);
		}
	}


	private function extract_players($clubs) {
		$players = array_map(function ($club) {
			return $club['players'];
		}, $clubs);
		$players = array_merge(...$players);
		$players = array_values(array_filter($players, function ($player) {
			return !Fitet_Monitor_Utils::is_hidden($player['playerCode']);
		}));
		return $players;

	}

	private function add_player_url($players) {
		global $post;
		foreach ($players as &$player) {
			$player['playerUrl'] = Fitet_Monitor_Utils::player_page_url("index.php?page_id=$post->ID", $player['playerCode'], $player['playerName']);
		}
		return $players;
	}

	private function add_team_data($players, $team_page_id) {
		foreach ($players as &$player) {
			foreach ($player['history']['championships'] as &$championship) {
				$championship_id = $championship['championshipId'];
				$season_id = $championship['seasonId'];
				$team_name = $championship['teamName'];
				$team_id = Fitet_Monitor_Utils::team_id_by_name($championship_id, $season_id, $team_name);
				$club_code = Fitet_Monitor_Utils::club_code_by_team_id($championship_id, $season_id, $team_id);
				$championship['clubCode'] = $club_code;
				$championship['clubLogo'] = Fitet_Monitor_Utils::club_logo_by_code($club_code);
				if (Fitet_Monitor_Utils::team_loaded($championship_id, $season_id, $team_id))
					$championship['teamPageUrl'] = "index.php?page_id=$team_page_id&season=$season_id&championship=$championship_id&team=$team_id";
			}

		}
		return $players;
	}

	private function add_season_data($players, $player_page_id, $multi_club) {
		foreach ($players as &$player) {
			foreach ($player['season'] as &$season) {
				$season['opponentPlayerName'] = $season['opponent'];
				unset($season['opponent']);
				$season['opponentPlayerId'] = Fitet_Monitor_Utils::player_id_by_name($season['opponentPlayerName']);
				$season['opponentPlayerPageUrl'] = '';
				if (!empty($season['opponentPlayerId'])) {
					if ($multi_club) {
						$opponent_player_code = Fitet_Monitor_Utils::player_code_by_id($season['opponentPlayerId']);
					} else {
						$opponent_player_code = Fitet_Monitor_Utils::player_code_by_id($season['opponentPlayerId'], $player['clubCode']);
					}
					if (!empty($opponent_player_code)) {
						$season['opponentPlayerPageUrl'] = Fitet_Monitor_Utils::player_page_url("index.php?page_id=$player_page_id", $opponent_player_code, $season['opponentPlayerName']);
					}
				}
			}
		}
		return $players;
	}

	private function add_tournament_data($players, $player_page_id, $multi_club) {
		foreach ($players as &$player) {
			foreach ($player['history']['nationalDoublesTournaments'] as &$tournament) {
				$tournament['partnerPlayerName'] = $this->extract_partner($player['playerName'], $tournament['team']);
				unset($tournament['team']);
				$tournament['partnerPlayerId'] = Fitet_Monitor_Utils::player_id_by_name($tournament['partnerPlayerName']);
				$tournament['partnerPlayerPageUrl'] = '';
				if (!empty($tournament['partnerPlayerId'])) {
					if ($multi_club) {
						$partner_player_code = Fitet_Monitor_Utils::player_code_by_id($tournament['partnerPlayerId']);
					} else {
						$partner_player_code = Fitet_Monitor_Utils::player_code_by_id($tournament['partnerPlayerId'], $player['clubCode']);
					}
					if (!empty($partner_player_code)) {
						$tournament['partnerPlayerPageUrl'] = Fitet_Monitor_Utils::player_page_url("index.php?page_id=$player_page_id", $partner_player_code, $tournament['partnerPlayerName']);
					}
				}
			}
		}
		return $players;
	}

	private function add_club_data($players) {
		// todo implement!
		foreach ($players as &$player) {
			$player['clubPageUrl'] = '';
			$player['clubLogo'] = Fitet_Monitor_Utils::club_logo_by_code($player['clubCode']);
		}
		return $players;
	}

	private function single($attributes) {
		$template = ['players' => ''];
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

		$resources = $this->extract_players($resources);
		$player_code = explode('-', $attributes['player'])[0];
		$resources = array_values(array_filter($resources, function ($player) use ($player_code) {
			return $player['playerCode'] == $player_code;
		}));

		if (empty($resources)) {
			return ['multiClub' => $multi_club];
		}

		global $post;

		$resources = $this->add_player_best($resources);
		$resources = $this->add_player_url($resources);
		$resources = $this->add_team_data($resources, $attributes['teams-page-id']);

		// todo remove after dev
		if (FITET_MONITOR_IS_DEV)
			$resources = $this->devSeasonFix($resources);
		// fine remove

		$resources = $this->add_season_data($resources, $post->ID, $multi_club);
		$resources = $this->add_tournament_data($resources, $post->ID, $multi_club);
		$resources = $this->add_multi_club($resources, $multi_club);

		$resources = $resources[0];


		$resources['multiClub'] = $multi_club;

		return $resources;
	}

	private function list($attributes) {
		$template = ['players' => [
			'playerId' => '',
			'playerCode' => '',
			'playerName' => '',
			'points' => '',
			'rank' => '',
			'best' => '',
			'category' => '',
			//'diff' => '',
			'sector' => '',
			//'region' => '',
			'type' => '',
			//'sex' => '',
			//'birthDate' => '',
			'clubName' => '',
			'clubCode' => '',
		]];
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


		$resources = $this->extract_players($resources);

		global $post;
		$caps_url = "index.php?page_id=$post->ID&mode=caps";
		$table_url = "index.php?page_id=$post->ID&mode=table";
		if (!empty($attributes['filter'])) {
			$resources = $this->filterPlayers($resources, $attributes['filter']);
			$table_url .= "&filter=" . $attributes['filter'];
		}

		$resources = $this->add_player_best($resources);
		$resources = $this->add_player_url($resources);
		$resources = $this->add_multi_club($resources, $multi_club);

		usort($resources, function ($p1, $p2) {
			return $p2['points'] - $p1['points'];
		});

		return [
			'players' => $resources,
			'filter' => $attributes['filter'],
			'tableUrl' => $table_url,
			'capsUrl' => $caps_url
		];
	}

	private function table($attributes) {
		$template = ['players' => [
			'playerId' => '',
			'playerCode' => '',
			'playerName' => '',
			'points' => '',
			'rank' => '',
			'best' => '',
			'category' => '',
			'diff' => '',
			'sector' => '',
			'region' => '',
			'type' => '',
			'sex' => '',
			'birthDate' => '',
			'clubName' => '',
			'clubCode' => '',
		]];
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

		$resources = $this->extract_players($resources);

		global $post;
		$caps_url = "index.php?page_id=$post->ID&mode=caps";
		$list_url = "index.php?page_id=$post->ID";

		$resources = $this->add_player_url($resources);
		$resources = $this->add_club_data($resources);

		usort($resources, function ($p1, $p2) {
			return $p2['points'] - $p1['points'];
		});

		return [
			'multiClub' => $multi_club,
			'players' => $resources,
			'listUrl' => $list_url,
			'capsUrl' => $caps_url
		];
	}

	private function caps($attributes) {
		$template = ['players' => [
			'playerId' => '',
			'playerCode' => '',
			'playerName' => '',
			'caps' => '',
			'clubName' => '',
			'clubCode' => '',
		]];
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

		$resources = $this->extract_players($resources);
		$resources = $this->add_club_data($resources);

		$resources = array_map(function ($player) {
			return [
				'playerId' => $player['playerId'],
				'playerCode' => $player['playerCode'],
				'playerName' => $player['playerName'],
				'tournaments' => !empty($player['caps']) ? $player['caps']['tournaments'] : '', // todo restore
				'championships' => !empty($player['caps']) ? $player['caps']['championships'] : '', // todo restore
				'clubCode' => $player['clubCode'],
				'clubName' => $player['clubName'],
			];
		}, $resources);


		global $post;
		$table_url = "index.php?page_id=$post->ID&mode=table";
		$list_url = "index.php?page_id=$post->ID";

		$resources = $this->add_player_url($resources);
		$resources = $this->add_club_data($resources);

		return [
			'multiClub' => $multi_club,
			'players' => $resources,
			'listUrl' => $list_url,
			'tableUrl' => $table_url
		];
	}

	private function filterPlayers($players, $filter) {
		return array_values(array_filter($players, function ($player) use ($filter) {
			return $player['type'] == $filter;
		}));
	}

	private function add_player_best($players) {
		return array_map(function ($player) {
			if (!empty($player['best'])) {
				$player['best'] = $player['best']['position'] . " " . __('on', 'fitet-monitor') . " " . $player['best']['date'];
			}
			return $player;
		}, $players);
	}

	private function add_multi_club($players, bool $multi_club) {
		return array_map(function ($player) use ($multi_club) {
			$player['multiClub'] = $multi_club;
			return $player;
		}, $players);
	}

	private function extract_partner($player_name, $team) {
		$team = explode('/', $team);
		$team = array_map(function ($partner_player_name) {
			return trim($partner_player_name);
		}, $team);

		return array_values(array_filter($team, function ($partner_player_name) use ($player_name) {
			return trim($player_name) != $partner_player_name;
		}))[0];

	}


}



