<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'public/components/table/class-fitet-monitor-table-component.php';


class Fitet_Monitor_Player_National_Tournament_Component extends Fitet_Monitor_Component {

	protected function components() {
		return [
			'table' => new Fitet_Monitor_Table_Component($this->plugin_name, $this->version),
		];
	}

	protected function process_data($data) {
		$columns = $this->columns();
		$sort = $this->sort();
		$rows = $this->rows($data);

		$table = [
			'name' => 'national-tournaments',
			'columns' => $columns,
			'sort' => $sort,
			'rows' => $rows,
		];

		return [
			'nationalTournamentsLabel' => __('National Tournaments', 'fitet-monitor'),
			'table' => $this->components['table']->render($table),
		];
	}

	public function columns() {
		return [
			'season' => __("Season", 'fitet-monitor'),
			'date' => __("Date", 'fitet-monitor'),
			'competition' => __("Competition", 'fitet-monitor'),
			'round' => __("Round", 'fitet-monitor')
		];
	}

	public function sort() {
		return [
			'date' => 'date',
			'round' => 'number',
		];
	}


	public function rows($data) {
		return array_map(function ($row) use ($data) {
			$row['competition'] = $this->competition($row['competition'], $row['tournament']);
			return $row;
		}, $data['history']['nationalTournaments']);
	}

	private function competition($competition, $tournament) {
		return "<b>$tournament</b><br><span>$competition</span>";
	}


}
