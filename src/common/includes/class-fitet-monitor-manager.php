<?php

define('FITET_MONITOR_MB_CONVERT_ENCODING_EXIST', function_exists('mb_convert_encoding'));
define('FITET_MONITOR_ICONV_EXIST', function_exists('iconv'));
require_once FITET_MONITOR_DIR . 'public/utils/class-fitet-monitor-utils.php';

class Fitet_Monitor_Manager {

    private $plugin_name;
    private $version;
    /**
     * @var Fitet_Monitor_Manager_Logger
     */
    protected $logger;
    /**
     * @var Fitet_Portal_Rest
     */
    protected $portal;
    /**
     * @var Fitet_Monitor_Repository
     */
    protected $repository;
    private $empty_club = [
        'nationalTitles' => [],
        'regionalTitles' => [],
        'caps' => [],
        'players' => [],
        'championships' => [],
        'lastUpdate' => '',
        'lastClubUpdate' => '',
        'lastPlayersUpdate' => '',
        'lastChampionshipsUpdate' => '',
    ];


    /**
     * @param string $plugin_name
     * @param string $version
     * @param Fitet_Monitor_Manager_Logger $logger
     * @param Fitet_Portal_Rest $portal
     */
    public function __construct($plugin_name, $version, $logger, $portal, $repository) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->logger = $logger;
        $this->portal = $portal;
        $this->repository = $repository;
    }

    private static function group_by($array, ...$keys) {
        $result = [];
        foreach ($array as $element) {
            $key = implode('', array_map(function ($k) use ($element) {
                return $element[$k];
            }, $keys));
            $result[$key] = $element;
        }
        return $result;
    }


    public function add_club($club) {
        $this->repository->save_club_db($club);
        $this->logger->reset_status($club['clubCode']);
    }

    public function edit_club($club_update) {
        $club_code = $club_update['clubCode'];
        $club = $this->repository->get_club_from_db($club_code);
        $club['clubName'] = $club_update['clubName'];
        $club['clubProvince'] = $club_update['clubProvince'];
        $club['clubLogo'] = $club_update['clubLogo'];
        $club['clubCron'] = $club_update['clubCron'];
        $this->repository->save_club_db($club);
    }


    public function delete_clubs($club_codes) {
        if (!is_array($club_codes))
            $club_codes = [$club_codes];

        $this->repository->delete_clubs_db($club_codes);
        $this->remove_scheduled_cronjob_for_clubs($club_codes);
        do_action('fm_after_change');
    }

    public function get_club($club_code, $template = null) {
        $club = $this->repository->get_club_from_db($club_code);
        $club = array_merge($this->empty_club, $club);
        return Fitet_Monitor_Utils::intersect_template($club, $template);
    }

    public function club_exist($club_code) {
        $club_info = $this->portal->get_club_info($club_code);
        return $club_info['clubName'] != 'N/A';
    }

    public function get_match_results($matchId, $seasonId, $championshipId, $formula) {
        $match_results = get_transient($this->plugin_name . "_match_$matchId");
        if ($match_results) {
            return $match_results;
        }
        $match_results = $this->portal->get_match_results($matchId, $seasonId, $championshipId, $formula);

        set_transient($this->plugin_name . "_match_$matchId", $match_results, 60/*30 * 24 * 60 * 60*/);

        return $match_results;
    }

    public function get_clubs($template = null) {
        $club_codes = $this->repository->get_club_codes_db();
        if (!$club_codes) {
            return [];
        }
        return array_map(function ($club_code) use ($template) {
            return $this->get_club($club_code, $template);
        }, $club_codes);
    }

    public function find_clubs($club_name_contains) {
        return $this->portal->find_clubs($club_name_contains);
    }

    public function get_status($club_code) {
        return $this->logger->get_status($club_code);
    }


    public function resetStatus($clubCode) {
        $this->logger->reset_status($clubCode);
    }

    public function reset_season($club_code, $season_id) {

        // todo deve diventare reset championship con seasonId e championshipId

        if ($club_code == null)
            throw new Exception("Club code can not be null!");
        if ($season_id == null)
            throw new Exception("Season id can not be null!");

        $club = $this->get_club($club_code);

        if (!isset($club['championships']))
            return;


        for ($i = 0; $i < count($club['championships']); $i++) {
            if ($club['championships'][$i]['seasonId'] == $season_id) {
                unset($club['championships'][$i]);
            }
        }

        $this->repository->save_club_db($club);

    }

    public function export($club_code) {
        return json_encode($this->get_club($club_code), 128);

    }

    public function update($club_code, $mode = '', $season_id = null) {

        register_shutdown_function(function () use ($club_code) {
            $status = $this->logger->get_status($club_code);
            if ($status['status'] == 'updating')
                $this->logger->add_status($club_code, 'Fail: ' . "Timeout", 0, 'fail');
        });

        $status_log = $this->logger->get_status($club_code);

        if ($status_log['status'] != 'updating') {

            try {
                $this->logger->reset_status($club_code);

                switch ($mode) {
                    case 'full-history':
                        $this->full_championships_history($club_code);
                        break;

                    case 'club':
                        $this->update_club($club_code);
                        break;
                    case 'players':
                        $this->update_players($club_code);
                        break;
                    case 'championships':
                        $this->update_season_championships($club_code, $season_id);
                        break;
                    default:
                    case 'all':

                        $this->update_club($club_code);
                        $this->update_players($club_code);
                        $this->update_season_championships($club_code);

                        break;
                }
                $this->logger->set_completed($club_code, 'Done');
                do_action('fm_after_change');


            } catch (Exception $e) {
                $this->logger->add_status($club_code, 'Fail: ' . $e->getMessage(), 0, 'fail');
                error_log($e);
            } catch (Throwable $e) {
                error_log($e);
                $this->logger->add_status($club_code, 'Fail: ' . $e->getMessage(), 0, 'fail');
            }

        }

    }

    public function _update_season_championships($club_code, $season_id = null) {
        error_log("_update_season_championships $club_code");
        $this->logger->reset_status($club_code);
        $this->update_season_championships($club_code, $season_id);
        $this->logger->set_completed($club_code, 'Done');
        do_action('fm_after_change');
        error_log("_update_season_championships $club_code done");

    }

    public function update_season_championships($club_code, $season_id = null) {
        if ($club_code == null)
            throw new Exception("Club code can not be null!");

        $this->logger->add_status($club_code, 'Start updating');

        $this->logger->add_status($club_code, "Getting info for club $club_code", 0);

        $club = $this->get_club($club_code);

        $this->logger->add_status($club_code, "Getting details for club " . $club['clubCode'] . " " . $club['clubName'], 5);
        $club_details = $this->portal->get_club_details($club_code);

        // if season_id is null, use last available season
        if ($season_id == null) {
            $season_id = $club_details['championships'][0]['seasonId'];
        }

        $championships = array_values(array_filter($club_details['championships'], function ($championship) use ($season_id) {
            return $championship['seasonId'] == $season_id;
        }));

        for ($i = 0, $count = count($championships); $i < $count; $i++) {
            $championship = $championships[$i];
            $championship_id = $championship['championshipId'];
            $championship_name = $championship['championshipName'];
            $season_id = $championship['seasonId'];
            $season_name = $championship['seasonName'];
            $this->logger->add_status($club_code, "Getting standings (" . ($i + 1) . "/$count): $season_name - $championship_name", 8 / $count);
            $standings = $this->portal->get_championship_standings($championship_id, $season_id);
            $championships[$i]['standings'] = $standings;
        }

        $total_standings = array_sum(array_map(function ($c) {
            return count($c['standings']);
        }, $championships));
        $standing_cursor = 0;
        for ($i = 0, $count_i = count($championships); $i < $count_i; $i++) {
            $championship = $championships[$i];
            for ($j = 0, $count_j = count($championship['standings']); $j < $count_j; $j++) {
                $championship_id = $championship['championshipId'];
                $championship_name = $championship['championshipName'];
                $season_id = $championship['seasonId'];
                $season_name = $championship['seasonName'];

                $team_id = $championship['standings'][$j]['teamId'];
                $team_name = $championship['standings'][$j]['teamName'];
                $this->logger->add_status($club_code, "Getting team info (" . ++$standing_cursor . "/$total_standings): $season_name - $championship_name - $team_name", 18 / $total_standings);
                $team_info = $this->portal->get_team_info($team_id, $championship_id, $season_id);
                $championships[$i]['standings'][$j] = array_merge($championship['standings'][$j], $team_info);
            }
        }

        $standing_cursor = 0;
        $total_standings = array_sum(array_map(function ($c) use ($club_code) {
            return count(array_values(array_filter($c['standings'], function ($s) use ($club_code) {
                return $club_code == $s['clubCode'];
            })));
        }, $championships));

        for ($i = 0, $_count = count($championships); $i < $_count; $i++) {
            $championship = $championships[$i];
            for ($j = 0, $count = count($championship['standings']); $j < $count; $j++) {
                $standing = $championship['standings'][$j];
                if ($club_code != $standing['clubCode']) {
                    continue;
                }
                $championship_id = $championship['championshipId'];
                $championship_name = $championship['championshipName'];
                $season_id = $championship['seasonId'];
                $season_name = $championship['seasonName'];

                $team_id = $standing['teamId'];
                $team_name = $standing['teamName'];
                $this->logger->add_status($club_code, "Getting team details (" . ++$standing_cursor . "/$total_standings): $season_name - $championship_name - $team_name", 8 / $total_standings);
                $team_details = $this->portal->get_team_details($team_id, $championship_id, $season_id);
                $championships[$i]['standings'][$j] = array_merge($standing, $team_details);
            }
        }

        for ($i = 0, $count = count($championships); $i < $count; $i++) {
            $championship = $championships[$i];
            $championship_id = $championship['championshipId'];
            $championship_name = $championship['championshipName'];
            $season_id = $championship['seasonId'];
            $season_name = $championship['seasonName'];

            $this->logger->add_status($club_code, "Getting calendar (" . ($i + 1) . "/$count): $season_name - $championship_name", 8 / $count);

            if ($championship_id == 85 && $season_id == 31) {
                $standings = $this->fixed_85_31();
            } else if ($championship_id == 44 && $season_id == 36) {
                $standings = $this->fixed_44_36();
            } else {
                $standings = $this->portal->get_championship_calendar($championship_id, $season_id);
            }
            $championships[$i]['calendar'] = $standings;
        }

        for ($i = 0; $i < count($championships); $i++) {
            if (!isset($championship['seasonId'])) {
                unset($championships[$i]);
            }
        }


        $club['championships'] = isset($club['championships']) ? $club['championships'] : [];
        $club_details['championships'] = $this->add_empty_standings($club_details['championships']);
        $club['championships'] = Fitet_Monitor_Utils::merge_championships($club_details['championships'], $club['championships']);
        $club['championships'] = Fitet_Monitor_Utils::merge_championships($club['championships'], $championships);

        $last_update = new DateTime("now", new DateTimeZone('Europe/Rome')); //first argument "must" be a string
        $last_update->setTimestamp(time()); //adjust the object to correct timestamp
        $club['lastUpdate'] = $last_update->format('d/m/Y H:i:s');
        $club['lastChampionshipsUpdate'] = $last_update->format('d/m/Y H:i:s');

        $club = $this->all_to_utf8($club);

        $this->repository->save_club_db($club);


    }

    public function _update_players($club_code) {
        error_log("_update_players $club_code");
        $this->logger->reset_status($club_code);
        $this->update_players($club_code);
        $this->logger->set_completed($club_code, 'Done');
        do_action('fm_after_change');
        error_log("_update_players $club_code done");

    }

    public function reset_players_ranking_id($club_code) {
        $club = $this->get_club($club_code);

        foreach ($club['players'] as &$player) {
            $player['rankingId'] = 0;
        }

        $club = $this->all_to_utf8($club);

        $this->repository->save_club_db($club);
    }

    public function update_players($club_code) {
        if ($club_code == null)
            throw new Exception("Club code can not be null!");

        $this->logger->add_status($club_code, 'Start updating');

        $this->logger->add_status($club_code, "Getting info for club $club_code", 0);

        $club = $this->get_club($club_code);

        $this->logger->add_status($club_code, "Getting ranking list", 5);

        $last_ranking = $this->portal->find_rankings()[0];

        $total_rankings = count(Fitet_Portal_Rest::$ranking_types) * count(Fitet_Portal_Rest::$ranking_sex);
        $players = $this->get_club($club_code, ['players' => ''])['players'];

        $min_rid = count($players) == 0 ? 0 : min(array_map(function ($p) {
            return $p['rankingId'];
        }, $players));
        $max_rid = count($players) == 0 ? 0 : max(array_map(function ($p) {
            return $p['rankingId'];
        }, $players));
        $last_rid = $last_ranking['rankingId'];
        $no_pending = $min_rid == $max_rid;// array_search($last_ranking['rankingId'], array_column($players, 'rankingId'));
        $update_available = $min_rid != $last_rid;

        if ($no_pending && $update_available) {
            $new_players = [];
            $standing_cursor = 0;
            for ($i = 0, $count_i = count(Fitet_Portal_Rest::$ranking_types); $i < $count_i; $i++) {
                $type = Fitet_Portal_Rest::$ranking_types[$i];
                for ($j = 0, $count_j = count(Fitet_Portal_Rest::$ranking_sex); $j < $count_j; $j++) {
                    $sex = Fitet_Portal_Rest::$ranking_sex[$j];
                    $type_name = $type['name'];
                    $sex_name = $sex['name'];
                    $date = $last_ranking['date'];
                    $this->logger->add_status($club_code, "Getting ranking (" . ++$standing_cursor . "/$total_rankings): $date - $type_name - $sex_name", 8 / $total_rankings);
                    $new_players[] = $this->portal->get_ranking($last_ranking['rankingId'], $sex, $type, $club_code);
                }
            }
            $new_players = array_merge(...$new_players);

            foreach ($new_players as &$px) {
                $px['rankingId'] = 0;
            }

            // $new_players = self::group_by('playerCode',$new_players);
            $players = self::group_by($players, 'playerName', 'birthDate', 'typeId'); // qui servirebbe il code

            foreach ($new_players as &$new_player) {
                $key = $new_player['playerName'] . $new_player['birthDate'] . $new_player['typeId'];
                if (isset($players[$key])) {
                    $players[$key]['rank'] = $new_player['rank'];
                    $players[$key]['points'] = $new_player['points'];
                    $players[$key]['category'] = $new_player['category'];
                    $players[$key]['sector'] = $new_player['sector'];
                    $players[$key]['diff'] = $new_player['diff'];
                    $players[$key]['region'] = $new_player['region'];
                    $players[$key]['clubCode'] = $new_player['clubCode'];
                    $players[$key]['clubName'] = $new_player['clubName'];
                    $players[$key]['sex'] = $new_player['sex'];
                    $players[$key]['type'] = $new_player['type'];
                    $new_player = $players[$key];
                }
            }
            $players = $new_players;


            usort($players, function ($a, $b) {
                return $b['points'] - $a['points'];
            });
            usort($players, function ($a, $b) {
                return $a['typeId'] - $b['typeId'];
            });


            $club['players'] = $players;
            $club = $this->all_to_utf8($club);
            $this->repository->save_club_db($club);

        }

        for ($i = 0, $count = count($players); $i < $count; $i++) {
            $player = $players[$i];

            if ($player['rankingId'] == $last_ranking['rankingId']) {
                $player_name = $player['playerName'];
                $player_code = $player['playerCode'];
                $this->logger->add_status($club_code, "Getting player info (" . ($i + 1) . "/$count): $player_name", 20 / $count);
                $this->logger->add_status($club_code, "Getting player season (" . ($i + 1) . "/$count): $player_name - $player_code", 10 / $count);
                $this->logger->add_status($club_code, "Getting player history (" . ($i + 1) . "/$count): $player_name - $player_code", 10 / $count);

                continue;
            }
            $player_name = $player['playerName'];
            $this->logger->add_status($club_code, "Getting player info (" . ($i + 1) . "/$count): $player_name", 20 / $count);

            $player_infos = $this->portal->find_players($player['playerName'], $player['birthDate']);
            $player_info = $player_infos[0];
            if (count($player_infos) > 1) {
                foreach ($player_infos as $info) {
                    $ranking = $this->portal->get_player_history($info['playerId'])['ranking'];
                    if (empty($ranking)) {
                        continue;
                    }
                    if ($ranking[0]['position'] == $player['rank']) {
                        $player_info = $info;
                        break;
                    }
                }
            }
            $players[$i] = array_merge($player, $player_info);

            $player = $players[$i];
            $player_name = $player['playerName'];
            $player_code = $player['playerCode'];
            $this->logger->add_status($club_code, "Getting player season (" . ($i + 1) . "/$count): $player_name - $player_code", 10 / $count);
            $players[$i]['season'] = $this->portal->get_player_season($player['playerId'], $last_ranking['rankingId']);


            $player = $players[$i];
            $player_name = $player['playerName'];
            $player_code = $player['playerCode'];
            $this->logger->add_status($club_code, "Getting player history (" . ($i + 1) . "/$count): $player_name - $player_code", 10 / $count);
            $players[$i]['history'] = $this->portal->get_player_history($player['playerId']);

            $players[$i]['best'] = self::calculate_best_ranking(isset($players[$i]['history']) ? $players[$i]['history']['ranking'] : []);


            $players[$i]['rankingId'] = $last_ranking['rankingId'];
            if ($i % 10 == 0) {
                // todo group by and merge
                // todo resume update timeout
                // todo composer
                // todo ita for show more
                $club['players'] = $players;
                $club = $this->all_to_utf8($club);
                $this->repository->save_club_db($club);
            }

        }

        $club['players'] = $players;

        $last_update = new DateTime("now", new DateTimeZone('Europe/Rome')); //first argument "must" be a string
        $last_update->setTimestamp(time()); //adjust the object to correct timestamp
        $club['lastUpdate'] = $last_update->format('d/m/Y H:i:s');
        $club['lastPlayersUpdate'] = $last_update->format('d/m/Y H:i:s');

        $club = $this->all_to_utf8($club);

        $this->repository->save_club_db($club);


    }

    public function _update_club($club_code) {
        error_log("_update_club $club_code");
        $this->logger->reset_status($club_code);
        $this->update_club($club_code);
        $this->logger->set_completed($club_code, 'Done');
        do_action('fm_after_change');
        error_log("_update_club $club_code done");
    }

    public function update_club($club_code) {
        if ($club_code == null)
            throw new Exception("Club code can not be null!");

        $this->logger->add_status($club_code, "Start updating club $club_code");

        $club = $this->get_club($club_code);

        $this->logger->add_status($club_code, "Getting details for club " . $club['clubCode'] . " " . $club['clubName'], 5);
        $club_details = $this->portal->get_club_details($club_code, 0);

        usort($club_details['nationalTitles'], [$this, 'sort_titles']);
        usort($club_details['regionalTitles'], [$this, 'sort_titles']);

        $club['nationalTitles'] = $club_details['nationalTitles'];
        $club['regionalTitles'] = $club_details['regionalTitles'];
        $club['caps'] = $club_details['caps'];
        $club_details['championships'] = $this->add_empty_standings($club_details['championships']);

        $club['championships'] = Fitet_Monitor_Utils::merge_championships($club_details['championships'], $club['championships']);

        $last_update = new DateTime("now", new DateTimeZone('Europe/Rome')); //first argument "must" be a string
        $last_update->setTimestamp(time()); //adjust the object to correct timestamp
        $club['lastUpdate'] = $last_update->format('d/m/Y H:i:s');
        $club['lastClubUpdate'] = $last_update->format('d/m/Y H:i:s');

        $club = $this->all_to_utf8($club);

        $this->repository->save_club_db($club);
    }

    private static function calculate_best_ranking($rankings) {
        if (empty($rankings)) {
            return null;
        }

        $rankings = array_map(function ($ranking) {
            if (empty($ranking['position'])) {
                return null;
            } else {
                return ['position' => $ranking['position'], 'date' => $ranking['date']];
            }
        }, $rankings);

        $rankings = array_values(array_filter($rankings, function ($ranking) {
            return $ranking != null;
        }));
        usort($rankings, function ($r1, $r2) {
            return intval($r1['position']) - intval($r2['position']);

        });
        return isset($rankings[0]) ? $rankings[0] : null;
    }

    public static function to_utf8($text) {
        if (FITET_MONITOR_MB_CONVERT_ENCODING_EXIST) {
            return mb_convert_encoding($text, "UTF-8", "ISO-8859-15");
        }
        if (FITET_MONITOR_ICONV_EXIST) {
            return iconv("ISO-8859-15", "UTF-8", $text);
        }
        return utf8_encode($text);

    }

    public function get_club_cron_jobs($club_code) {

        $cron = $this->get_club($club_code, ['cron' => '']);

        if (empty($cron)) {
            $hour = 60 * 60;
            $interval_label = 'daily';
            $interval = wp_get_schedules()[$interval_label]['interval'];
            $time = time();
            $time = $interval * (1 + floor($time / $interval));

            return
                [
                    'clubInterval' => $interval_label,
                    'playersInterval' => $interval_label,
                    'championshipsInterval' => $interval_label,
                    'clubTime' => $time + $hour * 1,
                    'playersTime' => $time + $hour * 2,
                    'championshipsTime' => $time + $hour * 3,
                ];
        }

        return $cron;

    }

    public function club_already_stored($club_code) {
        return in_array($club_code, $this->repository->get_club_codes_db());
    }

    /**
     * @param $championships
     * @return array
     */
    public function add_empty_standings($championships) {
        return array_map(function ($championship) {
            if (!isset($championship['standings'])) {
                $championship['standings'] = [];
            }
            return $championship;
        }, $championships);
    }

    public function schedule_cronjob() {
        add_filter('cron_schedules', function ($schedules) {
            $schedules['fitet_monitor_dev_interval'] = [
                'interval' => 600,
                'display' => esc_html__('Every Five Minutes', 'fitet-monitor'),];
            return $schedules;
        });

        $club_codes = $this->repository->get_club_codes_db();

        foreach ($club_codes as $club_code) {
            $cron_job = [];
            if (!wp_next_scheduled('fm_cron_update_club_hook', [$club_code])) {
                $cron_job = empty ($cron_job) ? $this->get_club_cron_jobs($club_code) : $cron_job;
                wp_schedule_event($cron_job['clubTime'], $cron_job['clubInterval'], 'fm_cron_update_club_hook', [$club_code]);
            }

            if (!wp_next_scheduled('fm_cron_update_players_hook', [$club_code])) {
                $cron_job = empty ($cron_job) ? $this->get_club_cron_jobs($club_code) : $cron_job;
                wp_schedule_event($cron_job['playersTime'], $cron_job['playersInterval'], 'fm_cron_update_players_hook', [$club_code]);
            }

            if (!wp_next_scheduled('fm_cron_update_championships_hook', [$club_code])) {
                $cron_job = empty ($cron_job) ? $this->get_club_cron_jobs($club_code) : $cron_job;
                wp_schedule_event($cron_job['championshipsTime'], $cron_job['championshipsInterval'], 'fm_cron_update_championships_hook', [$club_code]);
            }

            add_action('fm_cron_update_club_hook', [$this, '_update_club']);
            add_action('fm_cron_update_players_hook', [$this, '_update_players']);
            add_action('fm_cron_update_championships_hook', [$this, '_update_season_championships']);


        }
    }

    private function remove_scheduled_cronjob_for_clubs($club_codes): void {
        foreach ($club_codes as $club_code) {
            wp_clear_scheduled_hook('fm_cron_update_club_hook', [$club_code]);
            wp_clear_scheduled_hook('fm_cron_update_players_hook', [$club_code]);
            wp_clear_scheduled_hook('fm_cron_update_championships_hook', [$club_code]);
        }
    }


    private function sort_titles($a, $b): int {
        foreach (['season', 'tournament', 'competition', 'player'] as $field) {
            if ($a[$field] != $b[$field]) {
                return strcmp($b[$field], $a[$field]);
            }
        }
        return 0;
    }

    public function all_to_utf8($object) {
        if (is_string($object)) {
            if (empty(json_encode($object))) {
                $to_utf8 = Fitet_Monitor_Manager::to_utf8($object);
                error_log("not encodable  => $to_utf8");
                return $to_utf8;
            }
        }
        if (is_array($object) || is_object($object)) {
            $object = (array)$object;
            foreach (array_keys($object) as $array_key) {
                $object[$array_key] = $this->all_to_utf8($object[$array_key]);
            }
        }
        return $object;

    }

    private function full_championships_history($club_code, $home_teams_only = false) {
        if ($club_code == null)
            throw new Exception("Club code can not be null!");

        $this->logger->add_status($club_code, 'Start updating full championships history');

        $this->logger->add_status($club_code, "Getting info for club $club_code", 0);

        $club = $this->get_club($club_code);
        $this->logger->add_status($club_code, "Getting details for club " . $club['clubCode'] . " " . $club['clubName'], 5);

        $championships = $this->portal->get_club_details($club_code)['championships'];

        for ($i = 0, $count = count($championships); $i < $count; $i++) {
            $championship = $championships[$i];
            $championship_id = $championship['championshipId'];
            $championship_name = $championship['championshipName'];
            $season_id = $championship['seasonId'];
            $season_name = $championship['seasonName'];
            $this->logger->add_status($club_code, "Getting standings (" . ($i + 1) . "/$count): $season_name - $championship_name", 15 / $count);
            $standings = $this->portal->get_championship_standings($championship_id, $season_id);
            $championships[$i]['standings'] = $standings;
        }

        $total_standings = array_sum(array_map(function ($c) {
            return count($c['standings']);
        }, $championships));
        $standing_cursor = 0;
        for ($i = 0, $count_i = count($championships); $i < $count_i; $i++) {
            $championship = $championships[$i];
            for ($j = 0, $count_j = count($championship['standings']); $j < $count_j; $j++) {
                $championship_id = $championship['championshipId'];
                $championship_name = $championship['championshipName'];
                $season_id = $championship['seasonId'];
                $season_name = $championship['seasonName'];

                $team_id = $championship['standings'][$j]['teamId'];
                $team_name = $championship['standings'][$j]['teamName'];
                $this->logger->add_status($club_code, "Getting team info (" . ++$standing_cursor . "/$total_standings): $season_name - $championship_name - $team_name", 50 / $total_standings);
                $team_info = $this->portal->get_team_info($team_id, $championship_id, $season_id);
                $championships[$i]['standings'][$j] = array_merge($championship['standings'][$j], $team_info);
            }
        }

        $standing_cursor = 0;
        $total_standings = array_sum(array_map(function ($c) use ($club_code) {
            return count(array_values(array_filter($c['standings'], function ($s) use ($club_code) {
                return $club_code == $s['clubCode'];
            })));
        }, $championships));

        for ($i = 0, $_count = count($championships); $i < $_count; $i++) {
            $championship = $championships[$i];
            for ($j = 0, $count = count($championship['standings']); $j < $count; $j++) {
                $standing = $championship['standings'][$j];
                if ($club_code != $standing['clubCode']) {
                    continue;
                }
                $championship_id = $championship['championshipId'];
                $championship_name = $championship['championshipName'];
                $season_id = $championship['seasonId'];
                $season_name = $championship['seasonName'];

                $team_id = $standing['teamId'];
                $team_name = $standing['teamName'];
                $this->logger->add_status($club_code, "Getting team details (" . ++$standing_cursor . "/$total_standings): $season_name - $championship_name - $team_name", 15 / $total_standings);
                $team_details = $this->portal->get_team_details($team_id, $championship_id, $season_id);
                $championships[$i]['standings'][$j] = array_merge($standing, $team_details);
            }
        }

        for ($i = 0, $count = count($championships); $i < $count; $i++) {
            $championship = $championships[$i];
            $championship_id = $championship['championshipId'];
            $championship_name = $championship['championshipName'];
            $season_id = $championship['seasonId'];
            $season_name = $championship['seasonName'];
            $team_names = [];
            if ($home_teams_only) {
                $team_names = array_values(array_filter($championship['standings'], function ($standing) use ($club_code) {
                    return $standing['clubCode'] == $club_code;
                }));
                $team_names = array_map(function ($standing) use ($club_code) {
                    return $standing['teamName'];
                }, $team_names);
            }
            $this->logger->add_status($club_code, "Getting calendar (" . ($i + 1) . "/$count): $season_name - $championship_name", 15 / $count);


            if ($championship_id == 85 && $season_id == 31) {
                $standings = $this->fixed_85_31();
            } else if ($championship_id == 44 && $season_id == 36) {
                $standings = $this->fixed_44_36();
            } else {
                $standings = $this->portal->get_championship_calendar($championship_id, $season_id, $team_names);
            }
            $championships[$i]['calendar'] = $standings;
        }

        $championships = $this->all_to_utf8($championships);
        $club['championships'] = $championships;

        $last_update = new DateTime("now", new DateTimeZone('Europe/Rome'));
        $last_update->setTimestamp(time());
        $club['lastUpdate'] = $last_update->format('d/m/Y H:i:s');

        $this->repository->save_club_db($club);
    }

    private function fixed_44_36() {
        return json_decode(file_get_contents(__DIR__ . '/44-36.json'));
    }

    private function fixed_85_31() {
        return json_decode(file_get_contents(__DIR__ . '/85-31.json'));
    }


}
