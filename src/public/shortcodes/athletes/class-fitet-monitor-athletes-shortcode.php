<?php

require_once FITET_MONITOR_DIR . 'public/includes/class-fitet-monitor-shortcode.php';

class Fitet_Monitor_Athletes_Shortcode extends Fitet_Monitor_Shortcode {

	/**
	 * @var Fitet_Monitor_Manager
	 */
	private $manager;

	public function __construct($version, $plugin_name, $manager) {
		parent::__construct($version, $plugin_name, 'fm-monitor-athletes');
		$this->manager = $manager;
	}

	protected function process_data($data) {

		$attributes = $data['attributes'];
		$content = $data['content'];

		$x = shortcode_atts(
			[
				'club-code' => '',
				'pagination' => true
			]
			, $attributes, $this->tag);

		$club = $this->manager->get_club($x['club-code']);
		$table = $this->to_table($club['players']);


		return [
			'table' => $table
		];// ['style' => $style, 'message' => $message,];
	}

	private function to_table($items) {

		$columns = [
			'name' => 'name',
			'rank' => 'rank',
			'points' => 'points',
			'category' => 'category',
			'sector' => 'sector',
			'diff' => 'diff',
			'birthDate' => 'birthDate',
			'region' => 'region',
			'sex' => 'sex',
			'code' => 'code',
		];


		$s = "";

		$s .= '<link rel="stylesheet" href="https://unpkg.com/bootstrap-table@1.20.2/dist/bootstrap-table.min.css">
<script src="https://unpkg.com/bootstrap-table@1.20.2/dist/bootstrap-table.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

<table data-toggle="table"   data-pagination="true"
  data-search="true" class="table table-striped">
  <thead>
    <tr>';

		$keys = array_keys($columns);

		foreach ($keys as $key) {
			$str = $columns[$key];
			$s .= "<th>$str</th>";
		}
		$s .= '</tr>
  </thead>
  <tbody>';
		foreach ($items as $item) {
			$s .= "<tr>";
			foreach ($keys as $key) {
				$s .= "<td>$item[$key]</td>";
			}
			$s .= "</tr>";
		}
		$s .= '

  </tbody>
</table>


';


		return $s;

	}


}
