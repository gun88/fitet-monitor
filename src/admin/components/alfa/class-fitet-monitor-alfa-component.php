<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'admin/components/beta/class-fitet-monitor-beta-component.php';


class Fitet_Monitor_Alfa_Component extends Fitet_Monitor_Component {

	public function enqueue_styles() {
		parent::enqueue_styles();
		$additional = plugin_dir_url(__FILE__) . 'additional.css';
		wp_enqueue_style('Zzz_additional.css', $additional, array(), $this->version, 'all');
	}

	public function components() {
		return ['beta' => new Fitet_Monitor_Beta_Component($this->version)];
	}

	public function process_data($data) {
		$data['contentBeta'] = '';
		for ($i = 0; $i < $data['betas']; $i++) {
			$data['contentBeta'] .= $this->components['beta']->render(['innerBeta' => "innerBetino $i"]);
		}
		return $data;
	}

}
