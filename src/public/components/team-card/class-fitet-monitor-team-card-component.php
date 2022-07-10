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

	protected function components() {
		return [
			'playerCard' => new Fitet_Monitor_Player_Card_Component($this->plugin_name, $this->version, $this->player_card_config),
		];
	}


	protected function process_data($data) {
		return [
			'teamCardHeader' => $this->header($data),
			'teamCardInfo' => $this->info($data),
			'teamCardPlayers' => $this->players($data),
		];
	}

	private function header($team) {
		$team_name = $team['teamName'];
		if (isset($team['teamUrl']))
			$team_name = "<a href='" . $team['teamUrl'] . "'>" . $team['teamName'] . "</a>";
		return "<h2>$team_name</h2>";
	}

	private function players($team) {
		$players = isset($team['players']) ? $team['players'] : [];

		$players = array_map(function ($player) use ($team) {
			/*$_player = Fitet_Monitor_Utils::player_by_id($player['playerId']);
			if ($_player != null) {
				$player = $_player;
			//	$player['playerUrl'] = $team['playerUrl'];
			} else {
				$player['name'] = $player['playerName'];
			}*/

			//if ($player == null) return "";
			return $this->components['playerCard']->render($player);
		}, $players);

		$players = array_values($players);

		return "<h3>" . __("Players") . "</h3>" .
			"<div class='fm-team-card-players'>" . implode('', $players) . "</div>";
	}

	private function info($team) {
		$content = $this->row(__('Championship'), $team['championshipName']);
		$content .= $this->row(__('Season'), $team['seasonName']);
		$content .= $this->ranking($team);

		if (isset($team['teamUrl'])) {
			$content .= "<div><a class='fm-team-card-details-link' href='" . $team['teamUrl'] . "'>" . __('Open Team Details') . "</a></div>";
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
