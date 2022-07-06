<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';


class Fitet_Monitor_Player_Card_Component extends Fitet_Monitor_Component {

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
		'playerImage' => '',
	];

	private $labels = [];

	private $config;

	public function __construct($plugin_name, $version, $config) {
		parent::__construct($plugin_name, $version);
		$this->config = $config;
		$this->labels();
	}

	protected function process_data($data) {
		$data = array_merge($this->labels, $this->deault_confing, $this->config, $data);

		$data['playerImage'] = Fitet_Monitor_Utils::player_image($data, $data['link']);
		$data['playerUrl'] = Fitet_Monitor_Utils::player_page_url($data, $data['link']);

		$best_ranking = Fitet_Monitor_Utils::calculate_best_ranking($data['history']['ranking']);
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

	private function labels() {
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
