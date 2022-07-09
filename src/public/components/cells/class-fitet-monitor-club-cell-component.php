<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'public/components/common/class-fitet-monitor-club-image-component.php';

class Fitet_Monitor_Club_Cell_Component extends Fitet_Monitor_Component {

	protected function components() {
		return ['image' => new Fitet_Monitor_Club_Image_Component($this->plugin_name, $this->version)];
	}

	protected function process_data($data) {
		$data = array_merge(['clubCode' => '', 'clubName' => 'N/A', 'clubPageUrl' => ''], $data);
		$club_name = $data['clubName'];
		$image = $this->components['image']->render($data);
		$club_page_url = $data['clubPageUrl'];
		if (!empty($club_page_url)) {
			$club_name = "<a class='fm-club-name' href='" . $club_page_url . "'>$club_name</a>";
		} else {
			$club_name = "<span class='fm-club-name'>$club_name</span>";
		}
		return "<div class='fm-club-cell'>$image$club_name</div>";

	}

}
