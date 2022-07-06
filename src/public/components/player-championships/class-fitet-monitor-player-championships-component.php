<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'public/components/table/class-fitet-monitor-table-component.php';


class Fitet_Monitor_Player_Championships_Component extends Fitet_Monitor_Component {

	protected function components() {
		return ['table' => new Fitet_Monitor_Table_Component($this->plugin_name, $this->version),];
	}

	protected function process_data($data) {
		$table = [
			'name' => "championships",
			'columns' => $this->columns(),
			'sort' => $this->sort(),
			'rows' => $this->rows($data['history']['championships']),

		];

		return [
			'championshipsLabel' => __('Championships'),
			'table' => $this->components['table']->render($table),
		];
	}

	private function type($type): string {
		if ($type == 'naz') $type = __('National');
		if ($type == 'reg') $type = __('Regional');
		return $type;
	}

	private function team($team_name, $season_id, $championship_id) {
		return Fitet_Monitor_Utils::team_cell_by_name($team_name, $season_id, $championship_id, "index.php?page_id=80");
	}

	private function championship($championship_name, string $type) {
		return "<b>$championship_name</b><br><span>$type</span>";
	}

	private function rows($rows) {
		return array_map(function ($row) {
			if (is_numeric($row['matchPercentage']))
				$row['matchPercentage'] = round($row['matchPercentage']) . '%';
			$row['type'] = $this->type($row['type']);
			$row['championship'] = $this->championship($row['championshipName'], $row['type']);
			$row['team'] = $this->team($row['teamName'], $row['seasonId'], $row['championshipId']);
			return $row;
		}, $rows);
	}


	private function columns() {
		return [
			'season' => __("Season"),
			'championship' => __("Championship"),
			'team' => __("Team"),
			'matchCount' => __('Match'),
			'matchWin' => __('Won'),
			'matchLost' => __('Lost'),
			'matchPercentage' => __('Percentage'),
		];
	}


	private function sort() {
		return [
			'matchCount' => 'number',
			'matchWin' => 'number',
			'matchLost' => 'number',
			'matchPercentage' => 'number',
		];
	}


}
