<?php

require_once FITET_MONITOR_DIR . 'public/includes/class-fitet-monitor-shortcode.php';
require_once FITET_MONITOR_DIR . 'public/components/athlete-card/class-fitet-monitor-athlete-card-component.php';
require_once FITET_MONITOR_DIR . 'public/components/athlete-ranking/class-fitet-monitor-athlete-ranking-component.php';
require_once FITET_MONITOR_DIR . 'public/components/athlete-season/class-fitet-monitor-athlete-season-component.php';
require_once FITET_MONITOR_DIR . 'public/components/athlete-championships/class-fitet-monitor-athlete-championships-component.php';
require_once FITET_MONITOR_DIR . 'public/components/athlete-tournaments/class-fitet-monitor-athlete-regional-tournaments-component.php';
require_once FITET_MONITOR_DIR . 'public/components/athlete-tournaments/class-fitet-monitor-athlete-national-tournaments-component.php';
require_once FITET_MONITOR_DIR . 'public/components/athlete-tournaments/class-fitet-monitor-athlete-national-doubles-tournaments-component.php';

class Fitet_Monitor_Athlete_Detail_Shortcode extends Fitet_Monitor_Shortcode {

	/**
	 * @var Fitet_Monitor_Manager
	 */
	private $manager;

	public function __construct($version, $plugin_name, $manager) {
		parent::__construct($version, $plugin_name, 'fm-athlete-detail');
		$this->manager = $manager;
	}

	protected function components() {
		return [
			'athleteCard' => new Fitet_Monitor_Athlete_Card_Component($this->plugin_name, $this->version, []),
			'athleteRanking' => new Fitet_Monitor_Athlete_Ranking_Component($this->plugin_name, $this->version),
			'athleteSeason' => new Fitet_Monitor_Athlete_Season_Component($this->plugin_name, $this->version),
			'championships' => new Fitet_Monitor_Athlete_Championships_Component($this->plugin_name, $this->version),
			'regionalTournaments' => new Fitet_Monitor_Athlete_Regional_Tournament_Component($this->plugin_name, $this->version),
			'nationalTournaments' => new Fitet_Monitor_Athlete_National_Tournament_Component($this->plugin_name, $this->version),
			'nationalDoublesTournaments' => new Fitet_Monitor_Athlete_National_Doubles_Tournament_Component($this->plugin_name, $this->version),
		];
	}


	public function enqueue_scripts() {
		$file = FITET_MONITOR_DIR . "public/assets/chart.min.js";
		$file = plugin_dir_path($file) . basename($file);
		Fitet_Monitor_Helper::enqueue_script("chart.js", $file, [], $this->version, false);
		parent::enqueue_scripts();
	}

	protected function script_dependencies(): array {
		return ['chart.js', 'jquery'];
	}


	public function find_player($clubs, $player_code) {
		foreach ($clubs as $club) {
			foreach ($club['players'] as $player) {
				if ($player['code'] == $player_code) {
					return $player;
				}
			}
		}
		throw new Exception("Player with code: $player_code not found");
	}

	protected function process_data($data) {


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

		$player_code = explode('-', $_GET['atleta'])[0];

		$player = $this->find_player($clubs, $player_code);


		$player['link'] = $guid != null ? ($guid . '&atleta=' . $player['code'] . "-" . str_replace(' ', '-', $player['name'])) : null;


		$rankings = $player['history']['ranking'];

		$has_rankings = !empty($rankings);
		$has_season = !empty($player['season']);
		$has_championships = !empty($player['history']['championships']);
		$has_national_tournaments = !empty($player['history']['nationalTournaments']);
		$has_national_double_tournaments = !empty($player['history']['nationalDoublesTournaments']);
		$has_regional_tournaments = !empty($player['history']['regionalTournaments']);
		return [
			'rankingClass' => $has_rankings ? '' : 'fm-athlete-detail-hidden',
			'seasonClass' => $has_season ? '' : 'fm-athlete-detail-hidden',
			'championshipsClass' => $has_championships ? '' : 'fm-athlete-detail-hidden',
			'nationalTournamentsClass' => $has_national_tournaments ? '' : 'fm-athlete-detail-hidden',
			'nationalDoublesTournamentsClass' => $has_national_double_tournaments ? '' : 'fm-athlete-detail-hidden',
			'regionalTournamentsClass' => $has_regional_tournaments ? '' : 'fm-athlete-detail-hidden',
			'mainContent' => $this->components['athleteCard']->render($player),
			'ranking' => $has_rankings ? $this->components['athleteRanking']->render($rankings) : '',
			'season' => $has_season ? $this->components['athleteSeason']->render($player) : '',
			'championships' => $has_championships ? $this->components['championships']->render($player) : '',
			'regionalTournaments' => $has_regional_tournaments ? $this->components['regionalTournaments']->render($player) : '',
			'nationalTournaments' => $has_national_tournaments ? $this->components['nationalTournaments']->render($player) : '',
			'nationalDoublesTournaments' => $has_national_double_tournaments ? $this->components['nationalDoublesTournaments']->render($player) : '',
		];
	}


}
