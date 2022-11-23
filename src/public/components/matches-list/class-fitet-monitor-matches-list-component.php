<?php

require_once FITET_MONITOR_DIR . 'public/includes/class-fitet-monitor-shortcode.php';
require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'public/components/cells/class-fitet-monitor-team-cell-component.php';
require_once FITET_MONITOR_DIR . 'public/utils/class-fitet-monitor-utils.php';

class Fitet_Monitor_Matches_List_Component extends Fitet_Monitor_Component {

	protected function components() {
		return [
			'teamCell' => new Fitet_Monitor_Team_Cell_Component($this->plugin_name, $this->version),
		];
	}

	protected function process_data($data) {

		$data = array_merge(...$data);

		foreach ($data as &$championship) {

			foreach ($championship['calendar'] as &$calendar) {
				foreach ($calendar as &$match) {
					$match['firstLeg']['seasonId'] = $championship['seasonId'];
					$match['firstLeg']['seasonName'] = $championship['seasonName'];
					$match['firstLeg']['championshipId'] = $championship['championshipId'];
					$match['firstLeg']['championshipName'] = $championship['championshipName'];
					$match['firstLeg']['championshipDay'] = $match['championshipDay'];
					$match['firstLeg']['homeTeamName'] = $match['home'];
					$match['firstLeg']['awayTeamName'] = $match['away'];
					$match['firstLeg']['homeTeamId'] = $this->find_team_id_by_name($championship['standings'], $match['firstLeg']['homeTeamName']);
					$match['firstLeg']['awayTeamId'] = $this->find_team_id_by_name($championship['standings'], $match['firstLeg']['awayTeamName']);
					$match['firstLeg']['homeClubCode'] = $this->find_club_code_by_name($championship['standings'], $match['firstLeg']['homeTeamName']);
					$match['firstLeg']['awayClubCode'] = $this->find_club_code_by_name($championship['standings'], $match['firstLeg']['awayTeamName']);
					$match['firstLeg']['ownedHomeTeam'] = $this->is_owned_team($championship['standings'], $match['firstLeg']['homeTeamId']);
					$match['firstLeg']['ownedAwayTeam'] = $this->is_owned_team($championship['standings'], $match['firstLeg']['awayTeamId']);
					$match['firstLeg']['homeResult'] = trim(explode('-', $match['firstLeg']['result'])[0]);
					$match['firstLeg']['awayResult'] = trim(explode('-', $match['firstLeg']['result'])[1]);

					$match['returnMatch']['seasonId'] = $championship['seasonId'];
					$match['returnMatch']['seasonName'] = $championship['seasonName'];
					$match['returnMatch']['championshipId'] = $championship['championshipId'];
					$match['returnMatch']['championshipName'] = $championship['championshipName'];
					$match['returnMatch']['championshipDay'] = $match['championshipDay'];
					$match['returnMatch']['homeTeamName'] = $match['away'];
					$match['returnMatch']['awayTeamName'] = $match['home'];
					$match['returnMatch']['homeTeamId'] = $this->find_team_id_by_name($championship['standings'], $match['returnMatch']['homeTeamName']);
					$match['returnMatch']['awayTeamId'] = $this->find_team_id_by_name($championship['standings'], $match['returnMatch']['awayTeamName']);
					$match['returnMatch']['homeClubCode'] = $this->find_club_code_by_name($championship['standings'], $match['returnMatch']['homeTeamName']);
					$match['returnMatch']['awayClubCode'] = $this->find_club_code_by_name($championship['standings'], $match['returnMatch']['awayTeamName']);
					$match['returnMatch']['ownedHomeTeam'] = $this->is_owned_team($championship['standings'], $match['returnMatch']['awayTeamId']);
					$match['returnMatch']['ownedAwayTeam'] = $this->is_owned_team($championship['standings'], $match['returnMatch']['homeTeamId']);
					$match['returnMatch']['homeResult'] = $this->parse_result($match['returnMatch']['result'])['away'];
					$match['returnMatch']['awayResult'] = $this->parse_result($match['returnMatch']['result'])['home'];

					unset($match['championshipDay']);
					unset($match['home']);
					unset($match['away']);

					if (empty($match['firstLeg']['date'])) {
						unset($match['firstLeg']);
					}
					if (empty($match['returnMatch']['date'])) {
						unset($match['returnMatch']);
					}
					$match = array_values($match);

				}

				$calendar = array_merge(...$calendar);

			}
			$championship['calendar'] = array_merge(...$championship['calendar']);

			unset($championship['standings']);
			unset($championship['seasonId']);
			unset($championship['seasonName']);
			unset($championship['championshipId']);
			unset($championship['championshipName']);


		}

		$data = array_map(function ($championship) {
			return $championship['calendar'];
		}, $data);

		$data = array_merge(...$data);


		$data = array_values(array_filter($data, function ($match) {
			return $match['ownedHomeTeam'] || $match['ownedAwayTeam'];
		}));

		/*$data = array_values(array_filter($data, function ($match) {
			return $match['homeTeamId'] == 26019 || $match['awayTeamId'] == 26019;
		}));*/

		foreach ($data as &$match) {
			$match['datetime'] = implode('-', array_reverse(explode('/', $match['date'])));
			$match['datetime'] .= '_' . str_replace('.', ':', $match['time']);
		}

		usort($data, function ($a, $b) {
			return strcmp($a['datetime'], $b['datetime']);
		});

		$result = [];
		foreach ($data as $element) {
			$result[$element['date']][] = $element;
		}

		$data = $result;

		//$data = array_values($data)[0];

		$implode = implode('', array_map(function ($day, $matches) {
			$day = $date = str_replace('/', '-', $day);
			// setlocale(LC_ALL, $_SERVER['HTTP_ACCEPT_LANGUAGE']);
			setlocale(LC_ALL, 'IT'); // todo fix locale
			$strftime = ucfirst(utf8_encode(strftime("%A %e %B %Y", strtotime($day))));
			$date = date('l jS F Y', strtotime($day));
			$pre = $day == '13-11-2022' ? "<a id='fm-match-now'></a>" : '';
			return "$pre<div class='fm-match-day-label'>$strftime</div>" .
				"<div class='fm-match-grid'>" .
				implode('', array_map(function ($match) {
					// table table-striped fm-table fm-table-fm-team-calendar-first-leg27785 dynatable-loaded

					$date = $match['date'];
					$time = $match['time'];
					$home_result = $match['homeResult'];
					$away_result = $match['awayResult'];
					$matchId = $match['match'];
					$formula = $match['formula'];
					$seasonId = $match['seasonId'];
					$seasonName = $match['seasonName'];
					$championshipId = $match['championshipId'];
					$championshipName = $match['championshipName'];
					$championshipDay = $match['championshipDay'];
					$home_cell = $this->teams($match['homeTeamName'], $match['homeClubCode']);
					$away_cell = $this->teams($match['awayTeamName'], $match['awayClubCode']);

					$home_class = $this->extract_team_class($home_result, $away_result);
					$away_class = $this->extract_team_class($away_result, $home_result);

					$home_class .= $match['ownedHomeTeam'] ? ' fm-match-team-owned' : '';
					$away_class .= $match['ownedAwayTeam'] ? ' fm-match-team-owned' : '';

					$_match = $match; // todo remove
					$match = file_get_contents(__DIR__ . "/temp.html");
					$match = str_replace('$date', $date, $match);
					$match = str_replace('$time', $time, $match);
					$match = str_replace('$home_result', $home_result, $match);
					$match = str_replace('$away_result', $away_result, $match);
					$match = str_replace('$matchId', $matchId, $match);
					$match = str_replace('$formula', $formula, $match);
					$match = str_replace('$seasonId', $seasonId, $match);
					$match = str_replace('$seasonName', $seasonName, $match);
					$match = str_replace('$championshipId', $championshipId, $match);
					$match = str_replace('$championshipName', $championshipName, $match);
					$match = str_replace('$championshipDay', $championshipDay, $match);
					$match = str_replace('$home_cell', $home_cell, $match);
					$match = str_replace('$away_cell', $away_cell, $match);
					$match = str_replace('$home_class', $home_class, $match);
					$match = str_replace('$away_class', $away_class, $match);
					//	$match = str_replace('$pre', $away_cell, $match);
					return $match// . "<pre>" . json_encode($_match, 128) . "</pre>"
						;

				}, $matches)) . "</div>";
		}, array_keys($data), $data));

		return $implode;
	}

	private function teams($team_name, $club_code) {

		$data = ['clubCode' => $club_code, 'teamName' => $team_name, 'teamPageUrl' => '', 'clubLogo' => Fitet_Monitor_Utils::club_logo_by_code($club_code)];

		return $this->components['teamCell']->render($data);
	}

	private function find_team_id_by_name($standings, $team_name) {

		foreach ($standings as $team) {
			if ($team['teamName'] == $team_name) {
				return $team['teamId'];
			}
		}
		return '';
	}

	private function find_club_code_by_name($standings, $team_name) {

		foreach ($standings as $team) {
			if ($team['teamName'] == $team_name) {
				return $team['clubCode'];
			}
		}
		return '';
	}

	private function is_owned_team($standings, $team_id) {
		foreach ($standings as $standing) {
			if (($standing['teamId'] == $team_id) && !empty($standing['players'])) {
				return true;
			}
		}
		return false;
	}

	private function parse_result($result) {
		if (empty($result)) {
			$home = '';
			$away = '';
		} else {
			$parts = explode('-', $result);
			$home = trim($parts[0]);
			$away = trim($parts[1]);
		}

		return ['home' => $home, 'away' => $away];
	}

	private function extract_team_class($home_result, $away_result) {
		if (trim($home_result) == '' || trim($away_result) == '') {
			return 'fm-match-pristine';
		}
		if ($home_result > $away_result) {
			return 'fm-match-won';
		}
		if ($home_result < $away_result) {
			return 'fm-match-lost';
		}

		if ($home_result == $away_result) {
			return 'fm-match-draw';
		}
	}


}

function flatten_array(array $demo_array) {
	$new_array = array();
	array_walk_recursive($demo_array, function ($array) use (&$new_array) {
		$new_array[] = $array;
	});
	return $new_array;
}
