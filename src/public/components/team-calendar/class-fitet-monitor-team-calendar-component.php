<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'public/components/match-cards-group/class-fitet-monitor-match-cards-group-component.php';

class Fitet_Monitor_Team_Calendar_Component extends Fitet_Monitor_Component {
    protected function components() {
        return [
            'matchCardsGroup' => new Fitet_Monitor_Match_Cards_Group_Component($this->plugin_name, $this->version),
        ];
    }

    protected function process_data($data) {
        return [
            'teamCalendarLabel' => __('Calendar', 'fitet-monitor'),
            'matchCardsGroups' => $this->main_content($data['calendar'])
        ];
    }

    private function main_content($groups) {
        return implode('', array_map(function ($group) {
            if (!empty($group['matches'])) {
                $phase = $group['matches'][0]['phase'];
                $championship_day = $group['matches'][0]['championshipDay'];
                $group['label'] = __('Day', 'fitet-monitor') . " " . $championship_day . " - " . ($phase == 'returnMatch' ? __('Return Match', 'fitet-monitor') : __('First Leg', 'fitet-monitor'));
            } else {
                $group['label'] = __('Day', 'fitet-monitor') . " " . $group['groupId'];
            }
            $group['anchor'] = '';
            return $this->components['matchCardsGroup']->render($group);
        }, $groups));
    }

}
