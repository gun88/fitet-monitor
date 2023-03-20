<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'public/components/table/class-fitet-monitor-table-component.php';

class Fitet_Monitor_Team_Standings_Component extends Fitet_Monitor_Component {
    protected function components() {
        return [
            'table' => new Fitet_Monitor_Table_Component($this->plugin_name, $this->version),
            'teamCell' => new Fitet_Monitor_Team_Cell_Component($this->plugin_name, $this->version),
        ];
    }


    protected function process_data($data) {
        return [
            'teamStandingsLabel' => __("Standings", 'fitet-monitor'),
            'promoLabel' => __("Promotion", 'fitet-monitor'),
            'playOffLabel' => __("Playoff", 'fitet-monitor'),
            'playOutLabel' => __("Playout", 'fitet-monitor'),
            'relegationLabel' => __("Relegation", 'fitet-monitor'),
            'retiredLabel' => __("Retired", 'fitet-monitor'),
            'table' => $this->table($data),
        ];
    }

    private function table($data) {
        return $this->components['table']->render(
            [
                'name' => 'fm-team-standings',
                'paginate' => false,
                'search' => false,
                'columns' => $this->columns(),
                'sort' => $this->sort(),
                'rows' => $this->rows($data),
            ]
        );
    }

    private function columns() {

        return [
            'ranking' => '#',
            // "teamId" => __('teamId'),
            "team" => __('Team', 'fitet-monitor'),
            // "clubCode" => __('clubCode'),
            // "clubName" => __('clubName'),
            // "teamStatus" => __('teamStatus'),
            "points" => __('Points', 'fitet-monitor'),
            "id" => __('ID', 'fitet-monitor'),
            "iv" => __('IV', 'fitet-monitor'),
            "ipa" => __('IPA', 'fitet-monitor'),
            "ip" => __('IP', 'fitet-monitor'),
            "pav" => __('PAV', 'fitet-monitor'),
            "pap" => __('PAP', 'fitet-monitor'),
            "sv" => __('SV', 'fitet-monitor'),
            "sp" => __('SP', 'fitet-monitor'),
            "pv" => __('PV', 'fitet-monitor'),
            "pp" => __('PP', 'fitet-monitor'),
            "pe" => __('PE', 'fitet-monitor'),


        ];
    }

    private function sort() {
        return [
            'ranking' => 'number',
            "points" => 'number',
            "id" => 'number',
            "iv" => 'number',
            "ipa" => 'number',
            "ip" => 'number',
            "pav" => 'number',
            "pap" => 'number',
            "sv" => 'number',
            "sp" => 'number',
            "pv" => 'number',
            "pp" => 'number',
            "pe" => 'number',
        ];
    }

    private function rows($data) {
        return array_map(function ($standing) use ($data) {
            $standing['team'] = $this->components['teamCell']->render([
                'clubCode' => $standing['clubCode'],
                'teamName' => $standing['teamName'],
                'teamPageUrl' => $standing['teamPageUrl'],
                'clubLogo' => $standing['clubLogo'],
            ]);
            unset($standing['teamName']);
            $standing['_rowClass'] = 'fm-team-status-' . $standing['teamStatus'];
            if ($standing['mainTeam']) {
                $standing['_rowClass'] .= ' fm-team-main-team';
            }
            if ($standing['retired']) {
                $standing['_rowClass'] .= ' fm-team-retired-team';
                $standing['ranking'] = count($data);
            }
            return $standing;
        }, $data);
    }


}
