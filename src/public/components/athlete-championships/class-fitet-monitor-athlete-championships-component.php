<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'public/components/table/class-fitet-monitor-table-component.php';


class Fitet_Monitor_Athlete_Championships_Component extends Fitet_Monitor_Component {

	protected function components() {
		return [
			'table' => new Fitet_Monitor_Table_Component($this->plugin_name, $this->version),
		];
	}


	protected function process_data($data) {

		$name = "championships-".$data['code'];
		$rows = $data['history']['championships'];

		/*$rows = array_map(function ($row) {
			$row['outcome'] = $row['win'] ? __('W') : __('L');
			return $row;
		}, $rows);*/

		$table = [
			'name' => $name,
			'columns' => [
				'season' => __("Season"),
				'championshipName' => __("Championship Name"),
				'championshipId' => __("Championship Id"),
				'seasonId' => __("Season Id"),
				'type' => __("Type"),
				'teamName' => __("Team Name"),
				'playerPosition' => __("Player Position"),
				'matchCount' => __("Match Count"),
				'matchWin' => __("Match Win"),
				'matchLost' => __("Match Lost"),
				'matchPercentage' => __("Match Percentage"),
			],
			'rows' => $rows,

		];

		return [
			'championshipsLabel' => __('Championships'),
			'table' => $this->components['table']->render($table),

		];
	}


}
