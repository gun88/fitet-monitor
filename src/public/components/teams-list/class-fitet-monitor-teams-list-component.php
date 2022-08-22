<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'public/components/team-card/class-fitet-monitor-team-card-component.php';

class Fitet_Monitor_Teams_List_Component extends Fitet_Monitor_Component {
	protected function components() {
		return [
			'teamCard' => new Fitet_Monitor_Team_Card_Component($this->plugin_name, $this->version, ['showLink' => true]),
		];
	}

	protected function process_data($data) {
		return [
			'lastUpdate' => $data['lastUpdate'],
			'filter' => $this->filter($data['seasons'], $data['seasonId']),
			'mainContent' => $this->main_content($data['teams'])
		];

	}


	private function main_content($data) {
		if (empty($data)) {
			return "<p style='text-align: center'>" . __('No Results', 'fitet-monitor') . "</p>";
		}
		$data = array_map(function ($player) {
			return $this->components['teamCard']->render($player);
		}, $data);
		return implode('<hr>', $data);
	}

	private function filter($seasons, $season_id) {
		$filters = '<div><img alt="filter" src="' . FITET_MONITOR_ICON_FILTER . '"/><span>' . __('Season', 'fitet-monitor') . '</span>';
		$filters .= "<select id='fm-team-list-season-filter'>";
		foreach ($seasons as $season) {
			$filters .= "<option value='" . $season['seasonId'] . "' " . ($season_id == $season['seasonId'] ? 'selected' : '') . ">" . $season['seasonName'] . "</option>";
		}
		$filters .= "</select>";
		$filters .= '</div>';
		return $filters;

	}


}
