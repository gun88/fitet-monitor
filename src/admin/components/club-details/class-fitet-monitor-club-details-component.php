<?php


require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'public/components/players-table/class-fitet-monitor-players-table-component.php';
require_once FITET_MONITOR_DIR . 'public/components/cells/class-fitet-monitor-player-cell-component.php';
require_once FITET_MONITOR_DIR . 'public/components/cells/class-fitet-monitor-team-cell-component.php';


class Fitet_Monitor_Club_Details_Component extends Fitet_Monitor_Component {

	private function labels() {
		return [
			'clubNameLabel' => __('Club name', 'fitet-monitor'),
			'affiliationDateLabel' => __('Affiliation date', 'fitet-monitor'),
			'emailLabel' => __('E-mail', 'fitet-monitor'),
			'statusLabel' => __('Status', 'fitet-monitor'),
			'lastUpdateLabel' => __('Last Update', 'fitet-monitor'),
			'playersLabel' => __('Players', 'fitet-monitor'),
			'championshipsLabel' => __('Championships', 'fitet-monitor'),
			'downloadFullHistoryLabel' => __('Download Full Championships History', 'fitet-monitor'),
			'downloadFullHistoryDisclaimer' => __('WARNING: This operation may take a while', 'fitet-monitor'),
			'nationalTitlesLabel' => __('National Titles', 'fitet-monitor'),
			'regionalTitlesLabel' => __('Regional Titles', 'fitet-monitor'),
			'updatingDisclaimer' => __('INFO: Data not available while updating', 'fitet-monitor'),
			'resetStatusLabel' => __('Reset Status', 'fitet-monitor')
		];
	}

	/**
	 * @param $championships
	 * @return array
	 */
	public function group_by_season_id($championships): array {
		$result = [];
		foreach ($championships as $championship) {
			$result[$championship['seasonId']][] = $championship;
		}
		return $result;
	}


	public function not_loaded_season_id_list($championships) {
		$acc = [];
		foreach ($championships as $championship) {
			if (empty($championship['standings']))
				$acc [] = $championship['seasonId'];
		}
		$acc = array_unique($acc);
		sort($acc);
		return $acc;
	}

	/**
	 * @param $championshipName
	 * @param $standings
	 * @return string
	 */
	function team_cell_part($club_code, $championship): string {

		$championshipName = $championship['championshipName'];
		$standings = $championship['standings'];
		return implode('', array_map(function ($standing) use ($championshipName) {
			$standing['teamName'] = $championshipName . ' - ' . $standing['teamName'];
			if (!empty($standing['players'])) {
				$toggles = "<span class='fm-toggle fm-expand'>&#9660;</span>" .
					"<span class='fm-toggle fm-collapse'>&#9650;</span>";
				$players = "<div class='fm-team-players-list fm-closed'>" .
					implode("", array_map(function ($player) {
						return $this->components['playerCell']->render($player);
					}, $standing['players'])) .
					"</div>";
			} else {
				$toggles = '';
				$players = '';
			}

			return "<div class='fm-team-cell-wrapper fm-closed'>" . $this->components['teamCell']->render($standing) .
				"<button>Export</button>" .
				"<button>Import</button>" .
				$toggles . "</div>" . $players;
		}, $standings));
	}

	protected function script_dependencies(): array {
		return ['jquery', 'wp-api'];
	}


	protected function components() {
		return [
			'playersTable' => new Fitet_Monitor_Players_Table_Component($this->plugin_name, $this->version),
			'playerCell' => new Fitet_Monitor_Player_Cell_Component($this->plugin_name, $this->version),
			'teamCell' => new Fitet_Monitor_Team_Cell_Component($this->plugin_name, $this->version),
			'table' => new Fitet_Monitor_Table_Component($this->plugin_name, $this->version),
		];
	}


	public function process_data($data) {

		$data = array_merge(['players' => [], 'championships' => [], 'nationalTitles' => [], 'regionalTitles' => []], $data);

		$displayUpdatingDisclaimer = $data['status'] == 'updating';

		return array_merge($this->labels(), [
			'clubCode' => $data['clubCode'],
			'clubName' => $data['clubName'],
			'status' => $this->status($data['status']),
			'lastUpdate' => $data['lastUpdate'],
			'players' => $this->player_table($data['players']),
			'championships' => $this->championships_table($data['championships'], $data['clubCode']),
			'not_loaded_seasons' => json_encode($this->not_loaded_season_id_list($data['championships'])),
			'nationalTitles' => $this->titles_table($data['nationalTitles'], 'national'),
			'regionalTitles' => $this->titles_table($data['regionalTitles'], 'regional'),
			'resetStatusUrl' => add_query_arg(['action' => 'resetStatus', 'clubCode' => $data['clubCode']], menu_page_url('fitet-monitor', false)),

			'displayUpdatingDisclaimer' => $displayUpdatingDisclaimer ? 'block' : 'none',
		]);

	}

	public function player_table($players) {
		if (empty($players)) {
			return "<p style='text-align: center'>" . __('No Results', 'fitet-monitor') . "</p>";
		}
		return $this->components['table']->render([
			'name' => 'fm-players-table',
			'columns' => [
				'playerCode' => __('Player Code', 'fitet-monitor'),
				'playerName' => __('Player', 'fitet-monitor'),
				'rank' => __('Rank', 'fitet-monitor'),
				'diff' => __('Diff', 'fitet-monitor'),
				'points' => __('Points', 'fitet-monitor'),
				'category' => __('Category', 'fitet-monitor'),
				'birthDate' => __('Birth Date', 'fitet-monitor'),
				'sector' => __('Sector', 'fitet-monitor'),
				'sex' => __('Sex', 'fitet-monitor'),
				'type' => __('Type', 'fitet-monitor'),
			],

			'sort' => [
				'playerCode' => 'number',
				'points' => 'number',
				'rank' => 'number',
				'diff' => 'number',
				'category' => 'number',
			],
			'rows' => array_map(function ($player) {
				$player['playerName'] = $this->components['playerCell']->render(['playerId' => $player['playerId'], 'playerName' => $player['playerName'], 'playerPageUrl' => '']);
				return $player;
			}, $players),
		]);
	}

	public function titles_table($titles, $prefix) {
		if (empty($titles)) {
			return "<p style='text-align: center'>" . __('No Results', 'fitet-monitor') . "</p>";
		}
		return $this->components['table']->render([
			'name' => "fm-$prefix-titles-table",
			'columns' => [
				'season' => __('Season', 'fitet-monitor'),
				'tournament' => __('Tournament', 'fitet-monitor'),
				'competition' => __('Competition', 'fitet-monitor'),
				'player' => __('Player', 'fitet-monitor'),
			],

			'sort' => [
				'season' => 'number',
			],
			'rows' => array_map(function ($title) {
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
				return $title;
			}, $titles),
		]);
	}

	private function status($status) {
		switch ($status) {
			case 'updating':
				return __('Updating', 'fitet-monitor');
			case 'new':
				return __('New', 'fitet-monitor');
			case 'ready':
			default:
				return __('Ready', 'fitet-monitor');
		}
	}

	private function championships_table($championships, $club_code) {
		if (empty($championships)) {
			return "<p style='text-align: center'>" . __('No Results', 'fitet-monitor') . "</p>";
		}

		foreach ($championships as &$championship) {
			$championship['standings'] = array_values(array_filter($championship['standings'], function ($standings) use ($club_code) {
				return ($standings['clubCode']) == $club_code;
			}));
		}

		$championships = $this->group_by_season_id($championships);


		$championships = array_map(function ($championship) use ($club_code) {
			$teams = implode('', array_map(function ($championship) use ($club_code) {
				return $this->team_cell_part($club_code, $championship);
			}, $championship));

			$season_id = $championship[0]['seasonId'];
			return [
				'seasonId' => $season_id,
				'seasonName' => $championship[0]['seasonName'],
				'teams' => "<div>$teams</div>",
				'standings' => $this->count__($championship, 'standings'),
				'calendar' => $this->count__($championship, 'standings'),
				'actions' => "<div style='display: flex;justify-content: center;'>" .
					"<button href='#' title='" . __('Update', 'fitet-monitor') . "' " .
					"class='fm-update-single-championship' data-club-code='$club_code' data-season-id='$season_id'>" .
					"<img style='width: 24px' alt='update-button' src='" . FITET_MONITOR_ICON_CLOUD_ARROW . "'/></button>" .
					"<button href='#' title='" . __('Reset', 'fitet-monitor') . "' " .
					"class='fm-reset-single-championship' data-club-code='$club_code' data-season-id='$season_id'>" .
					"<img style='width: 24px' alt='reset-button' src='" . FITET_MONITOR_ICON_ERASER . "'/></button>" .
					"</div>",
			];
		}, array_values($championships));

		return $this->components['table']->render([
			'name' => "fm-championships-table",
			'columns' => [
				'seasonName' => __('Season', 'fitet-monitor'),
				'seasonId' => __('Season id', 'fitet-monitor'),
				//'championshipId' => __('Championship id', 'fitet-monitor'),
				'teams' => __('Teams', 'fitet-monitor'),
				'standings' => __('Standings', 'fitet-monitor'),
				'calendar' => __('Calendar', 'fitet-monitor'),
				'actions' => __('Actions', 'fitet-monitor'),
				//'json' => __('json', 'fitet-monitor'),
				//'championshipName' => __('Championship', 'fitet-monitor'),
			],
			'sort' => [
				'seasonName' => 'number',
			],
			'rows' => $championships,
		]);
	}

	private function count__($championship, $field) {
		$total = count($championship);

		$loaded = count(array_values(array_filter($championship, function ($championship) use ($field) {
			return !empty($championship[$field]);
		})));

		return "$loaded/$total";

	}

}
