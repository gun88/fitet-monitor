<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'public/components/player-card/class-fitet-monitor-player-card-component.php';


class Fitet_Monitor_Players_List_Component extends Fitet_Monitor_Component {

	public function components() {
		return [
			'playerCard' => new Fitet_Monitor_Player_Card_Component($this->plugin_name, $this->version, [
				'showDiff' => false,
				'showRegion' => false,
				'showSex' => false,
				'showBirthDate' => false,
			]),
		];
	}

	protected function process_data($data) {
		$data = array_merge(['filter' => '', 'players' => [], 'tableUrl' => '#', 'capsUrl' => '#', 'showFilter' => true], $data);
		return [
			'pageMenu' => $this->menu($data['tableUrl'], $data['capsUrl']),
			'filter' => $data['showFilter'] ? $this->getFilter($data['filter']) : '',
			'mainContent' => $this->main_content($data['players']),
		];
	}

	private function main_content($data) {
		if (empty($data)) {
			return "<p style='text-align: center'>" . __('No Results', 'fitet-monitor') . "</p>";
		}
		$data = array_map(function ($player) {
			$inner = $this->components['playerCard']->render($player);
			$player_type_id = str_replace(' ', '-', strtolower($player['type']));
			return "<div class='fm-player-type-$player_type_id'>" .
				"$inner" .
				"<hr>" .
				"</div>";
		}, $data);
		return implode('', $data);
	}

	private function menu($table_url, $caps_url) {
		$menu_entries = [];
		$menu_entries[] = '<span><img alt="list" src="' . FITET_MONITOR_ICON_LIST . '"/>' . __('List', 'fitet-monitor') . '</span>';
		$menu_entries[] = '<a href="' . $table_url . '"><img alt="table" src="' . FITET_MONITOR_ICON_TABLE . '"/><span>' . __('Table', 'fitet-monitor') . '</span></a>';
		$menu_entries[] = '<a href="' . $caps_url . '"><img alt="caps" src="' . FITET_MONITOR_ICON_HASHTAG . '"/><span>' . __('Caps', 'fitet-monitor') . '</span></a>';

		return implode('|', $menu_entries);
	}


	private function getFilter($filter): string {
		$filters = '';
		// $filters .= '<div><img alt="filter" src="' . FITET_MONITOR_ICON_FILTER . '"/><span>' . __('Filter', 'fitet-monitor') . '</span>';
		// $filters .= "<select id='fm-player-list-filter'>";
		// $filters .= "<option " . empty($filter) . " value='none'>" . __('None', 'fitet-monitor') . "</option>";
		// $filters .= "<option " . ($filter == 'Italiani' ? 'selected' : '') . " value='Italiani'>" . __('Italiani', 'fitet-monitor') . "</option>";
		// $filters .= "<option " . ($filter == 'Stranieri' ? 'selected' : '') . " value='Stranieri'>" . __('Stranieri', 'fitet-monitor') . "</option>";
		// $filters .= "<option " . ($filter == 'Fuori Quadro' ? 'selected' : '') . " value='Fuori Quadro'>" . __('Fuori Quadro', 'fitet-monitor') . "</option>";
		// $filters .= "<option " . ($filter == 'Provvisori' ? 'selected' : '') . " value='Provvisori'>" . __('Provvisori', 'fitet-monitor') . "</option>";
		// $filters .= "</select>";
		// $filters .= '</div>';
		$filters .= '<div><img alt="filter" src="' . FITET_MONITOR_ICON_FILTER . '"/><span>' . __('Filter', 'fitet-monitor') . '</span>';
		$filters .= '<label for="fm-pt-it"><input type="checkbox" id="fm-pt-it" checked name="italiani"/>Italiani</label>';
		$filters .= '<label for="fm-pt-st"><input type="checkbox" id="fm-pt-st" checked name="stranieri"/>Stranieri</label>';
		$filters .= '<label for="fm-pt-pr"><input type="checkbox" id="fm-pt-pr" name="provvisori"/>Provvisori</label>';
		$filters .= '<label for="fm-pt-fq"><input type="checkbox" id="fm-pt-fq" name="fuori-quadro"/>Fuori Quadro</label>';
		$filters .= '</div>';
		return $filters;
	}

}
