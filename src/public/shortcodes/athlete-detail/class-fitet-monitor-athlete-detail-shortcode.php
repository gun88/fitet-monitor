<?php

require_once FITET_MONITOR_DIR . 'public/includes/class-fitet-monitor-shortcode.php';

class Fitet_Monitor_Athlete_Detail_Shortcode extends Fitet_Monitor_Shortcode {

	/**
	 * @var Fitet_Monitor_Manager
	 */
	private $manager;

	public function __construct($version, $plugin_name, $manager) {
		parent::__construct($version, $plugin_name, 'fm-athlete-detail');
		$this->manager = $manager;
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


	public function find_player(array $clubs, $player_code) {
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

		//echo add_query_arg( $wp->query_vars, home_url() );
		//error_log(json_decode($wp->query_vars));

		/*$attributes = $data['attributes'];
		$content = $data['content'];

		$attributes = shortcode_atts(
			['club-code' => '',]
			, $attributes, $this->tag);

		$club_code = $attributes['club-code'];
		if (empty($club_code)) {
			$clubs = $this->manager->get_clubs();
			if (!isset($clubs[0]))
				throw new Exception("No club found");
			$club = $clubs[0];
		} else {
			$club = $this->manager->get_clubs($club_code);
		}*/

		$player_code = explode('-', $_GET['atleta'])[0];

		$clubs = $this->manager->get_clubs();
		$player = $this->find_player($clubs, $player_code);


		$rankings = array_map(function ($ranking) {
			return empty($ranking['position']) ? null : $ranking['position'];
		}, $player['history']['ranking']);
		$points = array_map(function ($ranking) {
			return $ranking['points'];
		}, $player['history']['ranking']);
		$points_label = array_map(function ($ranking) {
			return $ranking['date'];
		}, $player['history']['ranking']);


		$bestRanking = min(array_filter($rankings, function ($r) {
			return $r != null;
		}));
		$bestPoints = max(array_filter($points, function ($p) {
			return $p != null;
		}));
		return [
			'bestRanking' => $bestRanking,
			'bestPoints' => $bestPoints,
			'rankings' => json_encode($rankings),
			'points' => json_encode($points),
			'labels' => json_encode($points_label),
			'content' => $this->to_content($player, $bestRanking),
			'json' => json_encode($player['history']['ranking'])
		];
	}


	private function to_content($player, $bestRanking) {
		$content = "";
		$content .= "<div class='fm-sc-athlete'>";

		$player_name = $player['name'];
		$player_img = $this->to_player_img($player);

		$content .= $player_img;
$player_code = $player['code'];
		$content .= "<div>";
		$content .= "<b>$player_name - $player_code</b>";
		$content .= "<div><b>" . __('Rank') . "</b>: <span>" . $player['rank'] . " (Record: $bestRanking)</span></div>";
		$content .= "<div><b>" . __('Points') . "</b>: <span>" . $player['points'] . "</span></div>";
		$content .= "<div><b>" . __('Category') . "</b>: <span>" . $player['category'] . "</span></div>";
		$content .= "<div><b>" . __('Sector') . "</b>: <span>" . $player['sector'] . "</span></div>";
		$content .= "<div><b>" . __('Birth date') . "</b>: <span>" . $player['birthDate'] . "</span></div>";

		$content .= "</div>";
		$content .= "</div><hr>";


		return $content;
	}


	public function to_player_img($player) {
		$id = $player['id'];
		$player_name = $player['name'];
		$player_image = "http://portale.fitet.org/images/atleti/$id.jpg";
		$player_no_image = "http://portale.fitet.org/images/atleti/m-vuoto.png";
		return "<div><img class='fm-sc-athlete-img' alt='$player_name' src='$player_image' onError='this.onerror=null;this.src=\"$player_no_image\";'/></div>";
	}

}
