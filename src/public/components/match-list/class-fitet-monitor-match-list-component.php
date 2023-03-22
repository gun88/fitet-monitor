<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'public/components/match-cards-group/class-fitet-monitor-match-cards-group-component.php';
require_once FITET_MONITOR_DIR . 'public/utils/class-fitet-monitor-utils.php';

class Fitet_Monitor_Match_List_Component extends Fitet_Monitor_Component {


    protected function components() {
        return [
            'matchCardsGroup' => new Fitet_Monitor_Match_Cards_Group_Component($this->plugin_name, $this->version),
        ];
    }

    private function to_group_label($day) {
        $label = ucfirst(utf8_encode(strftime("%A %e %B %Y", strtotime(str_replace('/', '-', $day)))));
        $label = str_replace("Monday", __('Monday', 'fitet-monitor'), $label);
        $label = str_replace("Tuesday", __('Tuesday', 'fitet-monitor'), $label);
        $label = str_replace("Wednesday", __('Wednesday', 'fitet-monitor'), $label);
        $label = str_replace("Thursday", __('Thursday', 'fitet-monitor'), $label);
        $label = str_replace("Friday", __('Friday', 'fitet-monitor'), $label);
        $label = str_replace("Saturday", __('Saturday', 'fitet-monitor'), $label);
        $label = str_replace("Sunday", __('Sunday', 'fitet-monitor'), $label);

        $label = str_replace("January", __('January', 'fitet-monitor'), $label);
        $label = str_replace("February", __('February', 'fitet-monitor'), $label);
        $label = str_replace("March", __('March', 'fitet-monitor'), $label);
        $label = str_replace("April", __('April', 'fitet-monitor'), $label);
        $label = str_replace("May", __('May', 'fitet-monitor'), $label);
        $label = str_replace("June", __('June', 'fitet-monitor'), $label);
        $label = str_replace("July", __('July', 'fitet-monitor'), $label);
        $label = str_replace("August", __('August', 'fitet-monitor'), $label);
        $label = str_replace("September", __('September', 'fitet-monitor'), $label);
        $label = str_replace("October", __('October', 'fitet-monitor'), $label);
        $label = str_replace("November", __('November', 'fitet-monitor'), $label);
        $label = str_replace("December", __('December', 'fitet-monitor'), $label);

        return $label;
    }

    protected function process_data($data) {
        return [
            'filter' => $this->filter($data['seasons'], $data['seasonId']),
            'mainContent' => $this->main_content($data['groups'], $data['scrollToRecent'])
        ];
    }

    private function main_content($groups, $scroll_to_recent) {

        $recent_date = Fitet_Monitor_Utils::extract_recent_date(array_map(function ($group) {
            return $group['groupId'];
        }, $groups));

        return implode('', array_map(function ($group) use ($recent_date, $scroll_to_recent) {
            $group['label'] = $this->to_group_label($group['groupId']);
            $group['anchor'] = $scroll_to_recent && $group['groupId'] == $recent_date ? 'fm-match-now' : '';
            return $this->components['matchCardsGroup']->render($group);
        }, $groups));
    }

    private function filter($seasons, $season_id) {
        $filters = '<div><img alt="filter" src="' . FITET_MONITOR_ICON_FILTER . '"/><span>' . __('Season', 'fitet-monitor') . '</span>';
        $filters .= "<select id='fm-match-list-filter'>";
        foreach ($seasons as $season) {
            $filters .= "<option value='" . $season['seasonId'] . "' " . ($season_id == $season['seasonId'] ? 'selected' : '') . ">" . $season['seasonName'] . "</option>";
        }
        $filters .= "</select>";
        $filters .= '</div>';
        return $filters;

    }

    public function render_out($data) {
        $this->initialize();
        return $this->render($data);
    }

}
