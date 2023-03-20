<?php


require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'public/components/match-card/class-fitet-monitor-match-card-component.php';
require_once FITET_MONITOR_DIR . 'public/utils/class-fitet-monitor-utils.php';

class Fitet_Monitor_Match_Cards_Group_Component extends Fitet_Monitor_Component {

	protected function components() {
		return [
			'matchCard' => new Fitet_Monitor_Match_Card_Component($this->plugin_name, $this->version)
		];
	}

	protected function process_data($data) {
		$default = [
			'anchor' => '',
			'label' => '',
			'matches' => [],
		];

		$data = array_merge($default, $data);

		$data['anchor'] = $this->to_anchor($data['anchor']);

		$data['matchCards'] = implode('', array_map(function ($match) {
			return $this->components['matchCard']->render($match);
		}, $data['matches']));


		return $data;
	}


	public function to_anchor($anchor) {
		return empty($anchor) ? '' : "<a id=\"$anchor\"></a>";
	}

}
