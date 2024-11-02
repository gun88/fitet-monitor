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
    public $logger;
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
        $this->repository->delete_players_db($club_codes);
        $this->repository->delete_championships_db($club_codes);
        /*todo */
        // todo cancella championships by club
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


        $this->repository->reset_championship($season_id);

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
            if (!isset($championships[$i]['seasonId'])) {
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

        foreach ($club['championships'] as &$championship) {
            $championship['id'] = $championship['seasonId'] * 100000 + $championship['championshipId'];
            $championship['lastUpdate'] = $last_update->format('d/m/Y H:i:s');
        }


        $championships = array_map(function ($championship) use ($club_code) {
            return [
                'id' => $championship['id'],
                'season_id' => $championship['seasonId'],
                'championship_id' => $championship['championshipId'],
                'season_name' => $championship['seasonName'],
                'championship_name' => $championship['championshipName'],
                'club_code' => $club_code,
                'last_update' => $championship['lastUpdate'],
                'standings' => json_encode($championship['standings']),
                'calendar' => json_encode(isset($championship['calendar']) ? $championship['calendar'] : '[]'),
            ];
        }, $club['championships']);

        $club = Fitet_Monitor_Manager::all_from_ISO_8859_15_to_utf8($club);

        $this->repository->save_bulk('fitet_monitor_championships', $championships);

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
        $this->repository->reset_players_ranking_id($club_code);
    }

    public function update_players($club_code) {
        if ($club_code == null)
            throw new Exception("Club code can not be null!");

        $this->logger->add_status($club_code, 'Start updating');

        $this->logger->add_status($club_code, "Getting info for club $club_code", 0);

        $this->logger->add_status($club_code, "Getting ranking list", 5);

        $players_from_portal = $this->portal->get_db_v2($club_code);

        // remove players not in portal anymore
        $players_from_portal_codes = array_map(function ($player) {
            return $player['code'];
        }, $players_from_portal);
        $this->repository->remove_player_not_in($club_code, $players_from_portal_codes);


        $last_ranking_id = $this->portal->find_rankings()[0]['rankingId'];
        $players_from_db = $this->repository->read_players($club_code);

        // player_codes_to_update: player codes in db with old ranking id
        $player_codes_to_update = array_map(function ($player) {
            return $player['code'];
        }, array_filter($players_from_db, function ($player) use ($last_ranking_id) {
            return $player['ranking_id'] < $last_ranking_id;
        }));
        // players_to_update: players in db with old ranking id - with portal updated data
        $players_to_update = array_filter($players_from_portal, function ($player) use ($player_codes_to_update) {
            return in_array($player['code'], $player_codes_to_update);
        });

        // player_codes_to_insert: player codes in portal but not in db
        $players_from_db_codes = array_map(function ($player) {
            return $player['code'];
        }, $players_from_db);
        $player_codes_to_insert = array_filter($players_from_portal_codes, function ($code) use ($players_from_db_codes) {
            return !in_array($code, $players_from_db_codes);
        });
        // players_to_insert: players in portal but not in db
        $players_to_insert = array_filter($players_from_portal, function ($player) use ($player_codes_to_insert) {
            return in_array($player['code'], $player_codes_to_insert);
        });

        $count = count($players_to_insert) + count($players_to_update);
        $i = 0;

        foreach (array_chunk($players_to_insert, 10) as $chunk) {
            foreach ($chunk as &$player) {
                $player = $this->fill_portal_player_with_online_info($player, $last_ranking_id, $i++, $count);
            }
            $this->repository->save_bulk('fitet_monitor_players', $chunk);

        }

        foreach (array_chunk($players_to_update, 10) as $chunk) {
            foreach ($chunk as &$player) {
                $player = $this->fill_portal_player_with_online_info($player, $last_ranking_id, $i++, $count);
            }
            $this->repository->save_bulk('fitet_monitor_players', $chunk);
        }


        /*
        todo update timestamp in club

                $club['players'] = $players;

         $last_update = new DateTime("now", new DateTimeZone('Europe/Rome')); //first argument "must" be a string
             $last_update->setTimestamp(time()); //adjust the object to correct timestamp
             $club['lastUpdate'] = $last_update->format('d/m/Y H:i:s');
             $club['lastPlayersUpdate'] = $last_update->format('d/m/Y H:i:s');

             $club = Fitet_Monitor_Manager::all_to_utf8($club);

             $this->repository->save_club_db($club);*/


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

        $club = Fitet_Monitor_Manager::all_from_ISO_8859_15_to_utf8($club);

        $this->repository->save_club_db($club);
    }

    private static function calculate_best_ranking($rankings) {
        if (empty($rankings)) {
            return ['position' => null, 'date' => null];
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

    private static function extract_last_ranking($rankings) {
        if (empty($rankings)) {
            return ['position' => null, 'date' => null, 'points'];
        }
        $last = $rankings[0];
        $last['position'] = empty($last['position']) ? null : $last['position'];
        $last['date'] = empty($last['date']) ? null : $last['date'];
        $last['points'] = empty($last['points']) ? null : $last['points'];
        return $last;
    }

    private static function calculate_last_diffs($rankings) {
        if (count($rankings) < 2) {
            return ['position' => null, 'points' => null];
        }
        $last = $rankings[0];
        $last_second = $rankings[1];
        $diffs['position'] = (empty($last['position']) || empty($last_second['position'])) ? null : ($last_second['position'] - $last['position']);
        $diffs['points'] = (empty($last['points']) || empty($last_second['points'])) ? null : ($last['points'] - $last_second['points']);
        return $diffs;
    }

    public static function from_ISO_8859_15_to_utf8($text) {
        if (FITET_MONITOR_MB_CONVERT_ENCODING_EXIST) {
            return mb_convert_encoding($text, "UTF-8", "ISO-8859-15");
        }
        if (FITET_MONITOR_ICONV_EXIST) {
            return iconv("ISO-8859-15", "UTF-8", $text);
        }
        return utf8_encode($text);

    }

    public static function from_windows_1252_to_utf8($text) {
        if (FITET_MONITOR_MB_CONVERT_ENCODING_EXIST) {
            return mb_convert_encoding($text, "UTF-8", "Windows-1252");
        }
        if (FITET_MONITOR_ICONV_EXIST) {
            return iconv("Windows-1252", "UTF-8", $text);
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

    /**
     * @param $player
     * @param $last_ranking_id
     * @return mixed
     */
    public function fill_portal_player_with_online_info($player, $last_ranking_id, $i, $count) {
        unset($player['calculation_type']);
        unset($player['foreigner']);
        unset($player['missing_data']);

        $player_name = $player['last_name'] . ' ' . $player['first_name'];
        $this->logger->add_status($player['club_code'], "Getting player season (" . ($i + 1) . "/$count): $player_name - ${player['code']}", 10 / $count);
        $details = $this->portal->get_player_details($player['id'], $last_ranking_id);
        $season = $details['season'];
        $profile = $details['profile'];

        $this->logger->add_status($player['club_code'], "Getting player history (" . ($i + 1) . "/$count): $player_name - ${player['code']}", 10 / $count);
        $history = $this->portal->get_player_history($player['id']);

        $player['season'] = json_encode($season);
        $player['rankings'] = json_encode($history['ranking']);
        $player['championships'] = json_encode($history['championships']);
        $player['national_tournaments'] = json_encode($history['nationalTournaments']);
        $player['national_doubles_tournaments'] = json_encode($history['nationalDoublesTournaments']);
        $player['regional_tournaments'] = json_encode($history['regionalTournaments']);


        $player['sector'] = $profile['sector'];

        if ($profile['fq'] && $player['type_id'] != 9) {
            $player['type_id'] = 2;
            $player['type'] = 'Fuori Quadro';
        }

        $best = self::calculate_best_ranking($history['ranking']);
        $player['best_rank'] = isset($best['position']) ? $best['position'] : null;
        $player['best_rank_date'] = isset($best['date']) ? $best['date'] : null;

        $last = self::extract_last_ranking($history['ranking']);
        $player['rank'] = $last['position'];
        $player['points'] = $last['points'];

        $diffs = self::calculate_last_diffs($history['ranking']);
        $player['diff_rank'] = $diffs['position'];
        $player['diff_points'] = $diffs['points'];

        $player['ranking_id'] = $last_ranking_id;

        $last_update = new DateTime("now", new DateTimeZone('Europe/Rome')); //first argument "must" be a string
        $last_update->setTimestamp(time()); //adjust the object to correct timestamp
        $player['last_update'] = $last_update->format('d/m/Y H:i:s');

        return $player;
    }


    private function sort_titles($a, $b): int {
        foreach (['season', 'tournament', 'competition', 'player'] as $field) {
            if ($a[$field] != $b[$field]) {
                return strcmp($b[$field], $a[$field]);
            }
        }
        return 0;
    }

    public static function all_from_ISO_8859_15_to_utf8($object) {
        if (is_string($object)) {
            if (empty(json_encode($object))) {
                $to_utf8 = Fitet_Monitor_Manager::from_ISO_8859_15_to_utf8($object);
                if (empty(json_encode($to_utf8)))
                    error_log("not encodable  => $to_utf8");
                return $to_utf8;
            }
        }
        if (is_array($object) || is_object($object)) {
            $object = (array)$object;
            foreach (array_keys($object) as $array_key) {
                $object[$array_key] = Fitet_Monitor_Manager::all_from_ISO_8859_15_to_utf8($object[$array_key]);
            }
        }
        return $object;

    }

    public static function all_from_windows_1252_to_utf8($object) {
        if (is_string($object)) {
            if (empty(json_encode($object))) {
                $to_utf8 = Fitet_Monitor_Manager::from_windows_1252_to_utf8($object);
                if (empty(json_encode($to_utf8)))
                    error_log("not encodable  => $to_utf8");
                return $to_utf8;
            }
        }
        if (is_array($object) || is_object($object)) {
            $object = (array)$object;
            foreach (array_keys($object) as $array_key) {
                $object[$array_key] = Fitet_Monitor_Manager::from_windows_1252_to_utf8($object[$array_key]);
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

        $championships = Fitet_Monitor_Manager::all_from_ISO_8859_15_to_utf8($championships);
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


    /**
     * Plugin activation
     *
     * The code that runs during plugin activation.
     * This action is documented in includes/class-fitet-monitor-activator.php
     *
     * @since    1.0.0
     */
    public function activate() {
        error_log("################");
        error_log("#   ACTIVATE   #");
        error_log("################");

        $this->repository->create_tables();
    }

    /**
     * Plugin deactivation
     *
     * The code that runs during plugin deactivation.
     * This action is documented in includes/class-fitet-monitor-deactivator.php
     *
     * @since    1.0.0
     */
    public function deactivate() {
        error_log("################");
        error_log("#  DEACTIVATE  #");
        error_log("################");

        // todo sposta in manager
        global $wpdb;
        $array_values = $wpdb->get_col("SELECT * FROM {$wpdb->prefix}fitet_monitor_clubs");
        foreach ($array_values as $club_code) {
            wp_clear_scheduled_hook('fm_cron_update_club_hook', [$club_code]);
            wp_clear_scheduled_hook('fm_cron_update_players_hook', [$club_code]);
            wp_clear_scheduled_hook('fm_cron_update_championships_hook', [$club_code]);
        }

        // todo ...dopo rimuovi...serve solo in uninstall
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}fitet_monitor_clubs;");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}fitet_monitor_players;");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}fitet_monitor_championships;");
    }

}
