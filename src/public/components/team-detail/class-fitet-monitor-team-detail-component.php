<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'public/components/team-card/class-fitet-monitor-team-card-component.php';
require_once FITET_MONITOR_DIR . 'public/components/team-standings/class-fitet-monitor-team-standings-component.php';
require_once FITET_MONITOR_DIR . 'public/components/team-calendar/class-fitet-monitor-team-calendar-component.php';

class Fitet_Monitor_Team_Detail_Component extends Fitet_Monitor_Component {
	protected function components() {
		return [
			'teamCard' => new Fitet_Monitor_Team_Card_Component($this->plugin_name, $this->version, ['addAnchor' => true]),
			'teamStandings' => new Fitet_Monitor_Team_Standings_Component($this->plugin_name, $this->version),
			'teamCalendar' => new Fitet_Monitor_Team_Calendar_Component($this->plugin_name, $this->version),
		];
	}


	protected function process_data($data) {

		return [
			'standings' => $this->components['teamStandings']->render($data['standings']),
			'calendar' => $this->components['teamCalendar']->render($data),
			'pageMenu' => $this->menu(),
			'mainContent' => $this->main_content($data),
		];
	}

	private function menu() {
		$menu_entries = [];
		$menu_entries[] = '<a href="#players"><img alt="players" src="' . FITET_MONITOR_ICON_PLAYER . '"/>' . __('Players') . '</a>';
		$menu_entries[] = '<a href="#standings"><img alt="standings" src="' . FITET_MONITOR_ICON_CHART . '"/>' . __('Standings') . '</a>';
		$menu_entries[] = '<a href="#calendar"><img alt="calendar" src="' . FITET_MONITOR_ICON_CALENDAR . '"/>' . __('Calendar') . '</a>';

		return implode('|', $menu_entries);
	}

	private function main_content($data) {
		return $this->components['teamCard']->render($data);
	}

	private function calendar($data) {
		return '<code>' .
			json_encode($data, 128) .
			'</code>';
	}

	private function standings($data) {
		return '<code>' .
			json_encode($data, 128) .
			'</code>';

	}

}
