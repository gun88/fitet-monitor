<?php

if (!class_exists('simple_html_dom_node')) {
	require_once FITET_MONITOR_DIR . '/admin/includes/simple_html_dom.php';
}

class Fitet_Portal_Rest {

	public static $ranking_types = [
		['id' => 1, 'name' => 'Italiani'],
		['id' => 2, 'name' => 'Fuori Quadro'],
		['id' => 3, 'name' => 'Stranieri'],
		['id' => 9, 'name' => 'Provvisori']
	];

	public static $ranking_sex = [
		['id' => 'M', 'name' => 'Maschile'],
		['id' => 'F', 'name' => 'Femminile']
	];


	/**
	 * @var Fitet_Portal_Rest_Http_Service
	 */
	public $http_service;

	public function __construct($http_service) {
		$this->http_service = $http_service;
	}

	/**
	 * @param string $bgcolor
	 * @return string
	 */
	function calculate_marker(string $bgcolor): string {
		switch ($bgcolor) {
			case '#FFD700':
				$marker = 'gold';
				break;
			case '#C0C0C0':
				$marker = 'silver';
				break;
			case '#CD7F32':
				$marker = 'bronze';
				break;
			default:
				$marker = 'blank';
		}
		return $marker;
	}

	private function setHttpService($http_service) {
		$this->http_service = $http_service;
	}


	public function find_rankings() {
		$url = "http://portale.fitet.org/menu.php";
		$html_string = $this->http_service->get($url);
		$html = str_get_html($html_string);
		$rows = $html->find('p.testa a');

		// mapping html results
		$ranking_id_list = array_map(function ($row) {
			$href = $row->href;
			$date = $row->innertext;
			preg_match('/ID_CLASS=(-?\d+)/', $href, $matches);
			if (array_key_exists(1, $matches)) {
				$id = $matches[1];
				return ['date' => $date, 'rankingId' => $id];
			} else {
				return "";
			}
		}, $rows);

		// removing non-ranking entries
		$ranking_id_list = array_filter($ranking_id_list);

		// transorming oject to array
		$ranking_id_list = array_values($ranking_id_list);

		// sorting rankings
		usort($ranking_id_list, function ($r1, $r2) {
			return intval($r2['rankingId']) - intval($r1['rankingId']);
		});

		return $ranking_id_list;
	}

	public function get_ranking($ranking_id, $sex, $type, $club_code = null) {
		$sex_id = $sex['id'];
		$type_id = $type['id'];
		$url = "http://portale.fitet.org/fpdf2/excel_classifica.php?SESSO=$sex_id&TIPO=$type_id&CLASSIFICA=$ranking_id";

		$csv_string = $this->http_service->get($url);
		$csv_string = str_replace('&#220;', 'Ü', $csv_string);
		//$csv_string = mb_convert_encoding($csv_string, "UTF-8","Windows-1252");
		// splitting result by line
		$csv = array_filter(preg_split("/[\r\n]+/", $csv_string));

		// removing non printable lines
		$csv[0] = preg_replace('/[[:^print:]]/', '', $csv[0]);

		// converting CSV lines to array
		$csv = array_map(function ($csv_row) {
			return str_getcsv($csv_row, ";");
		}, $csv);

		// retrieving CSV header
		$header = array_shift($csv);

		// convert header fields to camelCase
		$header = array_map(function ($h) {
			return str_replace('_', '', ucwords($h, '_'));
		}, $header);

		// creating tmp objects - merging lines with header
		$ranking = array_map(function ($csv_row) use ($header) {
			return array_combine($header, $csv_row);
		}, $csv);

		// filter by club code if set
		if ($club_code != null) {
			$ranking = array_filter($ranking, function ($rank) use ($club_code) {
				return $rank['IdSocieta'] == $club_code;
			});
		}

		// restoring array indexes
		$ranking = array_values($ranking);

		// mapping objects with actual names
		return array_map(function ($rank) use ($sex, $type) {
			$r = intval($rank['Classifica']);
			$r = $r == 0 ? "N/A" : $r;
			return [
				'playerName' => $rank['NomeAtleta'],
				'rank' => $r,
				'points' => intval($rank['Punti']),
				'category' => intval($rank['Categoria']),
				'sector' => $rank['Settore'],
				'diff' => intval($rank['Diff']),
				'birthDate' => $rank['DataNascita'],
				'region' => $rank['RegioneComitato'],
				'clubCode' => intval($rank['IdSocieta']),
				'clubName' => $rank['NomeSocieta'],
				'sex' => $sex['id'],
				'type' => $type['name']
			];
		}, $ranking);
	}

	public function find_players($name, $birth_date = null) {
		$url = "http://archivio.fitet.org/corpo.php";

		// manage special characters
		$_name = array_reduce(preg_split("/[^a-zA-Z\d\s.\-]/", $name), function ($a, $b) {
			$strlen_a = $a == null ? 0 : strlen($a);
			$strlen_b = $b == null ? 0 : strlen($b);
			return ($strlen_a > 5 || $strlen_a > $strlen_b) ? $a : $b;
		});

		$body = "group1=one&nome=$_name";
		$html_string = $this->http_service->post($url, $body);
		$html = str_get_html($html_string);

		$players = $html->find('#tabs-1 a');

		$players = array_map(function ($player) {
			preg_match('/.+ATLETA=(-?\d+)/', $player->href, $matches);
			$player_id = $matches[1];
			preg_match('/^(\d*)\s-\s(.+?)\s\(([\d\/]*)\)$/', $player->innertext, $matches);
			$player_code = $matches[1];
			$player_name = $matches[2];
			$player_birth_date = $matches[3];
			return [
				'playerId' => $player_id,
				'playerCode' => $player_code,
				'playerName' => $player_name,
				'birthDate' => $player_birth_date,
			];

		}, $players);

		// filtering by exact name
		$players = array_filter($players, function ($player) use ($name) {
			return strcasecmp($player['playerName'], $name) == 0;
		});

		// filtering by birth date if provided
		if ($birth_date != null) {
			$players = array_filter($players, function ($player) use ($birth_date) {
				return $player['birthDate'] == $birth_date;
			});
		}

		// restoring indexes
		$players = array_values($players);

		// sort by recent player code
		usort($players, function ($a, $b) {
			return intval($b['playerCode']) - intval($a['playerCode']);
		});
		return $players;
	}


	public function get_player_season($player_id, $ranking_id) {
		$url = "http://portale.fitet.org/risultati/new_rank/dettaglioatleta_unica.php?ATLETA=$player_id&ID_CLASS=$ranking_id";
		$html_string = $this->http_service->get($url, ['X-Requested-With' => 'XMLHttpRequest']);
		$html = str_get_html($html_string);

		$season = $html->find('#fragment-2 tr');

		// skipping table header
		array_shift($season);

		$season = array_map(function ($row) {
			return array_map(function ($cell) {
				return implode(' - ', array_map(function ($p) {
					return $p->innertext;
				}, $cell->find('p')));
			}, $row->find('td'));
		}, $season);

		// mapping to history object
		return array_map(function ($column) {
			return [
				'opponent' => $column[0],
				'date' => $column[1],
				'match' => $column[2],
				'win' => $column[3] == 'V',
				'points' => doubleval(str_replace(',', '.', $column[4])),
			];
		}, $season);


	}

	public function get_player_history($player_id) {

		$url = "http://archivio.fitet.org/scheda_dettaglio_atleta.php?ATLETA=$player_id";
		$html_string = $this->http_service->get($url);
		$html = str_get_html($html_string);

		$ranking = $html->find('#fragment-4 tr');

		// skipping table header
		array_shift($ranking);

		$ranking = array_map(function ($row) {
			return $row->find('p');
		}, $ranking);

		// mapping to history object
		$ranking = array_map(function ($column) {
			return [
				'date' => $column[0]->innertext,
				'position' => $column[1]->innertext,
				'points' => $column[2]->innertext,
				'category' => $column[3]->innertext];
		}, $ranking);

		$championships = $html->find('#fragment-3 tr');

		// skipping table header
		array_shift($championships);

		$championships = array_map(function ($row) {
			return $row->find('p');
		}, $championships);

		// mapping to history object
		$championships = array_map(function ($column) {
			$href = $column[1]->find('a')[0]->href;
			preg_match('/.+CAMP=(-?\d+)/', $href, $matches);
			$championship_id = $matches[1];
			preg_match('/.+ANNATA=(-?\d+)/', $href, $matches);
			$season_id = $matches[1];
			return [
				'season' => $column[0]->innertext,
				'championshipName' => $column[1]->plaintext,
				'championshipId' => $championship_id,
				'seasonId' => $season_id,
				'type' => $column[2]->innertext,
				'teamName' => $column[3]->innertext,
				'playerPosition' => $column[4]->innertext,
				'matchCount' => $column[5]->innertext,
				'matchWin' => $column[6]->innertext,
				'matchLost' => $column[7]->innertext,
				'matchPercentage' => $column[8]->innertext,
			];
		}, $championships);

		$regionalTournaments = $html->find('#fragment-2 tr');

		// skipping table headers
		array_shift($regionalTournaments);
		array_shift($regionalTournaments);

		$regionalTournaments = array_map(function ($row) {
			return $row->find('td');
		}, $regionalTournaments);

		// mapping to history object
		$regionalTournaments = array_map(function ($column) {
			$marker = $this->calculate_marker(isset($column[5]) ? $column[5]->bgcolor : '');
			return [
				'season' => trim($column[0]->plaintext),
				'date' => trim($column[1]->plaintext),
				'region' => trim($column[2]->plaintext),
				'tournament' => trim($column[3]->plaintext),
				'competition' => trim($column[4]->plaintext),
				'round' => isset($column[5]) ? trim($column[5]->plaintext) : '',
				'marker' => $marker,
			];
		}, $regionalTournaments);


		$nationalTournaments = $html->find('#fragment-1 table')[0]->find('tr');

		// skipping table headers
		array_shift($nationalTournaments);
		array_shift($nationalTournaments);

		$nationalTournaments = array_map(function ($row) {
			return $row->find('td');
		}, $nationalTournaments);

		// mapping to history object
		$nationalTournaments = array_map(function ($column) {
			$marker = $this->calculate_marker(isset($column[4]) ? $column[4]->bgcolor : '');
			return [
				'season' => trim($column[0]->plaintext),
				'date' => trim($column[1]->plaintext),
				'tournament' => trim($column[2]->plaintext),
				'competition' => trim($column[3]->plaintext),
				'round' => isset($column[4]) ? trim($column[4]->plaintext) : '',
				'marker' => $marker,
			];
		}, $nationalTournaments);


		$nationalDoublesTournaments = $html->find('#fragment-1 table')[1]->find('tr');

		// skipping table headers
		array_shift($nationalDoublesTournaments);
		array_shift($nationalDoublesTournaments);

		$nationalDoublesTournaments = array_map(function ($row) {
			return $row->find('td');
		}, $nationalDoublesTournaments);

		// mapping to history object
		$nationalDoublesTournaments = array_map(function ($column) {
			$marker = $this->calculate_marker(isset($column[5]) ? $column[5]->bgcolor : '');
			return [
				'season' => trim($column[0]->plaintext),
				'date' => trim($column[1]->plaintext),
				'team' => trim($column[2]->plaintext),
				'tournament' => trim($column[3]->plaintext),
				'competition' => trim($column[4]->plaintext),
				'round' => isset($column[5]) ? trim($column[5]->plaintext) : '',
				'marker' => $marker,
			];
		}, $nationalDoublesTournaments);

		return [
			'ranking' => $ranking,
			'nationalTournaments' => $nationalTournaments,
			'nationalDoublesTournaments' => $nationalDoublesTournaments,
			'regionalTournaments' => $regionalTournaments,
			'championships' => $championships,
		];


	}

	public function find_clubs($name_contains) {
		$url = "http://archivio.fitet.org/corpo.php";

		$body = "soc=one&nomesoc=$name_contains";
		$html_string = $this->http_service->post($url, $body);

		$html = str_get_html($html_string);
		$rows = $html->find('#tabs-3 a');

		// mapping html results
		return array_map(function ($row) {
			preg_match('/.+TEAM=(-?\d+)/', $row->href, $matches);
			$club_code = $matches[1];
			preg_match('/(.+)\s+\((\w+)\)/', $row->innertext, $matches);
			$club_name = $matches[1];
			$club_province = $matches[2];
			$club['clubCode'] = $club_code;
			$club['clubName'] = $club_name;
			$club['clubProvince'] = $club_province;
			$club['clubLogo'] = "http://portale.fitet.org/images/societa/$club_code.jpg";
			return $club;
		}, $rows);
	}

	public function get_club_info($club_code) {
		$url = "http://archivio.fitet.org/scheda_testa_soc.php?TEAM=$club_code";
		$html_string = $this->http_service->get($url);
		$html = str_get_html($html_string);

		$rows = $html->find('a');

		// mapping to anchor inner-text
		$rows = array_map(function ($row) {
			return $row->innertext;
		}, $rows);

		// filtering entries containing @
		$rows = array_filter($rows, function ($row) {
			return strpos($row, "@");
		});

		// restoring array indexes
		$rows = array_values($rows);

		$email = $rows[0] ?? "N/A";

		// extracting name and province
		$line = $html->find('tr', 1)->find('td', 0)->innertext;
		preg_match('/^(.+)\s\((.+)\)$/', $line, $matches);
		$club_name = $matches[1] ?? "N/A";
		$club_province = $matches[2] ?? "N/A";

		// extracting date
		$line = $html->find('tr', 2)->find('td', 0)->innertext;
		$affiliation_date = preg_replace('/[a-zA-Z\s]/', '', $line);

		$affiliation_date = empty($affiliation_date) ? "N/A" : $affiliation_date;

		return [
			"clubCode" => $club_code,
			"clubName" => $club_name,
			"clubProvince" => $club_province,
			"email" => $email,
			"affiliationDate" => $affiliation_date];
	}

	public function get_club_details($club_code, $history_size = null) {
		$url = "http://archivio.fitet.org/scheda_dettaglio_soc.php?TEAM=$club_code";
		$html_string = $this->http_service->get($url);
		$html_string = preg_replace('/[[:^print:]]/', '', $html_string);
		$html = str_get_html($html_string);

		// retrieving championships data
		$cells = $html->find('#campionati th');

		$season_name = null;
		$championships = [];
		foreach ($cells as $cell) {
			if ($cell->hasAttribute('rowspan')) {
				$season_name = $cell->plaintext;
			} else {
				$link = $cell->find('a', 0);
				$championship_name = $link->innertext;
				preg_match('/.+CAMP=(-?\d+)/', $link->href, $matches);
				$championship_id = $matches[1];
				preg_match('/.+ANNATA=(-?\d+)/', $link->href, $matches);
				$season_id = $matches[1];
				// add if allowed by param
				$championships[] = [
					'seasonId' => intval($season_id),
					'seasonName' => $season_name,
					'championshipId' => intval($championship_id),
					'championshipName' => $championship_name,
				];
			}
		}

		if ($history_size != null && !empty($championships)) {
			$max_season_id = $championships[0]['seasonId'] - $history_size;
			$championships = array_filter($championships, function ($championship) use ($max_season_id) {
				return $championship['seasonId'] > $max_season_id;
			});
		}

		// retrieving national tournaments data
		$national_titles = $html->find('#nazionali tr');

		// removing useless header rows
		array_shift($national_titles);
		array_shift($national_titles);

		// retrieving values
		$national_titles = array_map(function ($row) {
			$row = $row->find('p');
			return array_map(function ($cell) {
				return $cell->plaintext;
			}, $row);
		}, $national_titles);

		// creating object
		$national_titles = array_map(function ($row) {
			return array_combine(['season', 'tournament', 'competition', 'player'], $row);
		}, $national_titles);


		// retrieving regional tournaments data
		$regional_titles = $html->find('#regionali tr');

		// removing useless header rows
		array_shift($regional_titles);
		array_shift($regional_titles);

		// retrieving values
		$regional_titles = array_map(function ($row) {
			$row = $row->find('p');
			return array_map(function ($cell) {
				return $cell->plaintext;
			}, $row);
		}, $regional_titles);

		// creating object
		$regional_titles = array_map(function ($row) {
			return array_combine(['season', 'tournament', 'competition', 'player'], $row);
		}, $regional_titles);

		// retrieving caps data
		$caps = $html->find('#presenze tr');

		// removing useless header rows
		array_shift($caps);

		// retrieving values
		$caps = array_map(function ($row) {
			return array_map(function ($r) {
				return $r->plaintext;
			}, $row->find('p'));
		}, $caps);

		// creating object
		$caps = array_map(function ($row) {
			return array_combine(['playerCode', 'playerName', 'count'], $row);
		}, $caps);

		return [
			'championships' => $championships,
			'nationalTitles' => $national_titles,
			'regionalTitles' => $regional_titles,
			'caps' => $caps,
		];

	}

	public function get_championship_standings($championship_id, $season_id) {
		$url = "http://portale.fitet.org/risultati/archivio/ClassificaSquadre.asp?ANNATA=$season_id&CAMP=$championship_id";
		$html_string = $this->http_service->get($url);
		$html = str_get_html($html_string);
		$rows = $html->find('tr');

		// removing useless header
		array_shift($rows);

		// removing useless header
		array_shift($rows);

		// removing useless footer
		array_pop($rows);

		return array_map(function ($team) {
			$link = $team->find('td', 1)->find('a', 0);
			preg_match('/.+SQUADRA=(-?\d+)/', $link->href, $matches);
			$team_id = $matches[1];
			$team_name = trim($link->innertext);
			$team_status_cell = $team->find('td', 1)->find('p', 0);
			if ($team_status_cell->hasClass('dettagli_promo'))
				$team_status = 'promotion';
			else if ($team_status_cell->hasClass('dettagli_Playoff'))
				$team_status = 'playoff';
			else if ($team_status_cell->hasClass('dettagli_Playout'))
				$team_status = 'playout';
			else if ($team_status_cell->hasClass('dettagli_retroc'))
				$team_status = 'relegation';
			else
				$team_status = 'neutral';

			return [
				'teamId' => intval($team_id),
				'teamName' => $team_name,
				'teamStatus' => $team_status,
				'points' => intval($team->find('p', 1)->innertext),
				'id' => intval($team->find('p', 2)->innertext),
				'iv' => intval($team->find('p', 3)->innertext),
				'ipa' => intval($team->find('p', 4)->innertext),
				'ip' => intval($team->find('p', 5)->innertext),
				'pav' => intval($team->find('p', 6)->innertext),
				'pap' => intval($team->find('p', 7)->innertext),
				'sv' => intval($team->find('p', 8)->innertext),
				'sp' => intval($team->find('p', 9)->innertext),
				'pv' => intval($team->find('p', 10)->innertext),
				'pp' => intval($team->find('p', 11)->innertext),
				'pe' => intval($team->find('p', 12)->innertext),
			];
		}, $rows);
	}

	public function get_championship_calendar($championship_id, $season_id, $team_names = []) {
		$url = "https://portale.fitet.org/risultati/archivio/Calendario.asp?ANNO=$season_id&CAM=$championship_id";
		$html_string = $this->http_service->get($url);
		// fix not closed tr
		$html_string = preg_replace('/<\/td>\s*?<tr>/i', '</td></tr><tr>', $html_string);
		$html_string = preg_replace('/[[:^print:]]/', '', $html_string);
		$html_string = str_replace("<a href='Giornata.asp?INCONTRO=</a>", '', $html_string);

		$html = str_get_html($html_string);

		// retrieving calendar data
		$tables = $html->find('table');

		// removing useless header
		array_shift($tables);

		$tables = array_map(function ($table) use ($url) {
			return array_map(function ($row) use ($url) {
				return array_map(function ($cell) use ($url) {
					$link = $cell->find('a', 0);
					if ($link != null) {
						preg_match('/.+INCONTRO=(-?\d+)/', $link->href, $matches);
						if (!isset($matches[1]))
							return [];
						$match = $matches[1];
						preg_match('/.+FORMULA=(-?\d+)/', $link->href, $matches);
						$formula = $matches[1];
						return ['result' => $link->innertext,
							'match' => $match,
							'formula' => $formula];
					}
					return $cell->find('p', 0)->plaintext;
				}, $row->find('td'));
			}, $table->find('tr'));
		}, $tables);

		$tables = array_map(function ($table) {
			// filtering out empty rows
			$table = array_filter($table, function ($row) {
				return array_filter($row);
			});
			// restoring indexes
			return array_values($table);
		}, $tables);


		$tables = array_map(function ($table) {
			$championship_day = array_shift($table);
			$championship_day = intval(preg_replace('/\D/', '', $championship_day[0]));


			return array_map(function ($row) use ($championship_day) {

				$row[0] = isset($row[0]) ? $row[0] : '';
				$row[1] = isset($row[1]) ? $row[1] : '';
				$row[2] = isset($row[2]) && is_array($row[2]) ? $row[2] : ['result' => '', 'match' => '', 'formula' => ''];
				$row[2]['result'] = isset($row[2]['result']) ? $row[2]['result'] : '';
				$row[2]['match'] = isset($row[2]['match']) ? $row[2]['match'] : '';
				$row[2]['formula'] = isset($row[2]['formula']) ? $row[2]['formula'] : '';
				$row[3] = isset($row[3]) ? $row[3] : '';
				$row[4] = isset($row[4]) ? $row[4] : '';
				$row[5] = isset($row[5]) && is_array($row[5]) ? $row[5] : ['result' => '', 'match' => '', 'formula' => ''];
				$row[5]['result'] = isset($row[5]['result']) ? $row[5]['result'] : '';
				$row[5]['match'] = isset($row[5]['match']) ? $row[5]['match'] : '';
				$row[5]['formula'] = isset($row[5]['formula']) ? $row[5]['formula'] : '';
				$row[6] = isset($row[6]) ? $row[6] : '';
				$row[7] = isset($row[7]) ? $row[7] : '';

				return [
					'championshipDay' => $championship_day,
					'home' => trim($row[3]),
					'away' => trim($row[4]),
					'firstLeg' => [
						'date' => trim(str_replace('*', '', $row[0])),
						'time' => $row[1],
						'result' => $row[2]['result'],
						'match' => intval($row[2]['match']),
						'formula' => intval($row[2]['formula']),
					],
					'returnMatch' => [
						'date' => trim(str_replace('*', '', $row[7])),
						'time' => $row[6],
						'result' => $row[5]['result'],
						'match' => intval($row[5]['match']),
						'formula' => intval($row[5]['formula']),
					],
				];
			}, $table);


		}, $tables);

		if (!empty($team_names)) {
			$tables = array_map(function ($rows) use ($team_names) {
				return array_values(array_filter($rows, function ($row) use ($team_names) {
					return in_array($row['home'], $team_names) || in_array($row['away'], $team_names);
				}));
			}, $tables);
		}

		return $tables;
	}

	public function get_team_info($team_id, $championship_id, $season_id) {
		$url = "http://portale.fitet.org/risultati/campionati/DatiSquadra.asp?SQUADRA=$team_id&CAM=$championship_id&ANNO=$season_id";
		$html_string = $this->http_service->get($url);
		$html = str_get_html($html_string);

		$team_name = $html->find('table', 0)->find('tr', 0)->find('p', 1)->innertext;

		$club_row = $html->find('table', 1)->find('tr', 1);
		$club_name = $club_row->find('p', 0)->innertext;
		$club_code = intval($club_row->find('p', 1)->innertext);

		return [
			'teamId' => $team_id,
			'teamName' => trim($team_name),
			'clubCode' => $club_code,
			'clubName' => trim($club_name)
		];
	}

	public function get_team_details($team_id, $championship_id, $season_id) {
		$url = "http://portale.fitet.org/risultati/archivio/Percentuali.asp?SQUADRA=$team_id&CAMP=$championship_id&ANNATA=$season_id";

		$html_string = $this->http_service->get($url);
		$html = str_get_html($html_string);

		$rows = $html->find('tr');

		// removing header rows
		array_shift($rows);
		array_shift($rows);

		$players = array_map(function ($row) {
			$link = $row->find('a', 0);
			preg_match('/.+IDA=(-?\d+)/', $link->href, $matches);
			$player_id = $matches[1];
			$player_name = $link->innertext;

			return [
				'playerId' => $player_id,
				'playerName' => utf8_encode($player_name),// todo controlla se si puo rimuovere
				'pd' => intval($row->find('p', 1)->innertext),
				'pav' => intval($row->find('p', 2)->innertext),
				'pap' => intval($row->find('p', 3)->innertext),
				'sv' => intval($row->find('p', 4)->innertext),
				'sp' => intval($row->find('p', 5)->innertext),
				'pv' => intval($row->find('p', 6)->innertext),
				'pp' => intval($row->find('p', 7)->innertext),
				'percentage' => doubleval(str_replace(',', '.', $row->find('p', 8)->innertext),)
			];
		}, $rows);

		return [
			'players' => $players,
		];
	}

}
