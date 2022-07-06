<?php
require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'public/components/table/class-fitet-monitor-table-component.php';
require_once FITET_MONITOR_DIR . 'public/utils/class-fitet-monitor-utils.php';

class Fitet_Monitor_Caps_Component extends Fitet_Monitor_Component {

	private $multi_club;

	public function __construct($plugin_name, $version, $multi_club = false) {
		parent::__construct($plugin_name, $version);
		$this->multi_club = $multi_club;
	}

	protected function components() {
		return ['table' => new Fitet_Monitor_Table_Component($this->plugin_name, $this->version)];
	}


	protected function process_data($data) {
		$caps = $data;

		/*if (empty($caps))
			return "no caps";
		// todo prova a calcolare
		*/

		$table = $this->table($caps);

		$table = [
			'name' => 'caps',
			'columns' => $this->columns(),
			'rows' => $this->rows($caps),
		];


		return [
			'table' => $this->components['table']->render($table),

		];
	}

	private function table($caps) {
		$content = "<table><thead><tr>";
		foreach ($caps[0] as $k => $v) {
			if (!$this->multi_club && ($k == 'clubCode' || $k == 'clubName'))
				continue;
			$content .= "<th>$k</th>";
		}
		$content .= "</tr></thead>";
		foreach ($caps as $cap) {
			$row = $this->row($cap);
			$content .= "<tr>$row</tr>";
		}
		$content .= "<tbody></tbody></table>";

		return $content;
	}


	public function row($cap) {

		$str = "";
		foreach ($cap as $k => $v) {
			if (!$this->multi_club && ($k == 'clubCode' || $k == 'clubName'))
				continue;

			$str .= "<td>$v</td>";
		}
		return $str;
	}

	private function columns() {

		$columns = [];
		$columns['count'] = __('Caps');
		$columns['player'] = __('Player');
		if ($this->multi_club) {
			$columns['club'] = __('Club');
		}
		return $columns;


	}

	private function rows($caps) {
		return array_map(function ($cap) {
			$cap['player'] = $this->player($cap);
			if ($this->multi_club)
				$cap['club'] = $this->club($cap);
			return $cap;
		}, $caps);
	}

	function player($cap): string {

		return Fitet_Monitor_Utils::player_cell_by_code($cap['playerCode'], "index.php?page_id=56", $cap['playerName']);
	}

	function club($cap): string {
		return Fitet_Monitor_Utils::club_cell($cap['clubCode'], $cap['clubName']);
	}


}
