<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';

class Fitet_Monitor_Video_Player_Component extends Fitet_Monitor_Component {


	protected function process_data($data) {
        if (empty($data['url'])) {
            return '';
        }

		$url = $data['url'];

		switch ($this->get_url_type($url)) {
			case "youtube":
				return $this->youtube_embed($url);
			case "dailymotion":
				return $this->dailymotion_embed($url);
			case "vimeo":
				return $this->vimeo_embed($url);
			default:
				return $this->file_embed($url);

		}
	}

	private function youtube_embed($url) {
		$id = $this->extract_youtube_id($url);
		$url = "https://www.youtube.com/embed/$id";
		return $this->to_iframe($url);
	}

	private function vimeo_embed($url) {
		$id = $this->extract_vimeo_id($url);
		$url = "https://player.vimeo.com/video/$id";
		return $this->to_iframe($url);
	}

	private function dailymotion_embed($url) {
		$id = $this->extract_dailymotion_id($url);
		$url = "https://www.dailymotion.com/embed/video/$id";
		return "<div class='fm-video-dailymotion'>".$this->to_iframe($url) . "</div>";
	}

	private function file_embed($url) {
		return '<video><source src="' . $url . '"></video><br><a target="_blank" href="' . $url . '">Video</a>';
	}

	private function get_url_type($url) {
		// ^(https?\:\/\/)?(www\.youtube\.com|youtu\.be)\/.+$
		if (preg_match('/^(https?:\/\/)?(www\.youtube\.com|youtu\.be)\/.+$/i', $url)) {
			return 'youtube';
		}

		if (preg_match('/^(https?:\/\/)?(vimeo\.com)\/.+$/i', $url)) {
			return 'vimeo';
		}
		if (preg_match('/^(https?:\/\/)?(www\.)?dailymotion\.com\/.+$/i', $url)) {
			return 'dailymotion';
		}
		return "unknown";

	}

	private function extract_youtube_id($url) {
		preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
		return $match[1];
	}

	private function extract_vimeo_id($url) {
		preg_match("/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*([0-9]{6,11})[?]?.*/", $url, $match);
		return $match[5];
	}

	private function extract_dailymotion_id($url) {
		if (preg_match('!^.+dailymotion\.com/(video|hub)/([^_]+)[^#]*(#video=([^_&]+))?|(dai\.ly/([^_]+))!', $url, $m)) {
			if (isset($m[6])) {
				return $m[6];
			}
			if (isset($m[4])) {
				return $m[4];
			}
			return $m[2];
		}
		return false;
	}

	public function to_iframe($url) {
		return '<iframe src="' . $url . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>';
	}


}
