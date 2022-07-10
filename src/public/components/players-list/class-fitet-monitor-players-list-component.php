<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'public/components/player-card/class-fitet-monitor-player-card-component.php';


class Fitet_Monitor_Players_List_Component extends Fitet_Monitor_Component {

	public function components() {
		return [
			'playerCard' => new Fitet_Monitor_Player_Card_Component($this->plugin_name, $this->version, [
				'showDiff' => false,
				'showPlayerCode' => false,
				'showRegion' => false,
				'showSex' => false,
				'showBirthDate' => false,
			]),
		];
	}

	protected function process_data($data) {
		$data = array_merge(['filter' => '', 'players' => [], 'tableUrl' => '#', 'capsUrl' => '#', 'showFilter' => false], $data);
		return [
			'pageMenu' => $this->menu($data['tableUrl'], $data['capsUrl']),
			'filter' => $data['showFilter'] ? $this->getFilter($data['filter']) : '',
			'mainContent' => $this->main_content($data['players']),
		];
	}

	private function main_content($data) {
		if (empty($data)) {
			return "<p style='text-align: center'>" . __('No Results') . "</p>";
		}
		$data = array_map(function ($player) {
			return $this->components['playerCard']->render($player);
		}, $data);
		return implode('<hr>', $data);
	}

	private function menu($table_url, $caps_url) {
		$menu_entries = [];
		$menu_entries[] = '<span><img alt="list" src="' . FITET_MONITOR_ICON_LIST . '"/>' . __('List') . '</span>';
		$menu_entries[] = '<a href="' . $table_url . '"><img alt="table" src="' . FITET_MONITOR_ICON_TABLE . '"/><span>' . __('Table') . '</span></a>';
		$menu_entries[] = '<a href="' . $caps_url . '"><img alt="caps" src="' . FITET_MONITOR_ICON_HASHTAG . '"/><span>' . __('Caps') . '</span></a>';

		return implode('|', $menu_entries);
	}


	private function getFilter($filter): string {
		$filters = '<div><img alt="filter" src="' . FITET_MONITOR_ICON_FILTER . '"/><span>' . __('Filter') . '</span>';
		$filters .= "<select id='fm-player-list-filter'>";
		$filters .= "<option " . empty($filter) . " value='none'>" . __('None') . "</option>";
		$filters .= "<option " . ($filter == 'Italiani' ? 'selected' : '') . " value='Italiani'>" . __('Italiani') . "</option>";
		$filters .= "<option " . ($filter == 'Stranieri' ? 'selected' : '') . " value='Stranieri'>" . __('Stranieri') . "</option>";
		$filters .= "<option " . ($filter == 'Fuori Quadro' ? 'selected' : '') . " value='Fuori Quadro'>" . __('Fuori Quadro') . "</option>";
		$filters .= "<option " . ($filter == 'Provvisori' ? 'selected' : '') . " value='Provvisori'>" . __('Provvisori') . "</option>";
		$filters .= "</select>";
		$filters .= '</div>';
		return $filters;
	}

}
