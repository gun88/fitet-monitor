<?php

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

        if ($club_from_cache !== false) {
            return $club_from_cache;
        }


        global $wpdb;

        $club_db = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}fitet_monitor_clubs WHERE code = %d", $club_code), ARRAY_A);

        $club_new_format = array(
            'nationalTitles' => json_decode($club_db['nationalTitles'], true),
            'regionalTitles' => json_decode($club_db['regionalTitles'], true),
            'caps' => json_decode($club_db['caps'], true),
            'players' => (array)json_decode($club_db['players'], true),
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

        );

        wp_cache_set($cache_key, $club_new_format);

        return $club_new_format;
    }

    public function get_club_codes_db() {
        $cache_key = $this->plugin_name . '-clubs';
        $club_codes_from_cache = wp_cache_get($cache_key);
        if ($club_codes_from_cache !== false) {
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

        $club_new_format = array(
            'code' => $club['clubCode'],
            'name' => $club['clubName'],
            'province' => $club['clubProvince'],
            'logo' => $club['clubLogo'],
            'cron' => $club['clubCron'],
            'players' => json_encode($club['players']),
            'championships' => json_encode($club['championships']),
            'caps' => json_encode($club['caps']),
            'nationalTitles' => json_encode($club['nationalTitles']),
            'regionalTitles' => json_encode($club['regionalTitles']),
            'last_update' => $club['lastUpdate'],
            'last_club_update' => $club['lastClubUpdate'],
            'last_players_update' => $club['lastPlayersUpdate'],
            'last_championships_update' => $club['lastChampionshipsUpdate'],
        );

        $wpdb->replace(
            "{$wpdb->prefix}fitet_monitor_clubs",
            $club_new_format,
            array(
                '%d',// code
                '%s',// name
                '%s',// province
                '%s',// logo
                '%s',// cron
                '%s',// players
                '%s',// championships
                '%s',// caps
                '%s',// nationalTitles
                '%s',// regionalTitles
                '%s',// last_update
                '%s',// last_club_update
                '%s',// last_players_update
                '%s',// last_championships_update
            )
        );

    }

    public function delete_clubs_db($club_codes) {

        global $wpdb;
        foreach ($club_codes as $club_code) {
            $wpdb->delete("{$wpdb->prefix}fitet_monitor_clubs", ['code' => $club_code], ['%d']);
        }
        wp_cache_flush();
        $wpdb->flush();

    }


}
