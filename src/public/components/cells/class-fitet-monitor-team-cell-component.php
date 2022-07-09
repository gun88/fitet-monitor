<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'public/components/common/class-fitet-monitor-team-image-component.php';

class Fitet_Monitor_Team_Cell_Component extends Fitet_Monitor_Component {

	protected function components() {
		return ['image' => new Fitet_Monitor_Team_Image_Component($this->plugin_name, $this->version)];
	}

	protected function process_data($data) {
		$data = array_merge(['clubCode' => '', 'teamName' => 'N/A', 'teamPageUrl' => ''], $data);
		$team_name = $data['teamName'];
		$image = $this->components['image']->render($data);
		$team_page_url = $data['teamPageUrl'];
		if (!empty($team_page_url)) {
			$team_name = "<a class='fm-team-name' href='" . $team_page_url . "'>$team_name</a>";
		} else {
			$team_name = "<span class='fm-team-name'>$team_name</span>";
		}
		return "<div class='fm-team-cell'>$image$team_name</div>";

	}

}
