<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'public/components/cells/class-fitet-monitor-team-cell-component.php';
require_once FITET_MONITOR_DIR . 'public/components/video-player/class-fitet-monitor-video-player-component.php';
require_once FITET_MONITOR_DIR . 'public/components/match-results/class-fitet-monitor-match-results-component.php';

class Fitet_Monitor_Match_Detail_Component extends Fitet_Monitor_Component {

    protected function components() {
        return [
            'teamCell' => new Fitet_Monitor_Team_Cell_Component($this->plugin_name, $this->version, []),
            'videoPlayer' => new Fitet_Monitor_Video_Player_Component($this->plugin_name, $this->version),
            'matchResults' => new Fitet_Monitor_Match_Results_Component($this->plugin_name, $this->version),
        ];
    }

    protected function process_data($data) {

        $data['dayLabel'] = __('Day', 'fitet-monitor');
        $data['phaseLabel'] = $data['phase'] == 'returnMatch' ? __('Return Match', 'fitet-monitor') : __('First Leg', 'fitet-monitor');

        $data['homeTeamCell'] = $this->components['teamCell']->render([
            'clubCode' => $data['homeClubCode'],
            'clubLogo' => $data['homeClubLogo'],
            'teamName' => $data['homeTeamName'],
            'teamPageUrl' => $data['homeTeamPageUrl']]);

        $data['awayTeamCell'] = $this->components['teamCell']->render([
            'clubCode' => $data['awayClubCode'],
            'clubLogo' => $data['awayClubLogo'],
            'teamName' => $data['awayTeamName'],
            'teamPageUrl' => $data['awayTeamPageUrl']]);

        $data['video'] = $this->components['videoPlayer']->render(['url' => isset($data['video']['url']) ? $data['video']['url'] : null]);
        $data['results'] = $this->components['matchResults']->render($data['results']);

        $data['scoreClass'] = $data['hasResults'] ? 'fm-has-results' : 'fm-no-results';

        $data['setsLabel'] = __('Set', 'fitet-monitor');
        $data['pointsLabel'] = __('Points', 'fitet-monitor');

        if ($data['homeSets'] == 0 && $data['awaySets'] == 0) {
            $data['homeSets'] = $data['awaySets'] = '';
        }
        if ($data['homePoints'] == 0 && $data['awayPoints'] == 0) {
            $data['homePoints'] = $data['awayPoints'] = '';
        }

        $data['fitetIcon'] = plugin_dir_url(FITET_MONITOR_DIR . 'public/assets/fitet-monitor-no-club-image.svg') . 'logo-fitet-300x219.png';
        $data['openFitetPage'] = __('Open Fitet Page', 'fitet-monitor');

        return $data;
    }


}
