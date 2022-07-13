<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'public/components/table/class-fitet-monitor-table-component.php';
require_once FITET_MONITOR_DIR . 'public/components/cells/class-fitet-monitor-player-cell-component.php';

class Fitet_Monitor_Team_Statistics_Component extends Fitet_Monitor_Component {
	protected function components() {
		return [
			'table' => new Fitet_Monitor_Table_Component($this->plugin_name, $this->version),
			'playerCell' => new Fitet_Monitor_Player_Cell_Component($this->plugin_name, $this->version),
		];
	}


	protected function process_data($data) {
		return [
			'teamStatisticsLabel' => __("Statistics", 'fitet-monitor'),
			'table' => $this->table($data),
		];
	}

	private function table($data) {
		return $this->components['table']->render(
			[
				'name' => 'fm-team-statistics',
				'paginate' => false,
				'search' => false,
				'columns' => $this->columns(),
				'sort' => $this->sort(),
				'rows' => $this->rows($data),
			]
		);
	}

	private function columns() {

		return [
			//"playerId" => __('playerId', 'fitet-monitor'),
			"playerName" => __('Player', 'fitet-monitor'),
			"pd" => __('PD', 'fitet-monitor'),
			"pav" => __('PAV', 'fitet-monitor'),
			"pap" => __('PAP', 'fitet-monitor'),
			"sv" => __('SV', 'fitet-monitor'),
			"sp" => __('SP', 'fitet-monitor'),
			"pv" => __('PV', 'fitet-monitor'),
			"pp" => __('PP', 'fitet-monitor'),
			"percentage" => __('Percentage', 'fitet-monitor'),
			//"multiClub" => __('multiClub', 'fitet-monitor'),
			//"playerImageUrl" => __('playerImageUrl', 'fitet-monitor'),
			//"playerCode" => __('playerCode', 'fitet-monitor'),
			//"clubCode" => __('clubCode', 'fitet-monitor'),
			//"playerUrl" => __('playerUrl', 'fitet-monitor'),
		];
	}

	private function sort() {
		return [
			'pd' => 'number',
			"pav" => 'number',
			"pap" => 'number',
			"sv" => 'number',
			"sp" => 'number',
			"pv" => 'number',
			"pp" => 'number',
			"percentage" => 'number',
		];
	}

	private function rows($data) {
		return array_map(function ($player) {
			$player['playerName'] = $this->components['playerCell']->render([
				'playerId' => $player['playerId'],
				'playerName' => $player['playerName'],
				'playerPageUrl' => $player['playerUrl'],
				'playerImage' => $player['playerImageUrl']]);
			if (is_numeric($player['percentage']))
				$player['percentage'] = round($player['percentage']) . '%';
			return $player;
		}, $data['players']);
	}


}
