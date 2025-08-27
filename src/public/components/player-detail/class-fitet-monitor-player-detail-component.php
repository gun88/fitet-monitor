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

		$data = array_merge(['history' => ['ranking' => [], 'championships' => [], 'nationalTournaments' => [], 'nationalDoublesTournaments' => [], 'regionalTournaments' => [],], 'season' => []], $data);
		$player = $data;
		$rankings = $player['history']['ranking'];
        $player['showName'] = FITET_MONITOR_SHOW_NAME_IN_PLAYER_DETAIL;

		$has_rankings = !empty($rankings);
		$has_season = !empty($player['season']);
		$has_championships = !empty($player['history']['championships']);
		$has_national_tournaments = !empty($player['history']['nationalTournaments']);
		$has_national_double_tournaments = !empty($player['history']['nationalDoublesTournaments']);
		$has_regional_tournaments = !empty($player['history']['regionalTournaments']);
		return [
			'pageMenu' => $this->menu($has_rankings, $has_season, $has_championships, $has_national_tournaments | $has_national_double_tournaments | $has_regional_tournaments),
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

	private function menu($has_rankings, $has_season, $has_championships, $has_tournaments): string {
		$menu_entries = [];

		if ($has_rankings) $menu_entries[] = '<a href="#ranking"><img alt="ranking" src="' . FITET_MONITOR_ICON_CHART . '"/>' . __('Ranking', 'fitet-monitor') . '</a>';
		if ($has_season) $menu_entries[] = '<a href="#season"><img alt="season" src="' . FITET_MONITOR_ICON_LIST . '"/>' . __('Season', 'fitet-monitor') . '</a>';
		if ($has_championships) $menu_entries[] = '<a href="#championships"><img alt="championships" src="' . FITET_MONITOR_ICON_CALENDAR . '"/>' . __('Championships', 'fitet-monitor') . '</a>';
		if ($has_tournaments) $menu_entries[] = '<a href="#tournaments"><img alt="tournaments" src="' . FITET_MONITOR_ICON_TROPHY . '"/>' . __('Tournaments', 'fitet-monitor') . '</a>';

		return implode('|', $menu_entries);
	}

}
