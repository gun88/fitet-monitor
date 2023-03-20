<?php


require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'public/components/cells/class-fitet-monitor-team-cell-component.php';
require_once FITET_MONITOR_DIR . 'public/components/video-thumbnail/class-fitet-monitor-video-thumbnail-component.php';
require_once FITET_MONITOR_DIR . 'public/utils/class-fitet-monitor-utils.php';

class Fitet_Monitor_Match_Card_Component extends Fitet_Monitor_Component {

    protected function components() {
        return [
            'teamCell' => new Fitet_Monitor_Team_Cell_Component($this->plugin_name, $this->version),
            'videoThumbnail' => new Fitet_Monitor_Video_Thumbnail_Component($this->plugin_name, $this->version)
        ];
    }


    protected function process_data($data) {
        $default = [
            'date' => '',
            'time' => '',
            'matchId' => '',
            'seasonId' => '',
            'championshipId' => '',
            'championshipName' => '',
            'championshipDay' => '',
            'formula' => '',
            'homeTeamName' => '',
            'homeResult' => '',
            'ownedHomeTeam' => '',
            'awayTeamName' => '',
            'awayResult' => '',
            'ownedAwayTeam' => '',
            'matchDetailUrl' => '',
        ];

        $data = array_merge($default, $data);

        $data['homeClass'] = $this->extract_team_class($data['homeResult'], $data['awayResult']);
        $data['awayClass'] = $this->extract_team_class($data['awayResult'], $data['homeResult']);

        $data['homeClass'] .= $data['ownedHomeTeam'] ? ' fm-match-team-owned' : '';
        $data['awayClass'] .= $data['ownedAwayTeam'] ? ' fm-match-team-owned' : '';

        $data['homeCell'] = $this->teams($data['homeTeamName'], $data['homeClubCode']);
        $data['awayCell'] = $this->teams($data['awayTeamName'], $data['awayClubCode']);

        if (isset($data['video']) && !empty($data['video'])) {
            $data['dateCell'] = $this->components['videoThumbnail']->render($data['video']);
        } else {
            $data['dateCell'] = $data['date'] . '<br>' . $data['time'];
        }

        return $data;
    }

    private function teams($team_name, $club_code) {
        $data = ['clubCode' => $club_code, 'teamName' => $team_name, 'teamPageUrl' => '', 'clubLogo' => Fitet_Monitor_Utils::club_logo_by_code($club_code)];
        return $this->components['teamCell']->render($data);
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

    public function render_out($data) {
        return $this->render($data);
    }


}
