<?php
require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'public/components/table/class-fitet-monitor-table-component.php';
require_once FITET_MONITOR_DIR . 'public/components/cells/class-fitet-monitor-player-cell-component.php';
require_once FITET_MONITOR_DIR . 'public/components/cells/class-fitet-monitor-club-cell-component.php';

class Fitet_Monitor_Players_Caps_Component extends Fitet_Monitor_Component {


	protected function components() {
		return [
			'table' => new Fitet_Monitor_Table_Component($this->plugin_name, $this->version),
			'playerCell' => new Fitet_Monitor_Player_Cell_Component($this->plugin_name, $this->version),
			'clubCell' => new Fitet_Monitor_Club_Cell_Component($this->plugin_name, $this->version),
		];
	}

	protected function process_data($data) {
		$data = array_merge(['players' => [], 'listUrl' => '#', 'tableUrl' => '#', 'multiClub' => true], $data);

		$show_championships = false;/* || array_sum(array_map(function ($player) {
				return $player['tournaments'];
			}, $data['players'])) > 0;*/

		return [
			'pageMenu' => $this->menu($data['listUrl'], $data['tableUrl']),
			'mainContent' => $this->main_content($data['players'], $data['multiClub'], $show_championships),
		];

	}

	private function main_content($players, $multi_club, $show_championships) {
		if (empty($players)) {
			return "<p style='text-align: center'>" . __('No Results', 'fitet-monitor') . "</p>";
		}

		$players = $this->rows($players, $multi_club);
		usort($players, function ($r1, $r2) {
			return $r2['total'] - $r1['total'];
		});
		return $this->components['table']->render([
			'name' => 'fm-players-caps',
			'columns' => $this->columns($multi_club, $show_championships),
			'sort' => $this->sort(),
			'rows' => $players,
		]);
	}

	private function menu($list_url, $table_url) {
		$menu_entries = [];
		$menu_entries[] = '<a href="' . $list_url . '"><img alt="list" src="' . FITET_MONITOR_ICON_LIST . '"/><span>' . __('List', 'fitet-monitor') . '</span></a>';
		$menu_entries[] = '<a href="' . $table_url . '"><img alt="caps" src="' . FITET_MONITOR_ICON_TABLE . '"/><span>' . __('Table', 'fitet-monitor') . '</span></a>';
		$menu_entries[] = '<span><img alt="caps" src="' . FITET_MONITOR_ICON_HASHTAG . '"/>' . __('Caps', 'fitet-monitor') . '</span>';

		return implode('|', $menu_entries);
	}

	private function columns($multi_club, $show_championships) {
		$columns = [];
		$columns  ['playerName'] = __('Name', 'fitet-monitor');
		if ($show_championships) { // todo terminare quando non ci saranno problemi di connessione sul sito fitet
			$columns  ['tournaments'] = __('Tournaments', 'fitet-monitor');
			$columns  ['championships'] = __('Championships', 'fitet-monitor');
			$columns  ['total'] = __('Total', 'fitet-monitor');
		} else {
			$columns  ['total'] = __('Caps', 'fitet-monitor');
		}

		if ($multi_club) {
			$columns['club'] = __('Club', 'fitet-monitor');
		}
		return $columns;
	}


	private function sort() {
		return [
			'tournaments' => 'number',
			'championships' => 'number',
			'total' => 'number',
		];
	}

	private function rows($players, $multi_club) {
		return array_map(function ($player) use ($multi_club) {
			$row = [
				'playerName' => $this->components['playerCell']->render(['playerId' => $player['playerId'], 'playerName' => $player['playerName'], 'playerPageUrl' => $player['playerUrl']]),
				'tournaments' => $player['tournaments'],
				'championships' => $player['championships'],
				'total' => $this->total($player['tournaments'], $player['championships']),
				'clubName' => $player['clubName'],
				'clubCode' => $player['clubCode'],
				'playerUrl' => $player['playerUrl'],

			];
			if ($multi_club) {
				$row['club'] = $this->components['clubCell']->render([
					'clubCode' => $player['clubCode'],
					'clubName' => $player['clubName'],
					'clubLogo' => $player['clubLogo'],
					'clubPageUrl' => $player['clubPageUrl']
				]);
			}
			return $row;
		}, $players);

	}


	private function total($tournaments, $championships): int {
		return intval($tournaments) + intval($championships);
	}


}
