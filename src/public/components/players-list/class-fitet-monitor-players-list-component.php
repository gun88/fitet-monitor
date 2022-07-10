<?php

require_once FITET_MONITOR_DIR . 'public/includes/class-fitet-monitor-shortcode.php';
require_once FITET_MONITOR_DIR . 'public/components/player-card/class-fitet-monitor-player-card-component.php';


class Fitet_Monitor_Players_List_Component extends Fitet_Monitor_Component {

	public function components() {
		return [
			'playerCard' => new Fitet_Monitor_Player_Card_Component($this->plugin_name, $this->version, [
				'showBest' => false,
				'showDiff' => false,
				'showPlayerCode' => false,
				'showRegion' => false,
				'showSex' => false,
				'showBirthDate' => false,
			]),
		];
	}

	protected function process_data($data) {
		$data = array_merge(['filter' => '', 'players' => [], 'tableUrl' => '#', 'capsUrl' => '#'], $data);
		return [
			'pageMenu' => $this->menu($data['filter'], $data['tableUrl'], $data['capsUrl']),
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

	private function menu($filter, $table_url, $caps_url) {
		$menu_entries = [];
		$filters = $this->filter($filter);
		$menu_entries[] = '<div><img alt="filter" src="' . FITET_MONITOR_ICON_FILTER . '"/><span>' . __('Filter') . '</span>' . $filters . '</div>';
		$menu_entries[] = '<a href="' . $table_url . '"><img alt="table" src="' . FITET_MONITOR_ICON_TABLE . '"/><span>' . __('Table') . '</span></a>';
		$menu_entries[] = '<a href="' . $caps_url . '"><img alt="caps" src="' . FITET_MONITOR_ICON_HASHTAG . '"/><span>' . __('Caps') . '</span></a>';

		return implode('|', $menu_entries);
	}


	private function filter($filter): string {
		$str = "<select id='fm-player-list-filter'>";
		$str .= "<option " . empty($filter) . " value='none'>" . __('None') . "</option>";
		$str .= "<option " . ($filter == 'Italiani' ? 'selected' : '') . " value='Italiani'>" . __('Italiani') . "</option>";
		$str .= "<option " . ($filter == 'Stranieri' ? 'selected' : '') . " value='Stranieri'>" . __('Stranieri') . "</option>";
		$str .= "<option " . ($filter == 'Fuori Quadro' ? 'selected' : '') . " value='Fuori Quadro'>" . __('Fuori Quadro') . "</option>";
		$str .= "<option " . ($filter == 'Provvisori' ? 'selected' : '') . " value='Provvisori'>" . __('Provvisori') . "</option>";
		$str .= "</select>";
		return $str;
	}

}
