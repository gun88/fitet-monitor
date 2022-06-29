<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';


class Fitet_Monitor_Athlete_Season_Component extends Fitet_Monitor_Component {
	protected function script_dependencies(): array {
		return ['jquery', 'jquery.dynatable.js'];
	}


	public function enqueue_scripts() {
		$file = FITET_MONITOR_DIR . "public/assets/jquery.dynatable.js";
		$file = plugin_dir_path($file) . basename($file);
		Fitet_Monitor_Helper::enqueue_script("jquery.dynatable.js", $file, ['jquery'], $this->version, false);
		parent::enqueue_scripts();
	}

	public function enqueue_styles() {
		parent::enqueue_styles();
		$file = FITET_MONITOR_DIR . "public/assets/jquery.dynatable.css";
		$file = plugin_dir_path($file) . basename($file);
		Fitet_Monitor_Helper::enqueue_style("jquery.dynatable.css", $file, [], $this->version, 'all');

	}

	protected function process_data($data) {

		$table = "


<table id='my-table' class='table table-striped'>
<thead>
	<tr>
		<th>" . __("Opponent") . "</th>
		<th>" . __("Date") . "</th>
		<th>" . __("Match") . "</th>
		<th>" . __("Outcome") . "</th>
		<th>" . __("Points") . "</th>
	</tr>
	</thead>
	<tbody>
	";


		for ($i = 0; $i < 10; $i++) {
			foreach ($data as $item) {
				$table .= "
	<tr>
		<td>" . $item['opponent'] . "</td>
		<td>" . $item['date'] . "</td>
		<td>" . $item['match'] . "</td>
		<td>" . ($item['win'] ? "V" : "P") . "</td>
		<td>" . $item['points'] . "</td>
	</tr>";
			}
		}

		$table .= "</tbody></table>";
		return ['table' => $table];
	}


}
