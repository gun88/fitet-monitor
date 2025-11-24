<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';

class Fitet_Monitor_Player_Season_Component extends Fitet_Monitor_Component {

    protected function components() {
        return [];
    }

    protected function process_data($data) {
        return [
            'seasonLabel' => __('Season', 'fitet-monitor'),
            'season' => $data['season']
        ];
    }

}
