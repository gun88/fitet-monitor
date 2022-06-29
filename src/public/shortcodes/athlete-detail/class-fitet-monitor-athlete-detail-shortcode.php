<?php

require_once FITET_MONITOR_DIR . 'public/includes/class-fitet-monitor-shortcode.php';
require_once FITET_MONITOR_DIR . 'public/components/athlete-card/class-fitet-monitor-athlete-card-component.php';
require_once FITET_MONITOR_DIR . 'public/components/athlete-season/class-fitet-monitor-athlete-season-component.php';
require_once FITET_MONITOR_DIR . 'public/components/athlete-ranking/class-fitet-monitor-athlete-ranking-component.php';

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


		$season = $player['season'];
		return [
			'mainContent' => $this->components['athleteCard']->render($player),
			'ranking' => empty($rankings) ? '' : $this->components['athleteRanking']->render($rankings),
			'season' => empty($season) ? '' : $this->components['athleteSeason']->render($season),
			'json' => json_encode($player)
		];
	}


}
