<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'public/components/table/class-fitet-monitor-table-component.php';

class Fitet_Monitor_Team_Standings_Component extends Fitet_Monitor_Component {
	protected function components() {
		return [
			'table' => new Fitet_Monitor_Table_Component($this->plugin_name, $this->version),
			'teamCell' => new Fitet_Monitor_Team_Cell_Component($this->plugin_name, $this->version),
		];
	}


	protected function process_data($data) {
		return [
			'teamStandingsLabel' => __("Standings"),
			'promoLabel' => __("Promotion"),
			'playOffLabel' => __("Playoff"),
			'playOutLabel' => __("Playout"),
			'relegationLabel' => __("Relegation"),
			'table' => $this->table($data),
		];
	}

	private function table($data) {
		return $this->components['table']->render(
			[
				'name' => 'fm-team-standings',
				'paginate' => false,
				'search' => false,
				'columns' => $this->columns(),
				'sort' => $this->sort(),
				'rows' => $this->rows($data),
			]
		);
	}

	private function columns() {

		return [
			'ranking' => '#',
			// "teamId" => __('teamId'),
			"team" => __('Team'),
			// "clubCode" => __('clubCode'),
			// "clubName" => __('clubName'),
			// "teamStatus" => __('teamStatus'),
			"points" => __('Points'),
			"id" => __('ID'),
			"iv" => __('IV'),
			"ipa" => __('IPA'),
			"ip" => __('IP'),
			"pav" => __('PAV'),
			"pap" => __('PAP'),
			"sv" => __('SV'),
			"sp" => __('SP'),
			"pv" => __('PV'),
			"pp" => __('PP'),
			"pe" => __('PE'),


		];
	}

	private function sort() {
		return [
			'ranking' => 'number',
			"points" => 'number',
			"id" => 'number',
			"iv" => 'number',
			"ipa" => 'number',
			"ip" => 'number',
			"pav" => 'number',
			"pap" => 'number',
			"sv" => 'number',
			"sp" => 'number',
			"pv" => 'number',
			"pp" => 'number',
			"pe" => 'number',
		];
	}

	private function rows($data) {
		return array_map(function ($standing) {
			$standing['team'] = $this->components['teamCell']->render([
				'clubCode' => $standing['clubCode'],
				'teamName' => $standing['teamName'],
				'teamPageUrl' => $standing['teamPageUrl'],
				'clubLogo' => $standing['clubLogo'],
			]);
			unset($standing['teamName']);
			$standing['_rowClass'] = 'fm-team-status-' . $standing['teamStatus'];
			if ($standing['mainTeam']) {
				$standing['_rowClass'] .= ' fm-team-main-team';

			}
			return $standing;
		}, $data);
	}


}
