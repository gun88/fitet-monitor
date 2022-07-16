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

		$players = $data['status'] == 'ready' ? $this->process_players($data['players']) : [];
		$championships = $data['status'] == 'ready' ? $data['championships'] : [];
		$nationalTitles = $data['status'] == 'ready' ? $data['nationalTitles'] : [];
		$regionalTitles = $data['status'] == 'ready' ? $data['regionalTitles'] : [];
		$displayUpdatingDisclaimer = $data['status'] == 'updating';


		$championships = $this->rework_championships($championships);

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
			'resetStatusLabel' => __('Reset Status', 'fitet-monitor'),

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

			'displayUpdatingDisclaimer' => $displayUpdatingDisclaimer ? 'block' : 'none',
			'displayPlayers' => !empty($players) ? 'block' : 'none',
			'displayChampionships' => !empty($championships) ? 'block' : 'none',
			'displayNationalTitles' => !empty($nationalTitles) ? 'block' : 'none',
			'displayRegionalTitles' => !empty($regionalTitles) ? 'block' : 'none',
		];

	}

	public function player_table() {
		$columns = [];
		$columns['playerName'] = __('Name', 'fitet-monitor');
		$columns['rank'] = __('Rank', 'fitet-monitor');
		$columns['points'] = __('Points', 'fitet-monitor');
		$columns['category'] = __('Category', 'fitet-monitor');
		$columns['sector'] = __('Sector', 'fitet-monitor');
		$columns['diff'] = __('Diff', 'fitet-monitor');
		$columns['birthDate'] = __('BirthDate', 'fitet-monitor');
		$columns['sex'] = __('Sex', 'fitet-monitor');
		$columns['playerCode'] = __('Code', 'fitet-monitor');
		$columns['playerId'] = __('Id', 'fitet-monitor');
		return new Fitet_Monitor_Bootstrap_Table([
			'columns' => $columns
		]);
	}


	public function championships_table() {
		$columns = [];
		$columns['seasonName'] = __('Season', 'fitet-monitor');
		$columns['names'] = __('Names', 'fitet-monitor');
		return new Fitet_Monitor_Bootstrap_Table([
			'columns' => $columns
		]);
	}

	public function national_titles_table() {
		$columns = [];
		$columns['season'] = __('Season', 'fitet-monitor');
		$columns['tournament'] = __('Tournament', 'fitet-monitor');
		$columns['competition'] = __('Competition', 'fitet-monitor');
		$columns['player'] = __('Player', 'fitet-monitor');
		return new Fitet_Monitor_Bootstrap_Table([
			'columns' => $columns
		]);
	}

	public function regional_titles_table() {
		$columns = [];
		$columns['season'] = __('Season', 'fitet-monitor');
		$columns['tournament'] = __('Tournament', 'fitet-monitor');
		$columns['competition'] = __('Competition', 'fitet-monitor');
		$columns['player'] = __('Player', 'fitet-monitor');
		return new Fitet_Monitor_Bootstrap_Table([
			'columns' => $columns
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

	private function process_players($players) {


		foreach ($players as &$player) {
			$name = $player['playerName'];
			$url = "#";
			$player['playerName'] = "<a href='$url' >$name</a>";

		}

		return $players;
	}

	private function rework_championships($championships) {
		$g = [];

		foreach ($championships as $championship) {
			if (isset($g[$championship['seasonName']])) {
				$g[$championship['seasonName']] .= ' - ' . $championship['championshipName'];
			} else {
				$g[$championship['seasonName']] = $championship['championshipName'];
			}
		}
		$y = [];
		foreach ($g as $k => $v) {
			$y[] = [
				'seasonName' => $k,
				'names' => $v,
			];
		}
		return $y;
	}


}
