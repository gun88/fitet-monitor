<?php

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

class Fitet_Monitor_Repository {

    private $plugin_name;
    private $version;

    /**
     * @param string $plugin_name
     * @param string $version
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function get_club_from_db($club_code) {
        $cache_key = $this->plugin_name . '-club-' . $club_code;
        $club_from_cache = wp_cache_get($cache_key);

        if (false && $club_from_cache !== false) {
            return $club_from_cache;
        }


        global $wpdb;

        $club_db = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}fitet_monitor_clubs WHERE code = %d", $club_code), ARRAY_A);

        if (!$club_db) {
            throw new Error("Club with code $club_code not found in DB");
        }

        $club_new_format = [
            'nationalTitles' => json_decode($club_db['nationalTitles'], true),
            'regionalTitles' => json_decode($club_db['regionalTitles'], true),
            'caps' => json_decode($club_db['caps'], true),
            'players' => $this->get_players($club_code), // (array)json_decode($club_db['players'], true),
            'championships' => json_decode($club_db['championships'], true),
            'lastUpdate' => $club_db['last_update'],
            'lastClubUpdate' => $club_db['last_club_update'],
            'lastPlayersUpdate' => $club_db['last_players_update'],
            'lastChampionshipsUpdate' => $club_db['last_championships_update'],
            'clubCode' => $club_db['code'],
            'clubName' => $club_db['name'],
            'clubProvince' => $club_db['province'],
            'clubLogo' => $club_db['logo'],
            'clubCron' => $club_db['cron'],

        ];

        wp_cache_set($cache_key, $club_new_format);

        return $club_new_format;
    }

    public function get_club_codes_db() {
        $cache_key = $this->plugin_name . '-clubs';
        $club_codes_from_cache = wp_cache_get($cache_key);
        if (false && $club_codes_from_cache !== false) {
            return $club_codes_from_cache;
        }
        global $wpdb;
        $club_codes_from_db = $wpdb->get_col("SELECT * FROM {$wpdb->prefix}fitet_monitor_clubs");
        wp_cache_set($cache_key, $club_codes_from_db);
        return $club_codes_from_db;
    }

    public function save_club_db($club) {

        if (empty($club['clubCode']))
            throw new Exception("empty club code");

        global $wpdb;

        $club_new_format = [
            'code' => $club['clubCode'],
            'name' => $club['clubName'],
            'province' => $club['clubProvince'],
            'logo' => $club['clubLogo'],
            'cron' => $club['clubCron'],
            // 'players' => '[]',//json_encode($club['players']),
            'championships' => json_encode($club['championships']),
            'caps' => json_encode($club['caps']),
            'nationalTitles' => json_encode($club['nationalTitles']),
            'regionalTitles' => json_encode($club['regionalTitles']),
            'last_update' => $club['lastUpdate'],
            'last_club_update' => $club['lastClubUpdate'],
            'last_players_update' => $club['lastPlayersUpdate'],
            'last_championships_update' => $club['lastChampionshipsUpdate'],
        ];

        $wpdb->replace(
            "{$wpdb->prefix}fitet_monitor_clubs",
            $club_new_format,
            [
                '%d',// code
                '%s',// name
                '%s',// province
                '%s',// logo
                '%s',// cron
                // '%s',// players
                '%s',// championships
                '%s',// caps
                '%s',// nationalTitles
                '%s',// regionalTitles
                '%s',// last_update
                '%s',// last_club_update
                '%s',// last_players_update
                '%s',// last_championships_update
            ]
        );


        // leggi tutti i player dal db
        // fai una diff con quelli attuali
        // inserisci quelli non presenti
        // aggiorna quelli già presenti

        wp_cache_flush();
        $wpdb->flush();
    }

    private function wpdb_bulk_insert($table, $rows) {
        global $wpdb;

        // Extract column list from first row of data
        $columns = array_keys($rows[0]);
        asort($columns);
        $columnList = '`' . implode('`, `', $columns) . '`';

        // Start building SQL, initialise data and placeholder arrays
        $sql = "INSERT INTO `$table` ($columnList) VALUES\n";
        $placeholders = [];
        $data = [];

        // Build placeholders for each row, and add values to data array
        foreach ($rows as $row) {
            ksort($row);
            $rowPlaceholders = [];

            foreach ($row as $key => $value) {
                $data[] = $value;
                $rowPlaceholders[] = is_numeric($value) ? '%d' : '%s';
            }

            $placeholders[] = '(' . implode(', ', $rowPlaceholders) . ')';
        }

        // Stitch all rows together
        $sql .= implode(",\n", $placeholders);

        $sql .= " ON DUPLICATE KEY UPDATE " . implode(',', array_map(function ($column) {
                return "$column=VALUES($column)";
            }, $columns));

        // Run the query.  Returns number of affected rows.
        return $wpdb->query($wpdb->prepare($sql, $data));
    }

    public function delete_clubs_db($club_codes) {

        global $wpdb;
        foreach ($club_codes as $club_code) {
            $wpdb->delete("{$wpdb->prefix}fitet_monitor_clubs", ['code' => $club_code], ['%d']);
        }
        wp_cache_flush();
        $wpdb->flush();

    }

    public function delete_players_db($club_codes) {

        global $wpdb;
        foreach ($club_codes as $club_code) {
            $wpdb->delete("{$wpdb->prefix}fitet_monitor_players", ['club_code' => $club_code], ['%d']);
        }
        wp_cache_flush();
        $wpdb->flush();

    }

    public function create_tables() {
        $this->create_clubs_table();
        $this->create_players_table();
    }

    private function create_clubs_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . "fitet_monitor_clubs";

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
  code int(10) NOT NULL,
  name varchar(255) NOT NULL,
  province varchar(2) NOT NULL,
  logo varchar(2048) NOT NULL,
  cron varchar(255) NOT NULL DEFAULT 'DEFAULT',
  last_update varchar(20) NULL,
  last_club_update varchar(20) NULL,
  last_players_update varchar(20) NULL,
  last_championships_update varchar(20) NULL,
  nationalTitles json DEFAULT '{}',
  regionalTitles json DEFAULT '{}',
  caps json DEFAULT '{}',
  championships json DEFAULT '{}',
  PRIMARY KEY  (code)
) $charset_collate;";

        // todo cambia i last update in timestamp
        // last_update timestamp NULL,
        // last_club_update timestamp NULL,
        // last_players_update timestamp NULL,
        // last_championships_update timestamp NULL,
        dbDelta($sql);
    }

    private function create_players_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . "fitet_monitor_players";

        $charset_collate = $wpdb->get_charset_collate();

// calculation_type,ranking_type
        $sql = "CREATE TABLE $table_name (
  code int(10) NOT NULL,
  id int(10) NOT NULL,
  last_name varchar(255) NOT NULL,
  first_name varchar(255) NOT NULL,
  rank int(10) NULL,
  best_rank int(10) NULL,
  best_rank_date varchar(20) NULL,
  points int(10) NULL,
  category int(1) NULL,
  sector varchar(255) NOT NULL,
  diff_rank int(10) NULL,
  diff_points int(10) NULL,
  birth_date varchar(20) NOT NULL,
  province varchar(2) NOT NULL,
  region varchar(255) NOT NULL,
  nationality varchar(2) NOT NULL,
  sex varchar(1) NOT NULL,
  type_id int(10) NOT NULL,
  type varchar(255) NOT NULL,
  ranking_id int(10) NOT NULL,
  rankings json DEFAULT '{}',
  season json DEFAULT '{}',
  championships json DEFAULT '{}',
  national_tournaments json DEFAULT '{}',
  national_doubles_tournaments json DEFAULT '{}',
  regional_tournaments json DEFAULT '{}',
  club_code int(10) NOT NULL,
  club_name varchar(255) NOT NULL,
  visible int(1) NOT NULL DEFAULT 1,
  override json DEFAULT '{}',
  last_update varchar(40) NULL,
  PRIMARY KEY  (code)
) $charset_collate;";

        // todo cambia in date
        // birth_date date NULL,
        // best_rank_date date NULL,
        dbDelta($sql);
    }


    public function save_players_________($players) {
        error_log("SAVE PLAYERS");
        global $wpdb;
        if (is_array($players) && count($players) > 0) {
            error_log(json_encode($players));

            $ps = array_map(function ($p) {
                $arr = [
                    'code' => $p['playerCode'],
                    'id' => $p['playerId'],
                    'name' => $p['playerName'],
                    'rank' => $p['rank'],
                    'best_rank' => $p['best']['position'],
                    'best_rank_date' => $p['best']['date'],
                    'points' => $p['points'],
                    'category' => $p['category'],
                    'sector' => $p['sector'],
                    'diff' => $p['diff'],
                    'birth_date' => $p['birthDate'],
                    'region' => $p['region'],
                    'sex' => $p['sex'],
                    'type' => $p['type'],
                    'type_id' => $p['typeId'],
                    'ranking_id' => $p['rankingId'],
                    'season' => json_encode($p['season']),
                    'ranking' => json_encode($p['history']['ranking']),
                    'championships' => json_encode($p['history']['championships']),
                    'national_tournaments' => json_encode($p['history']['nationalTournaments']),
                    'national_doubles_tournaments' => json_encode($p['history']['nationalDoublesTournaments']),
                    'regional_tournaments' => json_encode($p['history']['regionalTournaments']),
                    'club_code' => $p['clubCode'],
                    'club_name' => $p['clubName']
                ];
                error_log(json_encode($arr));
                return $arr;
            }, $players);


            $p2 = array_map(function ($p) {
                return $p['playerCode'];
            }, $players);

            error_log(json_encode($p2));
            $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}fitet_monitor_players");
            $this->wpdb_bulk_insert("{$wpdb->prefix}fitet_monitor_players", $ps);

            wp_cache_flush();
            $wpdb->flush();
        }
    }

    public function read_players($club_code) {
        global $wpdb;
        return $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$wpdb->prefix}fitet_monitor_players WHERE club_code = %d", $club_code),
            ARRAY_A
        );
    }

    private function convert_temp_players($players) {
        $players = array_map(function ($p) {
            return [
                'playerCode' => $p['code'],
                'playerId' => $p['id'],
                'playerName' => $p['last_name'] .' ' . $p['first_name'],
                'rank' => $p['rank'],
                'points' => $p['points'],
                'category' => $p['category'],
                'sector' => $p['sector'],
                'diff' => $p['diff'],
                'birthDate' => $p['birth_date'],
                'region' => $p['region'],
                'sex' => $p['sex'],
                'type' => $p['type'],
                'typeId' => $p['type_id'],
                'rankingId' => $p['ranking_id'],
                'clubCode' => $p['club_code'],
                'clubName' => $p['club_name'],
                'season' => json_decode($p['season'], true),
                'history' => [
                    'ranking' => json_decode($p['ranking'], true),
                    'championships' => json_decode($p['championships'], true),
                    'nationalTournaments' => json_decode($p['national_tournaments'], true),
                    'nationalDoublesTournaments' => json_decode($p['national_doubles_tournaments'], true),
                    'regionalTournaments' => json_decode($p['regional_tournaments'], true),
                ],
                'best' => ['position' => $p['best_rank'], 'date' => $p['best_rank_date']],
            ];
        }, $players);

        return $players;
        /*echo "<pre>" . json_encode($players) . "</pre>";
        echo "<br>";
        echo "<pre>" . json_encode($real) . "</pre>";
        die();*/
        //return $real;
    }

    public function get_players($club_code) {
        return $this->convert_temp_players($this->read_players($club_code));
    }

    public function reset_players_ranking_id($club_code) {
        global $wpdb;
        $wpdb->update(
            "{$wpdb->prefix}fitet_monitor_players",
            ['ranking_id' => 0],
            ['club_code' => $club_code],
            ['%d'],
            ['%d']
        );
        wp_cache_flush();
        $wpdb->flush();
    }

    public function remove_player_not_in($club_code, $codes) {
        if (count($codes) <= 0)
            return;
        global $wpdb;
        $sql_placeholders = implode(', ', array_map(function () {
            return '%d';
        }, $codes));

        array_unshift($codes, $club_code);

        $wpdb->query(
            $wpdb->prepare("DELETE FROM {$wpdb->prefix}fitet_monitor_players WHERE club_code = %d AND code NOT IN ($sql_placeholders)", $codes)
        );
    }

    public function save_bulk($players) {
        global $wpdb;
        $this->wpdb_bulk_insert("{$wpdb->prefix}fitet_monitor_players", $players);
    }

}
