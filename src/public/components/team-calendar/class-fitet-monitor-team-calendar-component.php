<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'public/components/table/class-fitet-monitor-table-component.php';
require_once FITET_MONITOR_DIR . 'public/components/cells/class-fitet-monitor-team-cell-component.php';

class Fitet_Monitor_Team_Calendar_Component extends Fitet_Monitor_Component {


	protected function components() {
		return [
			'table' => new Fitet_Monitor_Table_Component($this->plugin_name, $this->version),
			'teamCell' => new Fitet_Monitor_Team_Cell_Component($this->plugin_name, $this->version),
		];
	}

	protected function process_data($data) {

		$tables = "";
		foreach ([true, false] as $first_leg) {
			foreach ($data['calendar'] as $day) {
				$championshipDay = $day[0]['championshipDay'];
				if (empty($championshipDay)) {
					continue;
				}
				$tables .= "<h4>" . __('Day', 'fitet-monitor') . ' ' . $championshipDay . ' - ' . ($first_leg ? __('First Leg', 'fitet-monitor') : __('Return Match', 'fitet-monitor')) . "</h4>";
				$tables .= $this->components['table']->render($this->table($day, $first_leg, $data['teamId'], $data['teamName'], $data['standings']));

			}
		}

		return [
			'teamCalendarLabel' => __('Calendar', 'fitet-monitor'),
			'mainContent' => $tables,
		];
	}

	private function table($day, $first_leg, $main_team_id, $main_team_name, $standings) {

		return [
			'name' => 'fm-team-calendar-' . ($first_leg ? 'firt-leg' : '-return-match') . $main_team_id,
			'paginate' => false,
			'search' => false,
			'columns' => [
				'match' => __('Home', 'fitet-monitor'),
				'date' => __('Date', 'fitet-monitor'),
				'time' => __('Time', 'fitet-monitor'),
				'result' => __('Result', 'fitet-monitor'),
			],
			'rows' => array_map(function ($match) use ($first_leg, $standings, $main_team_name) {
				return [
					'_rowClass' => $this->is_main_team($main_team_name, $match['home'], $match['away'],) ? 'fm-team-calendar-main-team' : '',
					'match' => $this->match($first_leg, $match, $standings),
					'date' => $match['returnMatch']['date'],
					'time' => $match['returnMatch']['time'],
					'result' => $match['returnMatch']['result'],
				];
			}, $day),
		];
	}


	private function match($first_leg, $match, $standings) {
		$home_team = $first_leg ? $match['away'] : $match['home'];
		$away_team = $first_leg ? $match['home'] : $match['away'];
		$str = $this->teams($home_team, $standings) . $this->teams($away_team, $standings);
		return "<div class='fm-team-calendar-match'>$str</div>";
	}

	private function teams($team_name, $standings) {

		$standing = array_values(array_filter($standings, function ($standing) use ($team_name) {
			return trim($standing['teamName'] == trim($team_name));
		}));
		if (empty($standing) || count($standing) > 1) {
			$data = ['clubCode' => '', 'teamName' => $team_name, 'teamPageUrl' => '', 'clubLogo' => ''];
			return $this->components['teamCell']->render($data);
		}

		$standing = $standing[0];
		$team_id = $standing['teamId'];
		$club_code = $standing['clubCode'];
		$club_logo = $standing['clubLogo'];
		$team_page_url = $standing['teamPageUrl'];
		$data = ['clubCode' => $club_code, 'teamName' => $team_name, 'teamPageUrl' => $team_page_url, 'clubLogo' => $club_logo];

		return $this->components['teamCell']->render($data);
	}

	private function is_main_team($main_team_name, $home, $away) {
		if (trim($main_team_name) == trim($home))
			return true;
		if (trim($main_team_name) == trim($away))
			return true;
		return false;
	}


}
