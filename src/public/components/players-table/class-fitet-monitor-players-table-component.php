<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'public/components/table/class-fitet-monitor-table-component.php';
require_once FITET_MONITOR_DIR . 'public/components/cells/class-fitet-monitor-player-cell-component.php';
require_once FITET_MONITOR_DIR . 'public/components/cells/class-fitet-monitor-club-cell-component.php';

class Fitet_Monitor_Players_Table_Component extends Fitet_Monitor_Component {

	protected function components() {
		return [
			'table' => new Fitet_Monitor_Table_Component($this->plugin_name, $this->version),
			'playerCell' => new Fitet_Monitor_Player_Cell_Component($this->plugin_name, $this->version),
			'clubCell' => new Fitet_Monitor_Club_Cell_Component($this->plugin_name, $this->version),
		];
	}

	protected function process_data($data) {
		$data = array_merge(['players' => [], 'listUrl' => '#', 'capsUrl' => '#', 'multiClub' => true], $data);
		return [
			'pageMenu' => $this->menu($data['listUrl'], $data['capsUrl']),
			'mainContent' => $this->main_content($data['players'], $data['multiClub']),
		];

	}

	private function main_content($players, $multi_club) {
		if (empty($players)) {
			return "<p style='text-align: center'>" . __('No Results', 'fitet-monitor') . "</p>";
		}

		return $this->components['table']->render([
			'name' => 'fm-players-table',
			'columns' => $this->columns($multi_club),
			'sort' => $this->sort(),
			'rows' => $this->rows($players, $multi_club),
		]);
	}

	private function menu($list_url, $caps_url) {
		$menu_entries = [];
		$menu_entries[] = '<a href="' . $list_url . '"><img alt="list" src="' . FITET_MONITOR_ICON_LIST . '"/><span>' . __('List', 'fitet-monitor') . '</span></a>';
		$menu_entries[] = '<span><img alt="table" src="' . FITET_MONITOR_ICON_TABLE . '"/>' . __('Table', 'fitet-monitor') . '</span>';
		$menu_entries[] = '<a href="' . $caps_url . '"><img alt="caps" src="' . FITET_MONITOR_ICON_HASHTAG . '"/><span>' . __('Caps', 'fitet-monitor') . '</span></a>';

		return implode('|', $menu_entries);
	}


	private function columns($multi_club) {
		$columns = [
			'playerName' => __('Name', 'fitet-monitor'),
			'points' => __('Points', 'fitet-monitor'),
			'rank' => __('Rank', 'fitet-monitor'),
			'diff' => __('Diff.', 'fitet-monitor'),
			'category' => __('Category', 'fitet-monitor'),
			'sector' => __('Sector', 'fitet-monitor'),
		];
		if ($multi_club)
			$columns['club'] = __('Club', 'fitet-monitor');
		return $columns;
	}

	private function sort() {
		return [
			'points' => 'number',
			'rank' => 'number',
			'diff' => 'number',
			'category' => 'number',
		];
	}

	private function rows($players, $multi_club) {
		return array_map(function ($player) use ($multi_club) {
			$row = [
				'playerName' => $this->components['playerCell']->render(['playerId' => $player['playerId'], 'playerName' => $player['playerName'], 'playerPageUrl' => $player['playerUrl']]),
				'points' => $player['points'],
				'rank' => $this->rank($player['rank'], $player['type']),
				'diff' => $player['diff'],
				'category' => $player['category'],
				'sector' => $this->sector($player['sector'], $player['sex']),
			];
			if ($multi_club) {
				$row['club'] = $this->components['clubCell']->render([
					'clubCode' => $player['clubCode'],
					'clubName' => $player['clubName'],
					'clubPageUrl' => $player['clubPageUrl']
				]);
			}
			return $row;
		}, $players);

	}

	private function sector($sector, $sex) {
		if ($sex == 'M')
			return $sector . ' - ' . __('Men', 'fitet-monitor');
		if ($sex == 'F')
			return $sector . ' - ' . __('Women', 'fitet-monitor');
		return $sector;
	}

	private function rank($rank, $type) {
		return $type == 'Italiani' ? $rank : $type;
	}


}
