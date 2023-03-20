<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'public/components/common/class-fitet-monitor-player-image-component.php';

class Fitet_Monitor_Player_Cell_Component extends Fitet_Monitor_Component {

    protected function components() {
        return ['image' => new Fitet_Monitor_Player_Image_Component($this->plugin_name, $this->version)];
    }

    protected function process_data($data) {
        if (!isset($data[0])) {
            $data = [$data];
        }

        return implode('', array_map(function ($data) use (&$i) {
            $data = array_merge(['playerId' => '', 'playerName' => 'N/A', 'playerPageUrl' => '', 'playerImage' => ''], $data);
            $player_name = $data['playerName'];
            $image = $this->components['image']->render($data);
            $player_page_url = $data['playerPageUrl'];
            if (!empty($player_page_url)) {
                $player_name = "<a class='fm-player-name' href='" . $player_page_url . "'>$player_name</a>";
            } else {
                $player_name = "<span class='fm-player-name'>$player_name</span>";
            }
            return "<div class='fm-player-cell'>$image$player_name</div>";
        }, $data));


    }

}
