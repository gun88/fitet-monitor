<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'public/components/table/class-fitet-monitor-table-component.php';


class Fitet_Monitor_Titles_Component extends Fitet_Monitor_Component {

	private $multi_club;

	public function __construct($plugin_name, $version, $multi_club = false) {
		parent::__construct($plugin_name, $version);
		$this->multi_club = $multi_club;
	}

	protected function components() {
		return ['table' => new Fitet_Monitor_Table_Component($this->plugin_name, $this->version)];
	}


	protected function process_data($data) {

		$sections = [];

		if (!empty($data['nationalTitles'])) {
			$header = "<h1>" . __('National Titles') . "</h1>";
			$table = $this->table($data['nationalTitles'], 'nationalTitles');
			$sections[] = $header . $this->components['table']->render($table);
		}

		if (!empty($data['regionalTitles'])) {
			$header = "<h1>" . __('Regional Titles') . "</h1>";
			$table = $this->table($data['regionalTitles'], 'regionalTitles');
			$sections[] = $header . $this->components['table']->render($table);
		}

		if (empty($sections)) {
			return "<div class='fm-titles'>" . __('No results found') . "</div>";
		}

		$sections = implode('<hr>', $sections);

		return "<div class='fm-titles'>$sections</div>";
	}

	private function table($titles, $name) {
		return [
			'name' => "titles-$name",
			'columns' => $this->columns(),
			'rows' => $this->rows($titles),
		];
	}

	private function columns() {

		$columns = [];
		$columns['season'] = __('Season');
		$columns['tournament'] = __('Tournament');
		$columns['competition'] = __('Competition');
		$columns['player'] = __('Player');
		if ($this->multi_club) {
			$columns['club'] = __('Club');
		}
		return $columns;


	}

	private function rows($titles) {
		return array_map(function ($title) {
			$title['player'] = Fitet_Monitor_Utils::player_cell_by_name_and_club($title['player'], $title['clubCode'], "index.php?page_id=56");
			if ($this->multi_club)
				$title['club'] = Fitet_Monitor_Utils::club_cell($title['clubCode'], $title['clubName']);
			return $title;
		}, $titles);
	}


}
