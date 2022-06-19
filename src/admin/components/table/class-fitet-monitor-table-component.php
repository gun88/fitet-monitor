<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'admin/components/beta/class-fitet-monitor-beta-component.php';
require_once FITET_MONITOR_DIR . 'admin/includes/class-fitet-monitor-wp-table.php';

class Fitet_Monitor_Table_Component extends Fitet_Monitor_Component {


	private $columns = [
		'cb' => '<input type="checkbox" />',
		'club' => 'Club',
		'configuration' => 'Configuration',
		'lastUpdate' => 'Last Update',
	];

	private $bulk_actions = [
		'delete' => 'Delete',
		'update' => "Update"
	];


	public function components() {
		return ['beta' => new Fitet_Monitor_Beta_Component($this->version)];
	}

	public function process_data($data) {

		$data = array_map(function ($row) {
			$club_code = $row['clubCode'];
			$club_name = $row['clubName'];
			$last_update = $row['lastUpdate'];
			return [
				'cb' => "<input type='checkbox name='clubCode[] value='$club_code' class='fm-club-table-cb'/>",
				'club' => "$club_name",
				'clubCode' => $club_code,
				'configuration' => "<b>Configuration $club_code</b>",
				'lastUpdate' => $last_update,
			];
		}, $data);

		$wp_table = new Fitet_Monitor_Wp_Table();
		$wp_table->set_bulk_actions($this->bulk_actions);
		$wp_table->set_columns($this->columns);
		$wp_table->prepare_items($data);

		return ['table' => $wp_table->display()];
	}


}
