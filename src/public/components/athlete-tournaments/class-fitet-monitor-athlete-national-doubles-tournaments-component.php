<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'public/components/table/class-fitet-monitor-table-component.php';


class Fitet_Monitor_Athlete_National_Doubles_Tournament_Component extends Fitet_Monitor_Component {

	protected function components() {
		return [
			'table' => new Fitet_Monitor_Table_Component($this->plugin_name, $this->version),
		];
	}


	protected function process_data($data) {

		$name = "national-double-tournaments-" . $data['code'];
		$rows = $data['history']['nationalDoublesTournaments'];

		/*$rows = array_map(function ($row) {
			$row['outcome'] = $row['win'] ? __('W') : __('L');
			return $row;
		}, $rows);*/


		$table = [
			'name' => $name,
			'columns' => [
				'season' => __("Season"),
				'date' => __("Date"),
				'team' => __("Team"),
				'tournament' => __("Tournament"),
				'competition' => __("Competition"),
				'round' => __("Round"),
				'marker' => __("Marker"),
			],
			'rows' => $rows,

		];

		return [
			'nationalDoubleTournamentsLabel' => __('National Doubles Tournaments'),
			'table' => $this->components['table']->render($table),

		];
	}


}
