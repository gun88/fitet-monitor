<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';

class Fitet_Monitor_Team_Card_Component extends Fitet_Monitor_Component {
	private $player_card_config = [
		'showClub' => false,
		'showClubCode' => false,
		'showPoints' => false,
		'showRank' => false,
		'showBest' => false,
		'showCategory' => false,
		'showDiff' => false,
		'showPlayerCode' => false,
		'showSector' => false,
		'showRegion' => false,
		'showSex' => false,
		'showBirthDate' => false,
	];

	private $default_config = [
		'addAnchor' => false,
	];
	private $config = [];

	public function __construct($plugin_name, $version, $config) {
		parent::__construct($plugin_name, $version);
		$this->config = $config;
	}


	protected function components() {
		return [
			'playerCard' => new Fitet_Monitor_Player_Card_Component($this->plugin_name, $this->version, $this->player_card_config),
		];
	}


	protected function process_data($data) {
		$data = array_merge($this->default_config, $this->config, $data);
		return [
			'teamCardHeader' => $this->header($data),
			'teamCardInfo' => $this->info($data),
			'teamCardPlayers' => $this->players($data),
		];
	}

	private function header($team) {
		$team_name = $team['teamName'];
		if (isset($team['teamPageUrl']))
			$team_name = "<a href='" . $team['teamPageUrl'] . "'>" . $team['teamName'] . "</a>";
		return "<h2>$team_name</h2>";
	}

	private function players($team) {
		$players = isset($team['players']) ? $team['players'] : [];

		$players = array_map(function ($player) use ($team) {
			return $this->components['playerCard']->render($player);
		}, $players);

		$players = array_values($players);

		$anchor = $team['addAnchor'] ? "<a id='players'></a>" : "";
		return $anchor . "<h3>" . __("Players") . "</h3>" .
			"<div class='fm-team-card-players-content'>" . implode('', $players) . "</div>";
	}

	private function info($team) {
		$content = $this->row(__('Championship'), $team['championshipName']);
		$content .= $this->row(__('Season'), $team['seasonName']);
		$content .= $this->ranking($team);

		if (isset($team['teamPageUrl'])) {
			$content .= "<div><a class='fm-team-card-details-link' href='" . $team['teamPageUrl'] . "'>" . __('Open Team Details') . "</a></div>";
		}

		$content .= "<br>";
		return $content;
	}

	private function ranking($team) {
		$ranking = $team['ranking'];
		switch ($team['teamStatus']) {
			case 'playoff':
				$ranking .= " (" . __('Playoff') . ")";
				break;
			case 'playout':
				$ranking .= " (" . __('Playout') . ")";
				break;
			case 'relegation':
				$ranking .= " (" . __('Relegation') . ")";
				break;
			case 'promo':
				$ranking .= " (" . __('Promotion') . ")";
				break;
			case 'neutral':
			default:
				break;
		}

		return $this->row(__('Ranking'), $ranking);
	}


	private function row($label, $value) {
		return "<div><b>$label</b>: <span>$value</span></div>";
	}

}
