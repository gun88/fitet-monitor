<?php

require_once FITET_MONITOR_DIR . 'admin/includes/class-fitet-monitor-manager-logger.php';

class Fitet_Monitor_Utils {

    public static $clubs = null;


    // ok

    public static function player_code_by_id($player_id, $club_code = '') {
        foreach (self::clubs() as $club) {
            foreach ($club['players'] as $player) {
                if ($player['playerId'] == $player_id && (empty($club_code) || $club_code == $player['clubCode'])) {
                    return $player['playerCode'];
                }
            }
        }
        return '';
    }

    public static function player_id_by_code($player_code, $club_code = '') {
        foreach (self::clubs() as $club) {
            foreach ($club['players'] as $player) {
                if ($player['playerCode'] == $player_code && (empty($club_code) || $club_code == $player['clubCode'])) {
                    return $player['playerId'];
                }
            }
        }
        return '';
    }

    public static function player_id_by_name_in_standings($player_name, $club_code) {

        foreach (self::clubs() as $club) {
            if ($club['clubCode'] != $club_code) {
                continue;
            }
            foreach ($club['championships'] as $championship) {
                foreach ($championship['standings'] as $standing) {
                    if ($standing['clubCode'] != $club_code) {
                        continue;
                    }
                    foreach ($standing['players'] as $player) {
                        if ($player_name == $player['playerName'])
                            return $player['playerId'];
                    }

                }
            }
        }
        return '';
    }

    public static function team_id_by_name($championship_id, $season_id, $team_name) {
        foreach (self::clubs() as $club) {
            foreach ($club['championships'] as $championship) {
                foreach ($championship['standings'] as $standing) {
                    if (trim($standing['teamName']) == trim($team_name) &&
                        $championship['seasonId'] == $season_id &&
                        $championship['championshipId'] == $championship_id) {
                        return $standing['teamId'];
                    }
                }
            }
        }
        return '';
    }


    // fine ok

    public static function player_image_url($player_id) {
        if (empty($player_id)) {
            return FITET_MONITOR_PLAYER_NO_IMAGE;
        }
        foreach (['svg', 'SVG', 'png', 'PNG', 'jpg', 'JPG', 'jpeg', 'JPEG'] as $extension) {
            if (file_exists(FITET_MONITOR_UPLOAD_DIR . "/fitet-monitor/players/$player_id.$extension"))
                return FITET_MONITOR_UPLOAD_URL . "/fitet-monitor/players/$player_id.$extension";
        }
        return "http://portale.fitet.org/images/atleti/$player_id.jpg";
    }


    public static function club_logo_by_code($club_code) {
        foreach (['svg', 'SVG', 'png', 'PNG', 'jpg', 'JPG', 'jpeg', 'JPEG'] as $extension) {
            if (file_exists(FITET_MONITOR_UPLOAD_DIR . "/fitet-monitor/clubs/$club_code.$extension"))
                return FITET_MONITOR_UPLOAD_URL . "/fitet-monitor/clubs/$club_code.$extension";
        }
        foreach (self::clubs() as $club) {
            if ($club['clubCode'] == $club_code && isset($club['clubLogo']))
                return $club['clubLogo'];
        }
        return '';
    }

    private static function clubs() {
        // todo deve diventare private
        if (self::$clubs == null) {
            global $fitet_monitor_manager;
            $manager = $fitet_monitor_manager;

            self::$clubs = $manager->get_clubs([
                'clubCode' => '',
                'clubName' => '',
                'clubLogo' => '',
                'lastUpdate' => '',
                'players' => [
                    'playerId' => '',
                    'playerCode' => '',
                    'playerName' => '',
                    'clubCode' => '',
                ],
                'championships' => [
                    'championshipId' => '',
                    'seasonId' => '',
                    'standings' => '',
                ]
            ]);


            // remove not loaded teams
            self::$clubs = array_values(array_filter(self::$clubs, function ($club) {
                return isset($club['lastUpdate']);
            }));


        }

        return self::$clubs;
    }


    public static function player_by_id($player_id) {
        foreach (self::clubs() as $club) {
            foreach ($club['players'] as $player) {
                if ($player['playerId'] == $player_id) {
                    return $player;
                }
            }
        }
        return null;
    }

    public static function player_id_by_name($player_name) {
        $player_id_list = [];
        foreach (self::clubs() as $club) {
            foreach ($club['players'] as $player) {
                if (trim($player['playerName']) == trim($player_name)) {
                    $player_id_list[] = $player['playerId'];
                }
            }
        }

        if (empty($player_id_list))
            return '';
        if (count($player_id_list) > 1)
            error_log("WARNING: multiple player found with name '$player_name' - returning first with id: " . $player_id_list[0]);


        return $player_id_list[0];
    }


    public static function player_page_url($player_base_url, $player_code, $player_name = 'N/A') {
        if ($player_base_url == null || $player_code == null) {
            return null;
        }
        $slug = urlencode(str_replace(" ", "-", $player_name));
        $slug = "$player_code-$slug";
        return "/$player_base_url&player=$slug";

    }

    public static function match_page_url($match_base_url, $match_id) {
        if ($match_base_url == null || $match_id == null) {
            return null;
        }
        return "/$match_base_url&match=$match_id";

    }


    public static function club_code_by_team_id($championship_id, $season_id, $team_id) {
        foreach (self::clubs() as $club) {
            foreach ($club['championships'] as $championship) {
                foreach ($championship['standings'] as $standing) {
                    if (trim($standing['teamId']) == trim($team_id) &&
                        $championship['seasonId'] == $season_id &&
                        $championship['championshipId'] == $championship_id) {
                        return $standing['clubCode'];
                    }
                }
            }
        }
        return '';
    }


    public static function fill_team_rankings($championship) {
        for ($i = 0; $i < count($championship['standings']); $i++) {
            if ($i > 0 && $championship['standings'][$i - 1]['points'] == $championship['standings'][$i]['points']) {
                $championship['standings'][$i]['ranking'] = $championship['standings'][$i - 1]['ranking'];
            } else {
                $championship['standings'][$i]['ranking'] = $i + 1;
            }
        }
        usort($championship['standings'], function ($t1, $t2) {
            if ($t1['ranking'] != $t2['ranking']) {
                return $t1['ranking'] - $t2['ranking'];
            }
            return Fitet_Monitor_Utils::team_status_to_int($t2['teamStatus']) - Fitet_Monitor_Utils::team_status_to_int($t1['teamStatus']);
        });
        return $championship;
    }

    public static function intersect_template($array, $template) {
        $original_template = $template;
        if ($template == null)
            return $array;
        foreach ($template as $key => $value) {

            if (!isset($array[$key])) {
                continue;
            }

            if (empty($value)) {
                $template[$key] = $array[$key];
            } /*else if (null == ($array[$key])) {
				$template[$key] = null;
			}*/ else if (is_scalar($array[$key])) {
                $template[$key] = $array[$key];
            } else if (self::is_associative_array($array[$key])) {
                $template[$key] = self::intersect_template($array[$key], $value);
            } else {
                $template[$key] = [];
                foreach ($array[$key] as $item) {
                    if (is_scalar($item)) {
                        $template[$key][] = $item;
                    } else {
                        $template[$key][] = self::intersect_template($item, $value);
                    }
                }

            }


        }
        if ($template == $original_template)
            return [];
        return $template;
    }


    private static function is_associative_array($arr) {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }


    // PRIVATE!!!!!!!!
    public static function team_loaded($championship_id, $season_id, $team_id) {
        foreach (self::clubs() as $club) {
            foreach ($club['championships'] as $championship) {
                if ($championship['championshipId'] != $championship_id) continue;
                if ($championship['seasonId'] != $season_id) continue;

                foreach ($championship['standings'] as $standing) {
                    if ($standing['teamId'] != $team_id) continue;
                    if ($standing['clubCode'] != $club['clubCode']) continue;
                    return true;
                }

            }

        }
        return false;
    }

    public static function team_status_to_int($team_status) {
        switch ($team_status) {
            case 'promotion':
                return 5;
            case 'playoff':
                return 4;
            default:
            case 'neutral':
                return 3;
            case 'playout':
                return 2;
            case 'relegation':
                return 1;
        }
    }

    public static function is_hidden($player_code): bool {
        return in_array($player_code, TEMP_HIDDEN_PLAYERS);
    }

    public static function belongs_to_club($player_code, $club_code) {
        foreach (self::clubs() as $club) {
            if (!empty($club_code) && $club['clubCode'] != $club_code) {
                continue;
            }
            foreach ($club['players'] as $player) {
                if ($player['playerCode'] == $player_code)
                    return true;
            }
        }
        return false;
    }

    /**
     * @param array $old_championships
     * @param array $championships
     * @return mixed
     */
    public static function merge_championships($old_championships, $championships) {

        if (!$championships) {
            $championships = [];
        }

        foreach ($championships as $championship) {
            $index = self::index_of_championship($old_championships, $championship['seasonId'], $championship['championshipId']);
            if ($index == -1) {
                $old_championships[] = $championship;
            } else {
                //$championship['standings'] = Fitet_Monitor_Utils::merge_standings($old_championships[$index]['standings'], $championship['standings']);
                $old_championships[$index] = $championship;
            }
        }
        usort($old_championships, function ($c1, $c2) {
            if ($c2['seasonId'] == $c1['seasonId']) {
                return $c2['championshipId'] - $c1['championshipId'];
            }
            return $c2['seasonId'] - $c1['seasonId'];
        });

        return $old_championships;
    }

    private static function index_of_championship($championships, $season_id, $championship_id) {
        for ($i = 0; $i < count($championships); $i++) {
            if ($championships[$i]['seasonId'] == $season_id && $championships[$i]['championshipId'] == $championship_id) {
                return $i;
            }
        }
        return -1;
    }

    public static function last_season_id(array $resources) {
        $resources = array_map(function ($club) {
            return $club['championships'];
        }, $resources);
        $resources = array_merge(...$resources);
        $resources = array_map(function ($championship) {
            return intval($championship['seasonId']);
        }, $resources);

        $resources = array_unique($resources);
        rsort($resources);
        $resources = $resources[0];
        return $resources;
    }

    public static function extract_seasons($resources) {

        $resources = array_values(array_filter($resources, function ($championship) {
            return !empty($championship['standings']);
        }));

        $resources = array_unique(array_map(function ($championship) {
            return ['seasonId' => $championship['seasonId'], 'seasonName' => $championship['seasonName']];
        }, $resources), SORT_REGULAR);

        usort($resources, function ($s1, $s2) {
            return strcmp($s2['seasonName'], $s1['seasonName']);
        });

        return $resources;
    }

    public static function is_owned_team_from_standings_by_team_id($standings, $team_id) {
        foreach ($standings as $standing) {
            if (($standing['teamId'] == $team_id) && !empty($standing['players'])) {
                return true;
            }
        }
        return false;
    }

    public static function is_retired_team_from_standings_by_team_id($standings, $team_id) {
        foreach ($standings as $standing) {
            if (($standing['teamId'] == $team_id)) {
                return $standing['retired'];
            }
        }
        return false;
    }

    public static function extract_club_code_from_standings_by_team_name($standings, $team_name) {

        foreach ($standings as $team) {
            if ($team['teamName'] == $team_name) {
                return $team['clubCode'];
            }
        }
        return '';
    }

    public static function extract_video($match) {
        $videos = [
            '292616' => ['url' => 'https://www.youtube.com/watch?v=2NNiqOFGVdY', 'thumbnail' => 'https://i.ytimg.com/vi/2NNiqOFGVdY/default.jpg'],
            '293168' => ['url' => 'https://www.youtube.com/watch?v=ESSdeXUEre8', 'thumbnail' => 'https://i.ytimg.com/vi/ESSdeXUEre8/default.jpg'],
            '292608' => ['url' => 'https://www.youtube.com/watch?v=KyR-dx-dv00', 'thumbnail' => 'https://i.ytimg.com/vi/KyR-dx-dv00/default.jpg'],
            '292604' => ['url' => 'https://www.youtube.com/watch?v=71mTSIWMBi0', 'thumbnail' => 'https://i.ytimg.com/vi/71mTSIWMBi0/default.jpg'],
            '291857' => ['url' => 'https://www.youtube.com/watch?v=d-FMlLdghz4', 'thumbnail' => 'https://i.ytimg.com/vi/d-FMlLdghz4/default.jpg'],
            '291855' => ['url' => 'https://www.youtube.com/watch?v=MU0uvhWkLcU', 'thumbnail' => 'https://i.ytimg.com/vi/MU0uvhWkLcU/default.jpg'],
            '292596' => ['url' => 'https://www.youtube.com/watch?v=1yJMe13xd4U', 'thumbnail' => 'https://i.ytimg.com/vi/1yJMe13xd4U/default.jpg'],
        ];

        if (isset($videos[$match])) {
            return $videos[$match];
        }
        return [];
    }

    public static function extract_team_id_from_standings_by_team_name($standings, $team_name) {

        foreach ($standings as $team) {
            if ($team['teamName'] == $team_name) {
                return $team['teamId'];
            }
        }
        return '';
    }

    public static function to_match_groups($data, $only_owned_teams, $field, $match_page_id) {
        $data = array_merge(...$data);

        foreach ($data as &$championship) {
            foreach ($championship['calendar'] as &$calendar) {
                foreach ($calendar as &$match) {
                    $match['firstLeg']['seasonId'] = $championship['seasonId'];
                    $match['firstLeg']['seasonName'] = $championship['seasonName'];
                    $match['firstLeg']['championshipId'] = $championship['championshipId'];
                    $match['firstLeg']['championshipName'] = $championship['championshipName'];
                    $match['firstLeg']['championshipDay'] = $match['championshipDay'];
                    $match['firstLeg']['championshipDayId'] = $match['championshipDay'] . '_' . 'firstLeg';
                    $match['firstLeg']['homeTeamName'] = $match['home'];
                    $match['firstLeg']['awayTeamName'] = $match['away'];
                    $match['firstLeg']['video'] = self::extract_video($match['firstLeg']['match']);
                    $match['firstLeg']['matchDetailUrl'] = "/index.php?page_id=$match_page_id&match=" . $match['firstLeg']['match'];
                    $match['firstLeg']['homeTeamId'] = self::extract_team_id_from_standings_by_team_name($championship['standings'], $match['firstLeg']['homeTeamName']);
                    $match['firstLeg']['awayTeamId'] = self::extract_team_id_from_standings_by_team_name($championship['standings'], $match['firstLeg']['awayTeamName']);
                    $match['firstLeg']['homeClubCode'] = self::extract_club_code_from_standings_by_team_name($championship['standings'], $match['firstLeg']['homeTeamName']);
                    $match['firstLeg']['awayClubCode'] = self::extract_club_code_from_standings_by_team_name($championship['standings'], $match['firstLeg']['awayTeamName']);
                    $match['firstLeg']['ownedHomeTeam'] = self::is_owned_team_from_standings_by_team_id($championship['standings'], $match['firstLeg']['homeTeamId']);
                    $match['firstLeg']['ownedAwayTeam'] = self::is_owned_team_from_standings_by_team_id($championship['standings'], $match['firstLeg']['awayTeamId']);
                    $match['firstLeg']['retiredHomeTeam'] = self::is_retired_team_from_standings_by_team_id($championship['standings'], $match['firstLeg']['homeTeamId']);
                    $match['firstLeg']['retiredAwayTeam'] = self::is_retired_team_from_standings_by_team_id($championship['standings'], $match['firstLeg']['awayTeamId']);
                    $match['firstLeg']['homeResult'] = trim(explode('-', $match['firstLeg']['result'])[0]);
                    $match['firstLeg']['awayResult'] = trim(explode('-', $match['firstLeg']['result'])[1]);
                    $match['firstLeg']['phase'] = 'firstLeg';

                    $match['returnMatch']['seasonId'] = $championship['seasonId'];
                    $match['returnMatch']['seasonName'] = $championship['seasonName'];
                    $match['returnMatch']['championshipId'] = $championship['championshipId'];
                    $match['returnMatch']['championshipName'] = $championship['championshipName'];
                    $match['returnMatch']['championshipDay'] = $match['championshipDay'];
                    $match['returnMatch']['championshipDayId'] = $match['championshipDay'] . '_' . 'returnMatch';
                    $match['returnMatch']['homeTeamName'] = $match['away'];
                    $match['returnMatch']['awayTeamName'] = $match['home'];
                    $match['returnMatch']['video'] = self::extract_video($match['returnMatch']['match']);
                    $match['returnMatch']['matchDetailUrl'] = "/index.php?page_id=$match_page_id&match=" . $match['returnMatch']['match'];
                    $match['returnMatch']['homeTeamId'] = self::extract_team_id_from_standings_by_team_name($championship['standings'], $match['returnMatch']['homeTeamName']);
                    $match['returnMatch']['awayTeamId'] = self::extract_team_id_from_standings_by_team_name($championship['standings'], $match['returnMatch']['awayTeamName']);
                    $match['returnMatch']['homeClubCode'] = self::extract_club_code_from_standings_by_team_name($championship['standings'], $match['returnMatch']['homeTeamName']);
                    $match['returnMatch']['awayClubCode'] = self::extract_club_code_from_standings_by_team_name($championship['standings'], $match['returnMatch']['awayTeamName']);
                    $match['returnMatch']['ownedHomeTeam'] = self::is_owned_team_from_standings_by_team_id($championship['standings'], $match['returnMatch']['homeTeamId']);
                    $match['returnMatch']['ownedAwayTeam'] = self::is_owned_team_from_standings_by_team_id($championship['standings'], $match['returnMatch']['awayTeamId']);
                    $match['returnMatch']['retiredHomeTeam'] = self::is_retired_team_from_standings_by_team_id($championship['standings'], $match['returnMatch']['homeTeamId']);
                    $match['returnMatch']['retiredAwayTeam'] = self::is_retired_team_from_standings_by_team_id($championship['standings'], $match['returnMatch']['awayTeamId']);
                    $match['returnMatch']['homeResult'] = self::parse_result($match['returnMatch']['result'])['away'];
                    $match['returnMatch']['awayResult'] = self::parse_result($match['returnMatch']['result'])['home'];
                    $match['returnMatch']['phase'] = 'returnMatch';

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

            $championship['calendar'] = is_string($championship['calendar']) ? json_decode($championship['calendar']) : $championship['calendar'];
            $championship['calendar'] = array_merge(...($championship['calendar']));

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


        if ($only_owned_teams) {
            $data = array_values(array_filter($data, function ($match) {
                return $match['ownedHomeTeam'] || $match['ownedAwayTeam'];
            }));
        }

        $data = array_values(array_filter($data, function ($match) {
            return !($match['retiredHomeTeam'] || $match['retiredAwayTeam']);
        }));

        foreach ($data as &$match) {
            $match['datetime'] = implode('-', array_reverse(explode('/', $match['date'])));
            $match['datetime'] .= '_' . str_replace('.', ':', $match['time']);
        }

        usort($data, function ($a, $b) {
            return strcmp($a['datetime'], $b['datetime']);
        });

        $data = self::group_matches($data, $field);


        $array_map = array_map(function ($group) {
            $matches = array_map(function ($match) {
                return [
                    'date' => $match['date'],
                    'time' => $match['time'],
                    'matchId' => $match['match'],
                    'seasonId' => $match['seasonId'],
                    'championshipId' => $match['championshipId'],
                    'championshipName' => $match['championshipName'],
                    'championshipDay' => $match['championshipDay'],
                    'championshipDayId' => $match['championshipDayId'],
                    'phase' => $match['phase'],
                    'formula' => $match['formula'],
                    'homeClubCode' => $match['homeClubCode'],
                    'homeTeamName' => $match['homeTeamName'],
                    'homeResult' => $match['homeResult'],
                    'ownedHomeTeam' => $match['ownedHomeTeam'],
                    'awayClubCode' => $match['awayClubCode'],
                    'awayTeamName' => $match['awayTeamName'],
                    'awayResult' => $match['awayResult'],
                    'ownedAwayTeam' => $match['ownedAwayTeam'],
                    'video' => $match['video'],
                    'matchDetailUrl' => $match['matchDetailUrl'],

                ];

            }, $group['matches']);

            return ['groupId' => $group['groupId'], 'matches' => $matches,];

        }, $data);


        foreach ($array_map as &$group) {
            if (isset($group['matches']) && is_array($group['matches'])) {
                usort($group['matches'], function($a, $b) {
                    return $a['championshipId'] <=> $b['championshipId'];
                });
            }
        }
        unset($group);

        return $array_map;
    }

    public static function parse_result($result) {
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

    /**
     * @param array $data
     * @return array
     */
    public static function group_matches($data, $field) {

        $result = [];
        foreach ($data as $element) {
            if (!isset($result[$element[$field]])) {
                $result[$element[$field]] = ['groupId' => $element[$field], 'matches' => []];
            }
            $result[$element[$field]]['matches'][] = $element;
        }
        return $result;
    }

    private static function index_of_player($players, $player_id) {
        for ($i = 0; $i < count($players); $i++) {
            if ($players[$i]['playerId'] == $player_id) {
                return $i;
            }
        }
        return -1;
    }

    private static function merge_standings($old_standings, $standings) {

        return $standings;

        // todo terminare
        if (!isset($old_championship['players'])) {
            $old_championship['players'] = [];
        }

        foreach ($championship['players'] as $player) {
            $index = self::index_of_player($old_championship, $player['playerId']);
            if ($index == -1) {
                $old_championship['players'][] = $player;
            } else {
                $old_championship['players'][$index] = $player;
                // $old_championships[$index] = $championship;
            }
        }
        usort($old_championship['players'], function ($c1, $c2) {
            return strcmp($c1, $c2);
        });

        return $old_championship;

    }

    public static function extract_recent_date($dates) {
        if (count($dates) == 0) {
            return "none";
        }

        return array_reduce($dates, function ($day1, $day2) {
            return Fitet_Monitor_Utils::seconds_diff_from_now($day1) < Fitet_Monitor_Utils::seconds_diff_from_now($day2) ? $day1 : $day2;
        });

    }

    private static function seconds_diff_from_now($day) {
        return abs(time() - strtotime(str_replace('/', '-', $day)));
    }


}
