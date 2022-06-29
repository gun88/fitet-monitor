<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';


class Fitet_Monitor_Athlete_Card_Component extends Fitet_Monitor_Component {

	private $deault_confing = [
		'nameClass' => '',
		'codeClass' => '',
		'categoryClass' => '',
		'rankClass' => '',
		'pointsClass' => '',
		'diffClass' => '',
		'sectorClass' => '',
		'typeClass' => '',
		'sexClass' => '',
		'birthDateClass' => '',
		'clubClass' => '',
		'clubCodeClass' => '',
		'regionClass' => '',
		'linkWrapperStart' => '',
		'linkWrapperEnd' => '',
	];

	private $labels = [];

	private $config;

	public function __construct($plugin_name, $version, $config) {
		parent::__construct($plugin_name, $version);
		$this->config = $config;
		$this->init_labels();
	}

	public function calculate_best_ranking($rankings) {
		// todo to utils class

		$rankings = array_map(function ($ranking) {
			if (empty($ranking['position'])) {
				return null;
			} else {
				return ['position' => $ranking['position'], 'date' => $ranking['date']];
			}
		}, $rankings);

		$rankings = array_values(array_filter($rankings, function ($ranking) {
			return $ranking != null;
		}));
		usort($rankings, function ($r1, $r2) {
			return intval($r1['position']) - intval($r2['position']);

		});
		return isset($rankings[0]) ? $rankings[0] : null;
	}

	protected function process_data($data) {
		$data = array_merge($this->labels, $this->deault_confing, $this->config, $data);

		if (isset($data['link'])) {
			$link = $data['link'];
			$data['linkWrapperStart'] = "<a href='$link'>";
			$data['linkWrapperEnd'] = "</a>";
		}
		$best_ranking = $this->calculate_best_ranking( $data['history']['ranking']);
		if ($best_ranking != null) {
			$position = $best_ranking['position'];
			$date = $best_ranking['date'];
			$onLabel = __('on');
			$data['best'] = "$position $onLabel $date";
		} else {
			$data['best'] = "N/A";
		}


		return $data;
	}

	private function init_labels() {
		$this->labels = [
			'clubLabel' => __('Club'),
			'clubCodeLabel' => __('Code'),
			'categoryLabel' => __('Category'),
			'rankLabel' => __('Rank'),
			'bestLabel' => __('Best'),
			'pointsLabel' => __('Points'),
			'diffLabel' => __('Difference'),
			'sectorLabel' => __('Sector'),
			'regionLabel' => __('Region'),
			'typeLabel' => __('Type'),
			'sexLabel' => __('Sex'),
			'birthDateLabel' => __('Birth Date'),
		];
	}


}
