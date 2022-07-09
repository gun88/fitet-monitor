<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';

class Fitet_Monitor_Team_Image_Component extends Fitet_Monitor_Component {

	protected function process_data($data) {
		$data = array_merge(['clubCode' => '', 'teamName' => 'N/A', 'teamPageUrl' => ''], $data);
		$club_code = $data['clubCode'];
		$team_name = $data['teamName'];
		$team_image_url = empty($club_code) ? FITET_MONITOR_CLUB_NO_LOGO : "http://portale.fitet.org/images/societa/$club_code.jpg";
		$error_management = $team_image_url != FITET_MONITOR_CLUB_NO_LOGO ? "onError='this.onerror=null;this.src=\"" . FITET_MONITOR_CLUB_NO_LOGO . "\";'" : '';
		$image = "<img src='$team_image_url' alt='$team_name' $error_management/>";
		$team_page_url = $data['teamPageUrl'];
		if (!empty($team_page_url)) {
			$image = "<a class='fm-team-image' href='$team_page_url'>$image</a>";
		} else {
			$image = "<span class='fm-team-image'>$image</span>";
		}
		return $image;
	}

}
