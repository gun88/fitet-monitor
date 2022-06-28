<?php

class Fitet_Monitor_Bootstrap_Table {

	private $config;

	public function __construct($config) {
		$this->config = $config;
	}

	public function render($items) {
		$columns = $this->config['columns'];
		$class = isset($this->config['class']) ? $this->config['class'] : 'table table-striped';
		$search = isset($this->config['search']) ? $this->config['search'] : 'true';
		$pagination = isset($this->config['pagination']) ? $this->config['pagination'] : 'true';

		$table = "<div class='fm-bootstrap'>";
		$table .= "<table data-toggle='fm-table' data-pagination='$pagination' data-search='$search' class='$class'>";
		$table .= "<thead>";
		$table .= "<tr>";
		foreach ($columns as $key => $value) {
			$table .= "<th data-field='$key' data-sortable='true' >$value</th>";
		}
		$table .= "</tr>";
		$table .= "</thead>";

		$table .= "<tbody>";
		foreach ($items as $item) {
			$table .= "<tr>";
			foreach (array_keys($columns) as $key) {
				$table .= "<td class='column-$key'>$item[$key]</td>";
			}
			$table .= "</tr>";
		}
		$table .= "</tbody>";
		$table .= '</table>';
		$table .= "</div>";
		return $table;
	}


}
