<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'public/components/table/class-fitet-monitor-table-component.php';


class Fitet_Monitor_Player_Regional_Tournament_Component extends Fitet_Monitor_Component {

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
			'name' => 'regional-tournaments',
			'columns' => $columns,
			'sort' => $sort,
			'rows' => $rows,
		];

		return [
			'regionalTournamentsLabel' => __('Regional Tournaments'),
			'table' => $this->components['table']->render($table),
		];
	}

	public function columns() {
		return [
			'season' => __("Season"),
			'date' => __("Date"),
			'competition' => __("Competition"),
			'round' => __("Round")
		];
	}

	public function sort() {
		return [
			'date' => 'date',
			'round' => 'numberOnly',
		];
	}


	public function rows($data) {
		return array_map(function ($row) use ($data) {
			$row['round'] = $this->round($row['round'], $row['marker']);
			$row['competition'] = $this->competition($row['competition'], $row['tournament']);
			return $row;
		}, $data['history']['regionalTournaments']);
	}

	private function round($round, $marker) {
		// todo metti in updater

		if ($round == 'finale') $round = '2^ posizione';
		if ($round == 'semifinale') $round = '3^ posizione';
		if ($round == 'quarti-finale') $round = '5^ posizione';
		if ($round == 'ottavi-finale') $round = '9^ posizione';

		if (substr($round, 0, 2) == "1^") $marker = 'gold';
		if (substr($round, 0, 2) == "2^") $marker = 'silver';
		if (substr($round, 0, 2) == "3^") $marker = 'bronze';
		return "<span class='fm-points-$marker'>" . $round . "</span>";
	}

	private function competition($competition, $tournament) {
		return "<b>$tournament</b><br><span>$competition</span>";
	}


}
