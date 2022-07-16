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
		'showLink' => false,
		'showStatistics' => false,

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

		$players = array_values($players); // todo remove this line

		return "<h3>" . __("Players", 'fitet-monitor') . "</h3>" .
			"<div class='fm-team-card-players-content'>" . implode('', $players) . "</div>";
	}

	private function info($team) {
		$content = $this->row(__('Championship', 'fitet-monitor'), $team['championshipName']);
		$content .= $this->row(__('Season', 'fitet-monitor'), $team['seasonName']);
		$content .= $this->ranking($team);

		if (isset($team['teamPageUrl']) && $team['showLink']) {
			$content .= "<div><a class='fm-team-card-details-link' href='" . $team['teamPageUrl'] . "'>" . __('Open Team Details', 'fitet-monitor') . "</a></div>";
		}

		$content .= "<br>";
		return $content;
	}

	private function ranking($team) {
		$ranking = $team['ranking'];
		switch ($team['teamStatus']) {
			case 'playoff':
				$ranking .= " (" . __('Playoff', 'fitet-monitor') . ")";
				break;
			case 'playout':
				$ranking .= " (" . __('Playout', 'fitet-monitor') . ")";
				break;
			case 'relegation':
				$ranking .= " (" . __('Relegation', 'fitet-monitor') . ")";
				break;
			case 'promo':
				$ranking .= " (" . __('Promotion', 'fitet-monitor') . ")";
				break;
			case 'neutral':
			default:
				break;
		}

		return $this->row(__('Ranking', 'fitet-monitor'), $ranking);
	}


	private function row($label, $value) {
		return "<div><b>$label</b>: <span>$value</span></div>";
	}

}
