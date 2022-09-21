<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';

class Fitet_Monitor_Player_Image_Component extends Fitet_Monitor_Component {

	protected function process_data($data) {
		$data = array_merge(['playerId' => '', 'playerName' => 'N/A', 'playerPageUrl' => '','playerImage'=>''], $data);
		$player_id = $data['playerId'];
		$player_name = $data['playerName'];
		$player_image_url = $data['playerImage'];

		if (empty($player_image_url)) {
			$player_image_url = Fitet_Monitor_Utils::player_image_url($player_id);
		}

		if (empty($player_image_url)) {
			if (empty($player_id)) {
				$player_image_url = FITET_MONITOR_PLAYER_NO_IMAGE;
			} else {
				$player_image_url = "http://portale.fitet.org/images/atleti/$player_id.jpg";
			}
		}

		$error_management = $player_image_url != FITET_MONITOR_PLAYER_NO_IMAGE ? "onError='this.onerror=null;this.src=\"" . FITET_MONITOR_PLAYER_NO_IMAGE . "\";'" : '';
		$image = "<img src='$player_image_url' alt='$player_name' $error_management/>";
		$player_page_url = $data['playerPageUrl'];
		if (!empty($player_page_url)) {
			$image = "<a class='fm-player-image' href='$player_page_url'>$image</a>";
		} else {
			$image = "<span class='fm-player-image'>$image</span>";
		}
		return $image;
	}

}
