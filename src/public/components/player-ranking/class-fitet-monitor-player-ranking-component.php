<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';


class Fitet_Monitor_Player_Ranking_Component extends Fitet_Monitor_Component {


	public function enqueue_scripts() {
		$file = FITET_MONITOR_DIR . "public/assets/chart.min.js";
		$file = plugin_dir_path($file) . basename($file);
		Fitet_Monitor_Helper::enqueue_script("chart.js", $file, [], $this->version, false);
		parent::enqueue_scripts();
	}

	protected function script_dependencies(): array {
		return ['chart.js', 'jquery'];
	}

	protected function process_data($data) {

		$rankings = array_map(function ($ranking) {
			return empty($ranking['position']) ? null : $ranking['position'];
		}, $data);
		$points = array_map(function ($ranking) {
			return $ranking['points'];
		}, $data);
		$points_label = array_map(function ($ranking) {
			return $ranking['date'];
		}, $data);


		$best_ranking = array_filter($rankings, function ($ranking) {
			return $ranking != null;
		});

		// todo !!!!!!!!!!!!!!!!!
		$bestRanking = empty($best_ranking) ? 130000 : min($best_ranking);
		$bestPoints = max(array_filter($points, function ($point) {
			return $point != null;
		}));

		return [
			'rankingLabel' => __('Ranking History'),
			'bestRanking' => $bestRanking,
			'bestPoints' => $bestPoints,
			'rankings' => json_encode($rankings),
			'points' => json_encode($points),
			'labels' => json_encode($points_label),
		];
	}


}
