<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'public/components/table/class-fitet-monitor-table-component.php';
require_once FITET_MONITOR_DIR . 'public/components/cells/class-fitet-monitor-player-cell-component.php';
require_once FITET_MONITOR_DIR . 'public/components/cells/class-fitet-monitor-club-cell-component.php';


class Fitet_Monitor_Titles_Component extends Fitet_Monitor_Component {

	private $multi_club;

	public function __construct($plugin_name, $version, $multi_club = false) {
		parent::__construct($plugin_name, $version);
		$this->multi_club = $multi_club;
	}

	protected function components() {
		return [
			'table' => new Fitet_Monitor_Table_Component($this->plugin_name, $this->version),
			'playerCell' => new Fitet_Monitor_Player_Cell_Component($this->plugin_name, $this->version),
			'clubCell' => new Fitet_Monitor_Club_Cell_Component($this->plugin_name, $this->version),
		];
	}


	protected function process_data($data) {

		$sections = [];

		$player_page_url = $data['playerPageUrl'];

		if (!empty($data['nationalTitles'])) {
			$header = "<h1>" . __('National Titles', 'fitet-monitor') . "</h1>";
			$table = $this->table($data['nationalTitles'], 'nationalTitles', $player_page_url);
			$sections[] = $header . $this->components['table']->render($table);
		}

		if (!empty($data['regionalTitles'])) {
			$header = "<h1>" . __('Regional Titles', 'fitet-monitor') . "</h1>";
			$table = $this->table($data['regionalTitles'], 'regionalTitles', $player_page_url);
			$sections[] = $header . $this->components['table']->render($table);
		}

		if (empty($sections)) {
			return "<div class='fm-titles'>" . __('No results found', 'fitet-monitor') . "</div>";
		}

		$sections = implode('<hr>', $sections);

		return "<div class='fm-titles'>$sections</div>";
	}

	private function table($titles, $name, $player_page_url) {
		return [
			'name' => "titles-$name",
			'columns' => $this->columns(),
			'rows' => $this->rows($titles, $player_page_url),
		];
	}

	private function columns() {

		$columns = [];
		$columns['season'] = __('Season', 'fitet-monitor');
		$columns['player'] = __('Player', 'fitet-monitor');
		$columns['tournament'] = __('Tournament', 'fitet-monitor');
		$columns['competition'] = __('Competition', 'fitet-monitor');
		if ($this->multi_club) {
			$columns['club'] = __('Club', 'fitet-monitor');
		}
		return $columns;


	}

	private function rows($titles, $player_page_url) {
		return array_map(function ($title) use ($player_page_url) {
			// error_log(json_encode($title));
			$player_name = $title['player'];
			$player_id = Fitet_Monitor_Utils::player_id_by_name($player_name);
			$player_image_url = Fitet_Monitor_Utils::player_image_url($player_id);
			$player_code = Fitet_Monitor_Utils::player_code_by_id($player_id);
			if (!empty($player_page_url) && !empty($player_code) && !Fitet_Monitor_Utils::is_hidden($player_code)) {
				$player_url = Fitet_Monitor_Utils::player_page_url($player_page_url, $player_code, $player_name);
			} else {
				$player_url = '';
			}

			$title['player'] = $this->components['playerCell']->render(
				['playerId' => $player_id, 'playerName' => $player_name, 'playerPageUrl' => $player_url, 'playerImage' => $player_image_url]
			);
			if ($this->multi_club)
				$title['club'] = $this->components['clubCell']->render(
					['clubCode' => $title['clubCode'], 'clubName' => $title['clubName'], 'clubPageUrl' => '', 'clubLogo' => Fitet_Monitor_Utils::club_logo_by_code($title['clubCode'])]
				);
			return $title;
		}, $titles);
	}


}
