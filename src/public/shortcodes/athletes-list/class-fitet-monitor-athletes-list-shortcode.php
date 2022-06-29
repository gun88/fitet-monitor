<?php

require_once FITET_MONITOR_DIR . 'public/includes/class-fitet-monitor-shortcode.php';
require_once FITET_MONITOR_DIR . 'public/components/athlete-card/class-fitet-monitor-athlete-card-component.php';


class Fitet_Monitor_Athletes_List_Shortcode extends Fitet_Monitor_Shortcode {


	private $manager;

	public function __construct($version, $plugin_name, $manager) {
		parent::__construct($version, $plugin_name, 'fm-athletes-list');
		$this->manager = $manager;
	}

	protected function components() {
		return [
			'athleteCard' => new Fitet_Monitor_Athlete_Card_Component($this->plugin_name, $this->version, []),
		];
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
			$player['link'] = $guid != null ? ($guid . '&atleta=' . $player['code'] . "-" . str_replace(' ', '-', $player['name'])) : null;
			$content .= $this->components['athleteCard']->render($player);
			$content .= "<hr>";
		}


		return $content;
	}


}
