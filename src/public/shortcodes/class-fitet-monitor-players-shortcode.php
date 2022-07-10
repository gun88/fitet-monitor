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

	protected function process_attributes($attributes) {

		if (!empty($attributes['player'])) {
			throw new Exception("implementa!!");
			return ['mode' => 'single', 'data' => $players[0]];
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
		return array_merge(...$players);

	}

	private function add_player_url($players) {
		global $post;
		foreach ($players as &$player) {
			$player_slug = $player['playerCode'] . '-' . urlencode(str_replace(" ", "-", $player['playerName']));
			$player['playerUrl'] = "index.php?page_id=$post->ID&player=$player_slug";
		}
		return $players;
	}

	private function add_club_url($players) {
		// todo implement!
		foreach ($players as &$player) {
			$player['clubPageUrl'] = '';
		}
		return $players;
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
		$resources = $this->add_club_url($resources);

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
		$resources = $this->add_club_url($resources);

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
				$player['best'] = $player['best']['position'] . " " . __('on') . " " . $player['best']['date'];
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


}



