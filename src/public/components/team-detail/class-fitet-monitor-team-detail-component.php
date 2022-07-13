<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'public/components/team-card/class-fitet-monitor-team-card-component.php';
require_once FITET_MONITOR_DIR . 'public/components/team-standings/class-fitet-monitor-team-standings-component.php';
require_once FITET_MONITOR_DIR . 'public/components/team-statistics/class-fitet-monitor-team-statistics-component.php';
require_once FITET_MONITOR_DIR . 'public/components/team-calendar/class-fitet-monitor-team-calendar-component.php';

class Fitet_Monitor_Team_Detail_Component extends Fitet_Monitor_Component {
	protected function components() {
		return [
			'teamCard' => new Fitet_Monitor_Team_Card_Component($this->plugin_name, $this->version, ['showStatistics' => true]),
			'teamStatistics' => new Fitet_Monitor_Team_Statistics_Component($this->plugin_name, $this->version),
			'teamStandings' => new Fitet_Monitor_Team_Standings_Component($this->plugin_name, $this->version),
			'teamCalendar' => new Fitet_Monitor_Team_Calendar_Component($this->plugin_name, $this->version),
		];
	}


	protected function process_data($data) {

		return [
			'standings' => $this->components['teamStandings']->render($data['standings']),
			'calendar' => $this->components['teamCalendar']->render($data),
			'statistics' => $this->components['teamStatistics']->render($data),
			'pageMenu' => $this->menu(),
			'mainContent' => $this->main_content($data),
		];
	}

	private function menu() {
		$menu_entries = [];
		$menu_entries[] = '<a href="#statistics"><img alt="statistics" src="' . FITET_MONITOR_ICON_CHART . '"/>' . __('Statistics', 'fitet-monitor') . '</a>';
		$menu_entries[] = '<a href="#standings"><img alt="standings" src="' . FITET_MONITOR_ICON_LIST . '"/>' . __('Standings', 'fitet-monitor') . '</a>';
		$menu_entries[] = '<a href="#calendar"><img alt="calendar" src="' . FITET_MONITOR_ICON_CALENDAR . '"/>' . __('Calendar', 'fitet-monitor') . '</a>';

		return implode('|', $menu_entries);
	}

	private function main_content($data) {
		return $this->components['teamCard']->render($data);
	}

}
