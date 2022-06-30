<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'public/components/table/class-fitet-monitor-table-component.php';


class Fitet_Monitor_Athlete_Season_Component extends Fitet_Monitor_Component {

	protected function components() {
		return [
			'table' => new Fitet_Monitor_Table_Component($this->plugin_name, $this->version),
		];
	}


	protected function process_data($data) {

		$name = $data['code'];
		$rows = $data['season'];

		$rows = array_map(function ($row) {
			$row['outcome'] = $row['win'] ? __('W') : __('L');
			return $row;
		}, $rows);

		$table = [
			'name' => $name,
			'columns' => [
				'opponent' => __("Opponent"),
				'date' => __("Date"),
				'match' => __("Match"),
				'outcome' => __("Outcome"),
				'points' => __("Points"),
			],
			'rows' => $rows,

		];

		return [
			'seasonLabel' => __('Season'),
			'table' => $this->components['table']->render($table),

		];
	}


}
