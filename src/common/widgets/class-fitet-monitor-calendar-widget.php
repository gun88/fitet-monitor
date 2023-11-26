<?php

require_once FITET_MONITOR_DIR . 'public/utils/class-fitet-monitor-utils.php';
require_once FITET_MONITOR_DIR . 'public/components/match-list/class-fitet-monitor-match-list-component.php';


// Creating the widget
class Fitet_Monitor_Calendar_Widget extends WP_Widget {

    function __construct() {
        $id = __('Fitet Monitor Match Widget', 'fitet-monitor');
        $description = __('Show recent matches', 'fitet-monitor');
        parent::__construct('Fitet_Monitor_Calendar_Widget', $id, ['description' => $description,]);
    }

    public function widget($args, $instance) {
        $title = apply_filters('widget_title', $instance['title']);

        echo $args['before_widget'];
        if (!empty($title))
            echo $args['before_title'] . $title . $args['after_title'];

        // This is where you run the code and display the output
        echo $this->content($instance['matchPageId']);
        echo $args['after_widget'];
    }

    // Widget Backend
    public function form($instance) {
        if (isset($instance['title'])) {
            $title = $instance['title'];
        } else {
            $title = __('New title', 'fitet-monitor');
        }
        if (isset($instance['matchPageId'])) {
            $matchPageId = $instance['matchPageId'];
        } else {
            $matchPageId = 0;
        }
        // Widget admin form
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'fitet-monitor'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                   name="<?php echo $this->get_field_name('title'); ?>" type="text"
                   value="<?php echo esc_attr($title); ?>"/>
        </p>
        <p>
            <label
                for="<?php echo $this->get_field_id('matchPageId'); ?>"><?php _e('Match page id:', 'fitet-monitor'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('matchPageId'); ?>"
                   name="<?php echo $this->get_field_name('matchPageId'); ?>" type="text"
                   value="<?php echo esc_attr($matchPageId); ?>"/>
        </p>
        <?php
    }

// Updating widget replacing old instances with new
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['matchPageId'] = (!empty($new_instance['matchPageId'])) ? strip_tags($new_instance['matchPageId']) : '';
        return $instance;
    }

// Class Fitet_Monitor_Calendar_Widget ends here

    /**
     * @return mixed
     */
    public function content($match_page_id) {

        global $fitet_monitor_manager;
        $manager = $fitet_monitor_manager;

        $data = $manager->get_clubs([
                'championships' => [
                    'seasonId' => '',
                    'seasonName' => '',
                    'championshipId' => '',
                    'championshipName' => '',
                    'calendar' => '',
                    'standings' => '',
                ]
            ]
        );

        $data = array_map(function ($club) {
            return $club['championships'];
        }, $data);
        $data = array_merge(...$data);

        $acc = [];
        foreach ($data as $championship) {
            if (empty($championship['calendar']))
                continue;
            foreach ($championship['calendar'] as $days) {
                foreach ($days as $match) {
                    foreach (['firstLeg', 'returnMatch'] as $leg) {
                        if (empty($match[$leg]['date']) || empty($match[$leg]['time']))
                            continue;
                        if (!empty($match[$leg]['match']) && $match[$leg]['match'] != 0) {
                            $match[$leg]['phase'] = $leg;
                            $match[$leg]['seasonName'] = $championship['seasonName'];
                            $match[$leg]['seasonId'] = $championship['seasonId'];
                            $match[$leg]['championshipId'] = $championship['championshipId'];
                            $match[$leg]['championshipName'] = $championship['championshipName'];
                            $match[$leg]['championshipDay'] = $match['championshipDay'];
                            $match[$leg]['homeTeamName'] = $leg == 'returnMatch' ? $match['away'] : $match['home'];
                            $match[$leg]['awayTeamName'] = $leg == 'returnMatch' ? $match['home'] : $match['away'];
                            $match[$leg]['homeClubCode'] = Fitet_Monitor_Utils::extract_club_code_from_standings_by_team_name($championship['standings'], $match[$leg]['homeTeamName']);
                            $match[$leg]['awayClubCode'] = Fitet_Monitor_Utils::extract_club_code_from_standings_by_team_name($championship['standings'], $match[$leg]['awayTeamName']);
                            $match[$leg]['dateX'] = strtotime($this->to_date_string($match[$leg]['date'], $match[$leg]['time']));
                            $match[$leg]['dateY'] = $this->to_date_string($match[$leg]['date'], $match[$leg]['time']);
                            $match[$leg]['dateZ'] = strtotime($this->to_date_string($match[$leg]['date'], $match[$leg]['time'])) - time();;
                            $match[$leg]['homeTeamId'] = Fitet_Monitor_Utils::extract_team_id_from_standings_by_team_name($championship['standings'], $match[$leg]['homeTeamName']);
                            $match[$leg]['awayTeamId'] = Fitet_Monitor_Utils::extract_team_id_from_standings_by_team_name($championship['standings'], $match[$leg]['awayTeamName']);
                            $match[$leg]['ownedHomeTeam'] = Fitet_Monitor_Utils::is_owned_team_from_standings_by_team_id($championship['standings'], $match[$leg]['homeTeamId']);
                            $match[$leg]['ownedAwayTeam'] = Fitet_Monitor_Utils::is_owned_team_from_standings_by_team_id($championship['standings'], $match[$leg]['awayTeamId']);
                            $match[$leg]['ownedMatch'] = $match[$leg]['ownedHomeTeam'] || $match[$leg]['ownedAwayTeam'];
                            $acc[] = $match[$leg];
                        }
                    }
                }
            }
        }

        $data = array_values(array_filter($acc, function ($a) {
            return $a['ownedMatch'];
        }));

        usort($data, function ($a, $b) {
            return abs($a['dateZ']) > abs($b['dateZ']);
        });

        $data = array_slice($data, 0, 4);

        $card = new Fitet_Monitor_Match_Card_Component(FITET_MONITOR_NAME, FITET_MONITOR_VERSION);
        $card->initialize();

        $data = array_map(function ($a) use ($card, $match_page_id) {
            return $card->render_out([
                'date' => $a['date'],
                'time' => $a['time'],
                'matchId' => $a['match'],
                'seasonId' => $a['seasonId'],
                'championshipId' => $a['championshipId'],
                'championshipName' => $a['championshipName'],
                'championshipDay' => $a['championshipDay'],
                'formula' => $a['formula'],
                'homeClubCode' => $a['homeClubCode'],
                'homeTeamName' => $a['homeTeamName'],
                'homeResult' => trim(explode('-', $a['result'])[$a['phase'] == 'firstLeg' ? 0 : 1]),
                'ownedHomeTeam' => $a['ownedHomeTeam'],
                'awayResult' => trim(explode('-', $a['result'])[$a['phase'] == 'firstLeg' ? 1 : 0]),
                'awayClubCode' => $a['awayClubCode'],
                'awayTeamName' => $a['awayTeamName'],
                'ownedAwayTeam' => $a['ownedAwayTeam'],
                'matchDetailUrl' => "index.php?page_id=$match_page_id&match=" . $a['match'],
            ]);
        }, $data);

        $show_more_label = __('Show more...', 'fitet-monitor');
        $matches_link = "index.php?page_id=$match_page_id";
        $data = implode('', $data);
        return "<div class='fm-calendar-widget'>" .
            "<div class='fm-calendar-widget-content'>$data</div>" .
            "<div class='fm-calendar-widget-show-more' style='text-align: center;margin-bottom: 1em;'>" .
            "<a style='display: block;border: 1px solid;border-radius: 7px;padding: 8px' " .
            "href='$matches_link'>$show_more_label</a></div>" .
            "</div>";

    }

    private function to_date_string($date, $time) {
        $date_parts = explode('/', $date);
        $time_parts = explode('.', $time);
        if (empty($time_parts[1])) {
            $time_parts[1] = '00';
        }
        return "$date_parts[2]-$date_parts[1]-$date_parts[0] $time_parts[0]:$time_parts[1]";
    }

}

