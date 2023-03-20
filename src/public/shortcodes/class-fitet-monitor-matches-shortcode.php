<?php

require_once FITET_MONITOR_DIR . 'public/includes/class-fitet-monitor-shortcode.php';
require_once FITET_MONITOR_DIR . 'public/components/match-list/class-fitet-monitor-match-list-component.php';
require_once FITET_MONITOR_DIR . 'public/components/match-detail/class-fitet-monitor-match-detail-component.php';


class Fitet_Monitor_Matches_Shortcode extends Fitet_Monitor_Shortcode {

    /**
     * @var Fitet_Monitor_Manager
     */
    private $manager;

    public function __construct($version, $plugin_name, $manager) {
        parent::__construct($version, $plugin_name, 'fitet-monitor-matches');
        $this->manager = $manager;
    }

    public function attributes(): array {
        return ['match', 'mode', 'season', 'club', 'filter', 'teams-page-id', 'players-page-id'];
    }


    private function to_player($players, $player_name, $player_page_id) {

        $player = $this->extract_team_players_by_name($players, $player_name);
        $player_code = Fitet_Monitor_Utils::player_code_by_id($player['playerId']);
        $player = [
            'playerId' => $player['playerId'],
            'playerName' => $player['playerName'],
            'playerUrl' => Fitet_Monitor_Utils::player_page_url("index.php?page_id=$player_page_id", $player_code, $player['playerName']),
            'playerImageUrl' => Fitet_Monitor_Utils::player_image_url($player['playerId']),
        ];

        return $player;

    }


    private function sum_at_position($match_results, $position) {
        return array_reduce($match_results, function ($acc, $r) use ($position) {
            return $acc + $r[$position];
        }, 0);
    }


    protected function process_attributes($attributes) {

        if (!empty($attributes['match'])) {
            return ['mode' => 'single', 'data' => $this->single($attributes)];
        }

        return ['mode' => 'list', 'data' => $this->list($attributes)];
    }

    public function wrapped_component($mode) {
        switch ($mode) {
            case 'single':
                return new Fitet_Monitor_Match_Detail_Component($this->plugin_name, $this->version);
            default:
                return new Fitet_Monitor_Match_List_Component($this->plugin_name, $this->version);
        }
    }

    private function single($attributes) {
        $template = ['championships' => []];
        $multi_club = empty($attributes['club']);
        if ($multi_club) {
            // no club found - keeping all
            $resources = $this->manager->get_clubs($template);
        } else {
            $resources = [$this->manager->get_club($attributes['club'], $template)];
        }

        $data = $this->extract_match($resources, $attributes['match'], $attributes['teams-page-id']);

        $home_team_players = $this->extract_team_players($resources, $data['seasonId'], $data['championshipId'], $data['homeTeamId']);
        $away_team_players = $this->extract_team_players($resources, $data['seasonId'], $data['championshipId'], $data['awayTeamId']);

        $data['hasResults'] = empty($data['results']);
        $data['results'] = $this->to_results_table_data($data, $home_team_players, $away_team_players, $attributes['players-page-id']);

        return $data;
    }

    public function to_results_table_data($_data, $home_team_players, $away_team_players, $player_page_id) {

        $data = [];
        $data['homeTeamName'] = $_data['homeTeamName'];
        $data['awayTeamName'] = $_data['awayTeamName'];
        $data['maxSetCount'] = $this->calculate_max_set_count($_data['results']);

        $data['results'] = array_map(function ($result, $i) use ($home_team_players, $away_team_players, $player_page_id) {
            return [
                'matchPosition' => $i + 1,
                'homePlayer' => $this->to_player($home_team_players, $result[0], $player_page_id),
                'awayPlayer' => $this->to_player($away_team_players, $result[1], $player_page_id),
                'sets' => $this->to_sets($result),
                'homePlayerSets' => $result[count($result) - 4],
                'awayPlayerSets' => $result[count($result) - 3],
            ];
        }, $_data['results'], array_keys($_data['results']));

        self::try_parse_double('homePlayer', $data['results']);
        self::try_parse_double('awayPlayer', $data['results']);

        return $data;
    }


    private function calculate_max_set_count($results) {
        $array_map = array_map(function ($result) {
            return count($result) - 6;
        }, $results);
        $array_map[] = 5;
        return max($array_map) / 2;
    }

    private function to_sets($result) {
        $result = array_slice($result, 2, count($result) - 6);
        return array_map(function ($i) use ($result) {
            return [
                'home' => $result[($i * 2)],
                'away' => $result[($i * 2) + 1],
            ];
        }, range(0, count($result) / 2 - 1));
    }


    private function list($attributes) {
        $template = ['championships' => []];
        $multi_club = empty($attributes['club']);
        if ($multi_club) {
            // no club found - keeping all
            $resources = $this->manager->get_clubs($template);
        } else {
            $resources = [$this->manager->get_club($attributes['club'], $template)];
        }

        $last_season_id = Fitet_Monitor_Utils::last_season_id($resources);
        if (empty($attributes['season'])) {
            $attributes['season'] = $last_season_id;
        }

        $resources = array_values(array_filter($resources, function ($club) {
            return !empty($club);
        }));

        $seasons = Fitet_Monitor_Utils::extract_seasons(Fitet_Monitor_Teams_Shortcode::extract_championships($resources));

        $season_id = $attributes['season'];

        foreach ($resources as &$club) {
            $club = array_values(array_filter($club['championships'], function ($championship) use ($season_id) {
                return $championship['seasonId'] == $season_id;
            }));
        }

        $data = array_values(array_filter($resources, function ($season) {
            return !empty($season);
        }));

        global $post;
        $data = Fitet_Monitor_Utils::to_match_groups($data, true, 'date', $post->ID);
        return [
            'scrollToRecent' => $last_season_id == $season_id,
            'groups' => $data, 'seasonId' => $season_id, 'seasons' => $seasons
        ];

    }

    private function extract_match($clubs, $match_id, $team_page_id) {

        foreach ($clubs as $club) {
            foreach ($club['championships'] as $championship) {
                if (empty($championship['calendar']))
                    continue;
                foreach ($championship['calendar'] as $matches) {
                    foreach ($matches as $match) {
                        foreach (['firstLeg', 'returnMatch'] as $phase) {
                            if ($match[$phase]['match'] == $match_id) {
                                $home_team_name = $phase == 'firstLeg' ? $match['home'] : $match['away'];
                                $away_team_name = $phase == 'firstLeg' ? $match['away'] : $match['home'];
                                $home_team_id = Fitet_Monitor_Utils::extract_team_id_from_standings_by_team_name($championship['standings'], $home_team_name);
                                $away_team_id = Fitet_Monitor_Utils::extract_team_id_from_standings_by_team_name($championship['standings'], $away_team_name);
                                $match_results = $this->manager->get_match_results($match_id, $championship['seasonId'], $championship['championshipId'], $match[$phase]['formula']);


                                $home_club_code = Fitet_Monitor_Utils::extract_club_code_from_standings_by_team_name($championship['standings'], $home_team_name);
                                $away_club_code = Fitet_Monitor_Utils::extract_club_code_from_standings_by_team_name($championship['standings'], $away_team_name);
                                $owned_home_team = Fitet_Monitor_Utils::is_owned_team_from_standings_by_team_id($championship['standings'], $home_team_id);
                                $owned_away_team = Fitet_Monitor_Utils::is_owned_team_from_standings_by_team_id($championship['standings'], $away_team_id);
                                return
                                    [
                                        'date' => $match[$phase]['date'],
                                        'time' => $match[$phase]['time'],
                                        'matchId' => $match_id,
                                        'seasonId' => $championship['seasonId'],
                                        'championshipId' => $championship['championshipId'],
                                        'championshipDay' => $match['championshipDay'],
                                        'championshipName' => $championship['championshipName'],
                                        'formula' => $match[$phase]['formula'],
                                        'homeTeamName' => $home_team_name,
                                        'awayTeamName' => $away_team_name,
                                        'video' => Fitet_Monitor_Utils::extract_video($match_id),
                                        'homeTeamId' => $home_team_id,
                                        'awayTeamId' => $away_team_id,
                                        'homeClubCode' => $home_club_code,
                                        'awayClubCode' => $away_club_code,
                                        'ownedHomeTeam' => $owned_home_team,
                                        'ownedAwayTeam' => $owned_away_team,
                                        'homeResult' => trim(explode('-', $match[$phase]['result'])[$phase == 'firstLeg' ? 0 : 1]),
                                        'awayResult' => trim(explode('-', $match[$phase]['result'])[$phase == 'firstLeg' ? 1 : 0]),
                                        'results' => $match_results,
                                        'homeSets' => $this->sum_at_position($match_results, 12),
                                        'awaySets' => $this->sum_at_position($match_results, 13),
                                        'homePoints' => $this->sum_at_position($match_results, 14),
                                        'awayPoints' => $this->sum_at_position($match_results, 15),
                                        'phase' => $phase,
                                        'homeClubLogo' => Fitet_Monitor_Utils::club_logo_by_code($home_club_code),
                                        'awayClubLogo' => Fitet_Monitor_Utils::club_logo_by_code($away_club_code),
                                        'homeTeamPageUrl' => $this->to_team_page_url($championship['championshipId'], $championship['seasonId'], $home_team_id, $owned_home_team, $team_page_id),
                                        'awayTeamPageUrl' => $this->to_team_page_url($championship['championshipId'], $championship['seasonId'], $away_team_id, $owned_away_team, $team_page_id),
                                    ];
                            }
                        }

                    }
                }
            }
        }
        return [];
    }

    private function to_team_page_url($championship_id, $season_id, $team_id, $owned_team, $page_id) {
        $loaded = Fitet_Monitor_Utils::team_loaded($championship_id, $season_id, $team_id);
        if ($loaded && $owned_team) {
            return "index.php?page_id=$page_id&championship=$championship_id&season=$season_id&team=$team_id";
        } else {
            return '';
        }
    }

    private function extract_team_players($resources, $season_id, $championship_id, $team_id) {
        foreach ($resources as $resource) {
            foreach ($resource['championships'] as $championship) {
                if ($championship['seasonId'] == $season_id) {
                    if ($championship['championshipId'] == $championship_id) {
                        foreach ($championship['standings'] as $standing) {
                            if ($standing['teamId'] == $team_id) {
                                return isset($standing['players']) ? $standing['players'] : [];
                            }
                        }
                    }
                }
            }
        }
        return [];
    }

    private function extract_team_players_by_name($players, $player_name) {
        foreach ($players as $player) {
            if ($player['playerName'] == $player_name) {
                return $player;
            }
        }
        return ['playerName' => $player_name];
    }

    public static function try_parse_double_inner($players, $player) {
        if (!strpos($player['playerName'], '-'))
            return $player;
        $player_names = explode('-', $player['playerName']);

        if (count($player_names) != 2)
            return $player;

        $players = array_filter($players, function ($p) use ($player) {
            return $p['playerName'] != $player['playerName'];
        });
        $player_1 = self::extract_player_by_prefix($players, trim($player_names[0]));
        $player_2 = self::extract_player_by_prefix($players, trim($player_names[1]));

        if ($player_1 == null && $player_2 == null)
            return $player;

        if ($player_1 == null) $player_1 = ['playerName' => trim($player_names[0])];
        if ($player_2 == null) $player_2 = ['playerName' => trim($player_names[1])];


        return [$player_1, $player_2];

    }

    private static function extract_player_by_prefix($players, $name) {
        foreach ($players as $player) {
            if (substr($player['playerName'], 0, strlen($name)) === $name) {
                return $player;
            }
        }
        return null;
    }

    private static function try_parse_double($team, &$results) {
        $home_players = array_map(function ($match) use ($team) {
            return $match[$team];
        }, $results);

        foreach ($results as &$result) {
            $result[$team] = self::try_parse_double_inner($home_players, $result[$team]);
        }
    }


}



