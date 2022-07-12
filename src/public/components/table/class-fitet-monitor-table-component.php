<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';


class Fitet_Monitor_Table_Component extends Fitet_Monitor_Component {
	protected function script_dependencies(): array {
		return ['wp-i18n', 'jquery'];
	}


	protected function process_data($data) {

		$name = $data['name'];
		$columns = $data['columns'];
		$paginate = isset($data['paginate']) && ($data['paginate'] == false) ? 'data-paginate="false"' : '';
		$search = isset($data['search']) && ($data['search'] == false) ? 'data-search="false"' : '';
		$sort = isset($data['sort']) ? $data['sort'] : [];
		$keys = array_keys($columns);
		$rows = $data['rows'];

		$table = "<table id='$name' class='table table-striped fm-table fm-table-$name' $paginate $search>";
		$table .= "<thead>";
		$table .= "<tr>";
		foreach ($keys as $key) {
			$sort_type = isset($sort[$key]) ? "data-sort-type='" . $sort[$key] . "'" : "";
			$table .= "<th class='fm-table-column-$key' data-dynatable-column='$key' $sort_type>" . $columns[$key] . "</th>";
		}
		$table .= "</tr>";
		$table .= "</thead>";
		$table .= "<tbody>";

		foreach ($rows as $item) {
			$table .= empty($item['_rowClass']) ? '<tr>' : "<tr class='" . $item['_rowClass'] . "'>";
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
