<?php
require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'public/components/table/class-fitet-monitor-table-component.php';
require_once FITET_MONITOR_DIR . 'public/components/cells/class-fitet-monitor-player-cell-component.php';
require_once FITET_MONITOR_DIR . 'public/components/cells/class-fitet-monitor-club-cell-component.php';

class Fitet_Monitor_Players_Caps_Component extends Fitet_Monitor_Component {


    protected function components() {
        return [
            'table' => new Fitet_Monitor_Table_Component($this->plugin_name, $this->version),
            'playerCell' => new Fitet_Monitor_Player_Cell_Component($this->plugin_name, $this->version),
            'clubCell' => new Fitet_Monitor_Club_Cell_Component($this->plugin_name, $this->version),
        ];
    }

    protected function process_data($data) {
        $data = array_merge(['players' => [], 'listUrl' => '#', 'tableUrl' => '#', 'multiClub' => true], $data);

        return [
            'pageMenu' => $this->menu($data['listUrl'], $data['tableUrl']),
            'mainContent' => $this->main_content($data['players'], $data['multiClub']),
        ];

    }

    private function main_content($players, $multi_club) {
        if (empty($players)) {
            return "<p style='text-align: center'>" . __('No Results', 'fitet-monitor') . "</p>";
        }

        $players = $this->rows($players, $multi_club);
        return $this->components['table']->render([
            'name' => 'fm-players-caps',
            'columns' => $this->columns($multi_club),
            'sort' => $this->sort(),
            'rows' => $players,
        ]);
    }

    private function menu($list_url, $table_url) {
        $menu_entries = [];
        $menu_entries[] = '<a href="/' . $list_url . '"><img alt="list" src="' . FITET_MONITOR_ICON_LIST . '"/><span>' . __('List', 'fitet-monitor') . '</span></a>';
        $menu_entries[] = '<a href="/' . $table_url . '"><img alt="caps" src="' . FITET_MONITOR_ICON_TABLE . '"/><span>' . __('Table', 'fitet-monitor') . '</span></a>';
        $menu_entries[] = '<span><img alt="caps" src="' . FITET_MONITOR_ICON_HASHTAG . '"/>' . __('Caps', 'fitet-monitor') . '</span>';

        return implode('|', $menu_entries);
    }

    private function columns($multi_club) {
        $columns = [];
        $columns  ['playerName'] = __('Name', 'fitet-monitor');
        $columns  ['caps'] = __('Caps', 'fitet-monitor');
        if ($multi_club) {
            $columns['club'] = __('Club', 'fitet-monitor');
        }
        return $columns;
    }


    private function sort() {
        return [
            'caps' => 'number',
        ];
    }

    private function rows($players, $multi_club) {
        return array_map(function ($player) use ($multi_club) {
            $row = [
                'playerName' => $this->components['playerCell']->render([
                    'playerId' => $player['playerId'],
                    'playerName' => $player['playerName'],
                    'playerPageUrl' => $player['playerUrl']
                ]),
                'caps' => $player['caps'],
                'clubName' => $player['clubName'],
                'clubCode' => $player['clubCode'],
                'playerUrl' => $player['playerUrl'],

            ];
            if ($multi_club) {
                $row['club'] = $this->components['clubCell']->render([
                    'clubCode' => $player['clubCode'],
                    'clubName' => $player['clubName'],
                    'clubLogo' => $player['clubLogo'],
                    'clubPageUrl' => $player['clubPageUrl']
                ]);
            }
            return $row;
        }, $players);

    }


}
