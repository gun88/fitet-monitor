<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'public/components/table/class-fitet-monitor-table-component.php';
require_once FITET_MONITOR_DIR . 'public/components/cells/class-fitet-monitor-player-cell-component.php';

class Fitet_Monitor_Match_Results_Component extends Fitet_Monitor_Component {

    protected function components() {
        return [
            'table' => new Fitet_Monitor_Table_Component($this->plugin_name, $this->version),
            'playerCell' => new Fitet_Monitor_Player_Cell_Component($this->plugin_name, $this->version),
        ];
    }

    protected function process_data($data) {

        return [
            'table' => $this->table($data),
        ];
    }

    protected function table($data) {
        $home_team_name = $data['homeTeamName'];
        $away_team_name = $data['awayTeamName'];
        $max_set_count = $data['maxSetCount'];
        $header = $this->create_header($home_team_name, $away_team_name, $max_set_count);
        $body = $this->create_body($data['results']);
        $show_sets_label = __('Show/Hide Sets', 'fitet-monitor');
        $foot = "<tr><td colspan='100'><a href='#' class='fm-show-details-link'>$show_sets_label</a></td></tr>";

        return "<table><thead>$header</thead><tbody>$body</tbody><tfoot>$foot</tfoot></table>";
    }

    private function create_sets_header($max_set_count) {
        $set_label = __('Set', 'fitet-monitor');
        return implode('', array_map(function ($i) use ($set_label) {
            return "<th class='fm-match-set' colspan='2'>$set_label $i</th>";
        }, range(1, $max_set_count)));
    }

    private function create_header($home_team_name, $away_team_name, $max_set_count) {
        $sets = $this->create_sets_header($max_set_count);
        $match = __('Match', 'fitet-monitor');
        return "<tr><th class='fm-match-position'>#</th><th>$home_team_name</th><th>$away_team_name</th>$sets<th colspan='2' class='fm-match-overall'>$match</th></tr>";
    }

    private function create_body($results) {

        return implode('', array_map(function ($result) {
            $home_player_cell_class = $result['homePlayerSets'] > $result['awayPlayerSets'] ? 'fm-match-win' : 'fm-match-lost';
            $away_player_cell_class = $result['awayPlayerSets'] > $result['homePlayerSets'] ? 'fm-match-win' : 'fm-match-lost';
            return "<tr>" .
                "<td class='fm-match-position'>" . $result['matchPosition'] . "</td>" .
                "<td class='" . $home_player_cell_class . "'>" . $this->to_player_cell($result['homePlayer']) . "</td>" .
                "<td class='" . $away_player_cell_class . "'>" . $this->to_player_cell($result['awayPlayer']) . "</td>" .
                implode('', array_map(function ($set) {
                    return $this->to_set_td_pair($set);
                }, $result['sets'])) .
                "<td class='fm-match-overall'>" . $result['homePlayerSets'] . "</td>" .
                "<td class='fm-match-overall'>" . $result['awayPlayerSets'] . "</td>" .
                "</tr>";
        }, $results));
    }


    private function to_set_td_pair($set) {
        $class = "fm-match-set";
        if ($set['home'] == $set['away'] && $set['away'] == 0) {
            $class .= " fm-match-set-not-played";
        }
        return "<td class='$class'>" . $set['home'] . "</td><td class='$class'>" . $set['away'] . "</td>";
    }

    private function to_player_cell($player) {
        $arr = [$player];
        if (isset($player[0])) {
            $arr = $player;
        }
        $arr = array_map(function ($player) {
            return [
                'playerId' => $player['playerId'],
                'playerName' => $player['playerName'],
                'playerPageUrl' => $player['playerUrl'],
                'playerImage' => $player['playerImageUrl']];
        }, $arr);
        return $this->components['playerCell']->render($arr);
    }


}
