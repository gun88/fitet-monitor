<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';

class Fitet_Monitor_Club_Image_Component extends Fitet_Monitor_Component {

	protected function process_data($data) {
		$data = array_merge(['clubCode' => '', 'clubName' => 'N/A', 'clubPageUrl' => ''], $data);
		$club_code = $data['clubCode'];
		$club_name = $data['clubName'];
		$club_image_url = empty($club_code) ? FITET_MONITOR_CLUB_NO_LOGO : "http://portale.fitet.org/images/societa/$club_code.jpg";
		$error_management = $club_image_url != FITET_MONITOR_CLUB_NO_LOGO ? "onError='this.onerror=null;this.src=\"" . FITET_MONITOR_CLUB_NO_LOGO . "\";'" : '';
		$image = "<img src='$club_image_url' alt='$club_name' $error_management/>";
		$club_page_url = $data['clubPageUrl'];
		if (!empty($club_page_url)) {
			$image = "<a class='fm-club-image' href='$club_page_url'>$image</a>";
		} else {
			$image = "<span class='fm-club-image'>$image</span>";
		}
		return $image;
	}

}
