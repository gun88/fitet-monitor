<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'public/components/table/class-fitet-monitor-table-component.php';


class Fitet_Monitor_Player_Season_Component extends Fitet_Monitor_Component {

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
			'name' => 'player-season',
			'columns' => $columns,
			'sort' => $sort,
			'rows' => $rows,
		];

		return [
			'seasonLabel' => __('Season'),
			'table' => $this->components['table']->render($table),

		];
	}

	public function columns() {
		return [
			'date' => __("Date"),
			'opponent' => __("Opponent"),
			'points' => __("Points"),
			'match' => __("Match"),
		];
	}

	public function sort() {
		return [
			'date' => 'date',
			'points' => 'number',
		];
	}


	public function rows($data) {
		return array_map(function ($row) use ($data) {
			$row['points'] = $this->points($row['points'], $row['win']);
			$row['opponent'] = Fitet_Monitor_Utils::player_cell_by_name_and_club($row['opponent'], $data['clubCode'], "index.php?page_id=56");
			return $row;
		}, $data['season']);
	}

	private function points($points, $win) {
		return "<span class='" . ($win ? "fm-points-gain" : "fm-points-lost") . "'>" . round($points) . "</span>";
	}


}
