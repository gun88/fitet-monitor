<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'public/components/table/class-fitet-monitor-table-component.php';
require_once FITET_MONITOR_DIR . 'public/components/cells/class-fitet-monitor-player-cell-component.php';


class Fitet_Monitor_Player_Season_Component extends Fitet_Monitor_Component {

	protected function components() {
		return [
			'table' => new Fitet_Monitor_Table_Component($this->plugin_name, $this->version),
			'playerCell' => new Fitet_Monitor_Player_Cell_Component($this->plugin_name, $this->version),
		];
	}


	protected function process_data($data) {
		$table = [
			'name' => 'player-season',
			'columns' => $this->columns(),
			'sort' => $this->sort(),
			'rows' => $this->rows($data),
		];

		return [
			'seasonLabel' => __('Season', 'fitet-monitor'),
			'table' => $this->components['table']->render($table),

		];
	}

	public function columns() {
		return [
			'date' => __("Date", 'fitet-monitor'),
			'opponent' => __("Opponent", 'fitet-monitor'),
			'points' => __("Points", 'fitet-monitor'),
			'match' => __("Match", 'fitet-monitor'),
		];
	}

	private function sort() {
		return [
			'date' => 'date',
			'points' => 'number',
		];
	}


	private function rows($data) {
		return array_map(function ($row) use ($data) {
			$row['points'] = $this->points($row['points'], $row['win']);
			$row['opponent'] = $this->components['playerCell']->render(['playerId' => $row['opponentPlayerId'], 'playerName' => $row['opponentPlayerName'], 'playerPageUrl' => $row['opponentPlayerPageUrl']]);
			return $row;
		}, $data['season']);
	}

	private function points($points, $win) {
		if ($win) {
			return round($points) . " <span class='fm-points-gain'>&#9650;</span>";
		} else {
			return round($points) . " <span class='fm-points-lost'>&#9660;</span>";

		}
	}


}
