<?php

require_once FITET_MONITOR_DIR . 'public/includes/class-fitet-monitor-shortcode.php';
require_once FITET_MONITOR_DIR . 'public/components/player-card/class-fitet-monitor-player-card-component.php';
require_once FITET_MONITOR_DIR . 'public/components/player-ranking/class-fitet-monitor-player-ranking-component.php';
require_once FITET_MONITOR_DIR . 'public/components/player-season/class-fitet-monitor-player-season-component.php';
require_once FITET_MONITOR_DIR . 'public/components/player-championships/class-fitet-monitor-player-championships-component.php';
require_once FITET_MONITOR_DIR . 'public/components/player-tournaments/class-fitet-monitor-player-regional-tournaments-component.php';
require_once FITET_MONITOR_DIR . 'public/components/player-tournaments/class-fitet-monitor-player-national-tournaments-component.php';
require_once FITET_MONITOR_DIR . 'public/components/player-tournaments/class-fitet-monitor-player-national-doubles-tournaments-component.php';

class Fitet_Monitor_Player_Detail_Component extends Fitet_Monitor_Component {

	protected function components() {
		return [
			'playerCard' => new Fitet_Monitor_Player_Card_Component($this->plugin_name, $this->version, []),
			'playerRanking' => new Fitet_Monitor_Player_Ranking_Component($this->plugin_name, $this->version),
			'playerSeason' => new Fitet_Monitor_Player_Season_Component($this->plugin_name, $this->version),
			'championships' => new Fitet_Monitor_Player_Championships_Component($this->plugin_name, $this->version),
			'regionalTournaments' => new Fitet_Monitor_Player_Regional_Tournament_Component($this->plugin_name, $this->version),
			'nationalTournaments' => new Fitet_Monitor_Player_National_Tournament_Component($this->plugin_name, $this->version),
			'nationalDoublesTournaments' => new Fitet_Monitor_Player_National_Doubles_Tournament_Component($this->plugin_name, $this->version),
		];
	}

	protected function process_data($data) {
		/*

				$attributes = $data['attributes'];
				$content = $data['content'];

				$attributes = shortcode_atts(
					['club-code' => '', 'show-club' => false, 'detail-page' => 'atleta']
					, $attributes, $this->tag);

				$show_club = $attributes['show-club'];
				$club_code = $attributes['club-code'];
				if (empty($club_code)) {
					$clubs = $this->manager->get_clubs();
				} else {
					$clubs = [$this->manager->get_club($club_code)];
				}

				$detail_page = $attributes['detail-page'];

				$get_pages = get_pages();
				$get_pages = array_filter($get_pages, function ($page) use ($detail_page) {
					return $page->post_name == $detail_page;
				});

				if (empty($get_pages)) {
					$guid = null;
				} else {
					$guid = $get_pages[0]->guid;
				}

				if (!$_GET['atleta']) {
					// todo gestire
					return [];
				}

				$player_id = explode('-', $_GET['atleta'])[0];

				$player = $this->find_player($clubs, $player_id);*/


		$player = $data;
		global $post;


		$player['link'] = ("index.php?page_id=$post->ID" . '&player=' . $player['code'] . "-" . str_replace(' ', '-', $player['name']));


		$rankings = $player['history']['ranking'];

		$has_rankings = !empty($rankings);
		$has_season = !empty($player['season']);
		$has_championships = !empty($player['history']['championships']);
		$has_national_tournaments = !empty($player['history']['nationalTournaments']);
		$has_national_double_tournaments = !empty($player['history']['nationalDoublesTournaments']);
		$has_regional_tournaments = !empty($player['history']['regionalTournaments']);
		return [
			'pageMenu' => $this->menu($has_rankings, $has_season, $has_championships, $has_national_tournaments, $has_national_double_tournaments, $has_regional_tournaments),
			'rankingClass' => $has_rankings ? '' : 'fm-player-detail-hidden',
			'seasonClass' => $has_season ? '' : 'fm-player-detail-hidden',
			'championshipsClass' => $has_championships ? '' : 'fm-player-detail-hidden',
			'nationalTournamentsClass' => $has_national_tournaments ? '' : 'fm-player-detail-hidden',
			'nationalDoublesTournamentsClass' => $has_national_double_tournaments ? '' : 'fm-player-detail-hidden',
			'regionalTournamentsClass' => $has_regional_tournaments ? '' : 'fm-player-detail-hidden',
			'mainContent' => $this->components['playerCard']->render($player),
			'ranking' => $has_rankings ? $this->components['playerRanking']->render($rankings) : '',
			'season' => $has_season ? $this->components['playerSeason']->render($player) : '',
			'championships' => $has_championships ? $this->components['championships']->render($player) : '',
			'regionalTournaments' => $has_regional_tournaments ? $this->components['regionalTournaments']->render($player) : '',
			'nationalTournaments' => $has_national_tournaments ? $this->components['nationalTournaments']->render($player) : '',
			'nationalDoublesTournaments' => $has_national_double_tournaments ? $this->components['nationalDoublesTournaments']->render($player) : '',
		];
	}

	private function menu($has_rankings, $has_season, $has_championships, $has_national_tournaments, $has_national_double_tournaments, $has_regional_tournaments) {
		$menu_entries = [];

		if ($has_rankings) $menu_entries[] = '<a href="#ranking">' . __('Ranking') . '</a>';
		if ($has_season) $menu_entries[] = '<a href="#season">' . __('Season') . '</a>';
		if ($has_championships) $menu_entries[] = '<a href="#championships">' . __('Championships') . '</a>';
		if ($has_national_tournaments | $has_national_double_tournaments | $has_regional_tournaments)
			$menu_entries[] = '<a href="#tournaments">' . __('Tournaments') . '</a>';

		return implode('|', $menu_entries);
	}

}
