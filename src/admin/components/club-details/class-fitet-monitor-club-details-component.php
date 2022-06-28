<?php


require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-bootstrap-table.php';


class Fitet_Monitor_Club_Details_Component extends Fitet_Monitor_Component {

	protected function script_dependencies(): array {
		return ['jquery', 'wp-api'];
	}

	public function enqueue_scripts() {
		parent::enqueue_scripts();
		$file = FITET_MONITOR_DIR . "public/assets/bootstrap-table.js";
		$file = plugin_dir_path($file) . basename($file);
		Fitet_Monitor_Helper::enqueue_script("bootstrap-table.js", $file, ['jquery'], $this->version, false);
	}

	public function enqueue_styles() {
		parent::enqueue_styles();
		$file = FITET_MONITOR_DIR . "public/assets/fitet-monitor-bootstrap.css";
		$file = plugin_dir_path($file) . basename($file);
		Fitet_Monitor_Helper::enqueue_style("bootstrap.css", $file, [], $this->version, 'all');

	}


	public function process_data($data) {

		$players_table = $this->player_table();
		$championships_table = $this->championships_table();
		$national_titles_table = $this->national_titles_table();
		$regional_titles_table = $this->regional_titles_table();
		$attendances_table = $this->attendances_table();

		$players = $data['status'] == 'ready' ? $this->process_players($data['players']) : [];
		$championships = $data['status'] == 'ready' ? $data['championships'] : [];
		$nationalTitles = $data['status'] == 'ready' ? $data['nationalTitles'] : [];
		$regionalTitles = $data['status'] == 'ready' ? $data['regionalTitles'] : [];
		$attendances = $data['status'] == 'ready' ? $data['attendances'] : [];
		$displayUpdatingDisclaimer = $data['status'] == 'updating';

		return [
			'clubNameLabel' => __('Club name'),
			'affiliationDateLabel' => __('Affiliation date'),
			'emailLabel' => __('E-mail'),
			'statusLabel' => __('Status'),
			'lastUpdateLabel' => __('Last Update'),
			'playersLabel' => __('Players'),
			'championshipsLabel' => __('Championships'),
			'downloadFullHistoryLabel' => __('Download Full Championships History'),
			'downloadFullHistoryDisclaimer' => __('WARNING: This operation may take a while'),
			'nationalTitlesLabel' => __('National Titles'),
			'regionalTitlesLabel' => __('Regional Titles'),
			'attendancesLabel' => __('Attendances'),
			'updatingDisclaimer' => __('INFO: Data not available while updating'),

			'clubCode' => $data['clubCode'],
			'clubName' => $data['clubName'],
			'affiliationDate' => $data['affiliationDate'],
			'email' => $data['email'],
			'status' => $this->status($data['status']),
			'lastUpdate' => $data['lastUpdate'],
			'players' => $players_table->render($players),
			'championships' => $championships_table->render($championships),
			'nationalTitles' => $national_titles_table->render($nationalTitles),
			'regionalTitles' => $regional_titles_table->render($regionalTitles),
			'attendances' => $attendances_table->render($attendances),

			'displayUpdatingDisclaimer' => $displayUpdatingDisclaimer,
			'displayPlayers' => !empty($players) ? 'block' : 'none',
			'displayChampionships' => !empty($championships) ? 'block' : 'none',
			'displayNationalTitles' => !empty($nationalTitles) ? 'block' : 'none',
			'displayRegionalTitles' => !empty($regionalTitles) ? 'block' : 'none',
			'displayAttendances' => !empty($attendances) ? 'block' : 'none',
		];

	}

	public function player_table() {
		$columns = [];
		$columns['name'] = __('Name');
		$columns['rank'] = __('Rank');
		$columns['points'] = __('Points');
		$columns['category'] = __('Category');
		$columns['sector'] = __('Sector');
		$columns['diff'] = __('Diff');
		$columns['birthDate'] = __('BirthDate');
		$columns['sex'] = __('Sex');
		$columns['code'] = __('Code');
		return new Fitet_Monitor_Bootstrap_Table([
			'columns' => $columns
		]);
	}


	public function championships_table() {
		$columns = [];
		$columns['name'] = __('Name');
		$columns['seasonName'] = __('Season');
		return new Fitet_Monitor_Bootstrap_Table([
			'columns' => $columns
		]);
	}

	public function national_titles_table() {
		$columns = [];
		$columns['season'] = __('Season');
		$columns['tournament'] = __('Tournament');
		$columns['competition'] = __('Competition');
		$columns['player'] = __('Player');
		return new Fitet_Monitor_Bootstrap_Table([
			'columns' => $columns
		]);
	}

	public function regional_titles_table() {
		$columns = [];
		$columns['season'] = __('Season');
		$columns['tournament'] = __('Tournament');
		$columns['competition'] = __('Competition');
		$columns['player'] = __('Player');
		return new Fitet_Monitor_Bootstrap_Table([
			'columns' => $columns
		]);
	}

	public function attendances_table() {
		$columns = [];
		$columns['playerName'] = __('Name');
		$columns['playerCode'] = __('Code');
		$columns['count'] = __('Attendences');
		return new Fitet_Monitor_Bootstrap_Table([
			'columns' => $columns
		]);
	}

	private function status($status) {
		switch ($status) {
			case 'updating':
				return __('Updating');
			case 'new':
				return __('New');
			case 'ready':
			default:
				return __('Ready');
		}
	}

	private function process_players($players) {


		foreach ($players as &$player) {
			$name = $player['name'];
			$json = json_encode($player);
			$player['name'] = "<a href='#' onclick='showPlayer(event, $json)'>$name</a>";

		}

		return $players;
	}


}
