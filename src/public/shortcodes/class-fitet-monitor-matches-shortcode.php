<?php

require_once FITET_MONITOR_DIR . 'public/includes/class-fitet-monitor-shortcode.php';
require_once FITET_MONITOR_DIR . 'public/components/matches-list/class-fitet-monitor-matches-list-component.php';
require_once FITET_MONITOR_DIR . 'public/components/matches-detail/class-fitet-monitor-matches-detail-component.php';


class Fitet_Monitor_Matches_Shortcode extends Fitet_Monitor_Shortcode {

	/**
	 * @var Fitet_Monitor_Manager
	 */
	private $manager;

	public function __construct($version, $plugin_name, $manager) {
		parent::__construct($version, $plugin_name, 'fitet-monitor-matches');
		$this->manager = $manager;
	}

	public function attributes(): array {
		return ['match', 'mode', 'club', 'filter', 'teams-page-id'];
	}

	protected function process_attributes($attributes) {

		if (!empty($attributes['match'])) {
			return ['mode' => 'single', 'data' => $this->single($attributes)];
		}

		return ['mode' => 'list', 'data' => $this->list($attributes)];
	}

	public function wrapped_component($mode) {
		switch ($mode) {
			case 'single':
				return new Fitet_Monitor_Matches_Detail_Component($this->plugin_name, $this->version);
			default:
				return new Fitet_Monitor_Matches_List_Component($this->plugin_name, $this->version);
		}
	}


	private function extract_matchs($clubs) {
		// todo estrai data corrente
		$matchs = array_map(function ($club) {
			return $club['matchs'];
		}, $clubs);
		return array_merge(...$matchs);

	}

	private function add_match_url($matchs) {
		global $post;
		foreach ($matchs as &$match) {
			$match['matchUrl'] = Fitet_Monitor_Utils::match_page_url("index.php?page_id=$post->ID", $match['matchCode']);
		}
		return $matchs;
	}

	private function single($attributes) {
		// todo from fitet
		return [];
	}

	private function list($attributes) {
		$template = ['championships' => []];
		$multi_club = empty($attributes['club']);
		if ($multi_club) {
			// no club found - keeping all
			$resources = $this->manager->get_clubs($template);
		} else {
			$resources = [$this->manager->get_club($attributes['club'], $template)];
		}


		// todo get last season id
		$last_season_id = 37;

		foreach ($resources as &$club) {
			$club = array_values(array_filter($club['championships'], function ($championship) use ($last_season_id) {
				return $championship['seasonId'] == $last_season_id;
			}));
		}

		//$resources = $resources['championships'];


		/**/


		return $resources;
		/*
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
		];*/
	}

}



