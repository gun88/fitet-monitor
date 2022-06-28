<?php

require_once FITET_MONITOR_DIR . 'public/includes/class-fitet-monitor-shortcode.php';

class Fitet_Monitor_Athletes_List_Shortcode extends Fitet_Monitor_Shortcode {

	/**
	 * @var Fitet_Monitor_Manager
	 */
	private $manager;

	public function __construct($version, $plugin_name, $manager) {
		parent::__construct($version, $plugin_name, 'fm-athletes-list');
		$this->manager = $manager;
	}


	public function to_player_img($player, $player_url) {
		$id = $player['id'];
		$player_name = $player['name'];
		$player_image = "http://portale.fitet.org/images/atleti/$id.jpg";
		$player_no_image = "http://portale.fitet.org/images/atleti/m-vuoto.png";
		$player_img = "<img class='fm-sc-athlete-img' alt='$player_name' src='$player_image' onError='this.onerror=null;this.src=\"$player_no_image\";'/>";
		if ($player_url) {
			return "<a href='$player_url'>$player_img</a>";
		} else {
			return "<div>$player_img</div>";
		}
	}

	public function retrieve_club($club_code) {
		if (empty($club_code)) {
			$clubs = $this->manager->get_clubs();
			if (!isset($clubs[0]))
				throw new Exception("No club found");
			$club = $clubs[0];
		} else {
			$club = $this->manager->get_club($club_code);
		}
		return $club;
	}

	protected function process_data($data) {

		$attributes = $data['attributes'];
		$content = $data['content'];

		$attributes = shortcode_atts(
			['club-code' => '', 'detail-page' => 'atleta']
			, $attributes, $this->tag);

		$club_code = $attributes['club-code'];
		$detail_page = $attributes['detail-page'];

		$club = $this->retrieve_club($club_code);


		$get_pages = get_pages();
		$get_pages = array_filter($get_pages, function ($page) use ($detail_page) {
			return $page->post_name == $detail_page;
		});

		if (empty($get_pages)) {
			$guid = null;
		} else {
			$guid = $get_pages[0]->guid;
		}

		$content = $this->to_content($club['players'], $guid);

		return ['content' => $content,];
	}

	private function to_content($players, $guid) {
		$content = "";
		foreach ($players as $player) {
			$content .= "<div class='fm-sc-athlete'>";

			$player_name = $player['name'];
			$player_url = $guid != null ? ($guid . '&atleta=' . $player['code'] . "-" . str_replace(' ', '-', $player_name)) : null;
			$player_img = $this->to_player_img($player, $player_url);

			$content .= $player_img;

			$content .= "<div>";
			$content .= ($player_url != null ? "<a href='$player_url'><b>$player_name</b></a>" : "<b>$player_name</b>");
			$content .= "<div><b>" . __('Rank') . "</b>: <span>" . $player['rank'] . "</span></div>";
			$content .= "<div><b>" . __('Points') . "</b>: <span>" . $player['points'] . "</span></div>";
			$content .= "<div><b>" . __('Category') . "</b>: <span>" . $player['category'] . "</span></div>";
			$content .= "<div><b>" . __('Sector') . "</b>: <span>" . $player['sector'] . "</span></div>";

			$content .= "</div>";
			$content .= "</div><hr>";
		}


		return $content;
	}


}
