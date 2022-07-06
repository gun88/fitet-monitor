<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'public/components/table/class-fitet-monitor-table-component.php';


class Fitet_Monitor_Player_National_Doubles_Tournament_Component extends Fitet_Monitor_Component {

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
			'name' => 'national-doubles-tournaments',
			'columns' => $columns,
			'sort' => $sort,
			'rows' => $rows,
		];

		return [
			'nationalDoubleTournamentsLabel' => __('National Double Tournaments'),
			'table' => $this->components['table']->render($table),
		];
	}

	public function columns() {
		return [
			'season' => __("Season"),
			'date' => __("Date"),
			'partner' => __("Doubles Partner"),
			'competition' => __("Competition"),
			'round' => __("Round")
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
			$row['round'] = $this->round($row['round'], $row['marker']);
			$row['partner'] = $this->partner($row['team'], $data['name'], $data['clubCode'], $data['baseUrl'], );
			$row['competition'] = $this->competition($row['competition'], $row['tournament']);
			return $row;
		}, $data['history']['nationalDoublesTournaments']);
	}

	private function round($round, $marker) {
		// todo metti in updater
		if (substr($round, 0, 2) == "1^") $marker = 'gold';
		if (substr($round, 0, 2) == "2^") $marker = 'silver';
		if (substr($round, 0, 2) == "3^") $marker = 'bronze';
		return "<span class='fm-points-$marker'>" . $round . "</span>";
	}

	private function partner($team, $player_name, $club_code,$base_url) {
		$players = explode("/", $team);
		$players = array_map(function ($player) {
			return trim($player);
		}, $players);
		$players = array_values(array_filter($players, function ($player) use ($player_name) {
			return $player != $player_name;
		}));
		return Fitet_Monitor_Utils::player_cell_by_name_and_club($players[0], $club_code, $base_url);

	}

	private function competition($competition, $tournament) {
		return "<b>$tournament</b><br><span>$competition</span>";
	}


}
