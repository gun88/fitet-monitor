<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'public/components/common/class-fitet-monitor-player-image-component.php';


class Fitet_Monitor_Player_Card_Component extends Fitet_Monitor_Component {

	private $deault_confing = [
		'showClub' => true,
		'showClubCode' => true,
		'showPoints' => true,
		'showRank' => true,
		'showBest' => true,
		'showCategory' => true,
		'showDiff' => true,
		'showPlayerCode' => true,
		'showSector' => true,
		'showRegion' => true,
		'showSex' => true,
		'showBirthDate' => true,
		'playerImage' => FITET_MONITOR_PLAYER_NO_IMAGE,
		'playerUrl' => null,
		'playerName' => 'N/A',
		'playerCode' => 'N/A',
		'clubName' => 'N/A',
		'clubCode' => 'N/A',
		'points' => 'N/A',
		'rank' => 'N/A',
		'best' => 'N/A',
		'category' => 'N/A',
		'diff' => 'N/A',
		'sector' => 'N/A',
		'type' => 'N/A',
		'region' => 'N/A',
		'sex' => 'N/A',
		'birthDate' => 'N/A',
	];

	private $config;

	public function __construct($plugin_name, $version, $config) {
		parent::__construct($plugin_name, $version);
		$this->config = $config;
	}

	protected function components() {
		return ['image' => new Fitet_Monitor_Player_Image_Component($this->plugin_name, $this->version)];
	}

	protected function process_data($data) {
		$data = array_merge($this->deault_confing, $this->config, $data);
		$data['playerImage'] = $this->player_image($data);
		$data['playerContent'] = $this->player_content($data);

		return $data;
	}

	private function player_image($data) {
		return $this->components['image']->render([
			'playerId' => $data['playerId'],
			'playerName' => $data['playerName'],
			'playerPageUrl' => $data['playerUrl']
		]);
	}

	private function player_content($data) {
		$content = "<div>" . $this->name($data) . "</div>";
		if ($data['multiClub'])
			$content .= $data['showClub'] ? $this->club($data) : '';
		$content .= $data['showPoints'] ? $this->row(__('Points', 'fitet-monitor'), $data['points']) : '';
		$content .= $data['showRank'] ? $this->row(__('Rank', 'fitet-monitor'), $this->rank($data['rank'], $data['type'])) : '';
		$content .= $data['showBest'] ? $this->row(__('Best', 'fitet-monitor'), $this->best($data['best'])) : '';
		$content .= $data['showCategory'] ? $this->row(__('Category', 'fitet-monitor'), $data['category']) : '';
		$content .= $data['showDiff'] ? $this->row(__('Difference', 'fitet-monitor'), $data['diff']) : '';
		$content .= $data['showSector'] ? $this->row(__('Sector', 'fitet-monitor'), $data['sector']) : '';
		$content .= $data['showPlayerCode'] ? $this->row(__('Player Code', 'fitet-monitor'), $data['playerCode']) : '';
		$content .= $data['showRegion'] ? $this->row(__('Region', 'fitet-monitor'), $data['region']) : '';
		$content .= $data['showSex'] ? $this->row(__('Sex', 'fitet-monitor'), $data['sex']) : '';
		$content .= $data['showBirthDate'] ? $this->row(__('Birth Date', 'fitet-monitor'), $data['birthDate']) : '';
		return $content;
	}


	public function name($data) {
		$player_page_url = ($data['playerUrl']);
		if ($player_page_url != null) {
			return "<a href='$player_page_url'><b>" . $data['playerName'] . "</b></a>";
		} else {
			return "<span><b>" . $data['playerName'] . "</b></span>";
		}
	}

	private function club($data) {
		$club_name = $data['clubName'];
		if ($data['showClubCode']) {
			$club_name .= '<span> - ' . __('Code', 'fitet-monitor') . ': ' . $data['clubCode'] . '</span>';
		}
		return $this->row(__('Club', 'fitet-monitor'), $club_name);

	}


	private function row($label, $value) {
		return "<div><b>$label</b>: <span>$value</span></div>";
	}

	private function rank($rank, $type): string {
		return $type == 'Italiani' ? $rank : $type;
	}


	private function best($best) {
		return empty($best) ? 'N/A' : '';
	}

}
