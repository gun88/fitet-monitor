<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';


class Fitet_Monitor_Table_Component extends Fitet_Monitor_Component {

	protected function process_data($data) {

		$name = $data['name'];
		$columns = $data['columns'];
		$keys = array_keys($columns);
		$rows = $data['rows'];

		$table = "";
		$table .= "<table id='$name' class='table table-striped fm-table fm-table-$name'>";
		$table .= "<thead>";
		$table .= "<tr>";
		foreach ($keys as $key) {
			$table .= "<th class='fm-table-column-$key'>" . $columns[$key] . "</th>";
		}
		$table .= "</tr>";
		$table .= "</thead>";
		$table .= "<tbody>";

		foreach ($rows as $item) {
			$table .= "<tr>";
			foreach ($keys as $key) {
				$table .= "<td class='fm-table-column-$key'>" . $item[$key] . "</td>";
			}
			$table .= "</tr> ";
		}

		$table .= "</tbody>";
		$table .= "</table>";
		return $table;
	}


}
