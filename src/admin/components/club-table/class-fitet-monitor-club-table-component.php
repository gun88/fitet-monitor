<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'admin/components/club-table/club-cell/class-fitet-monitor-club-wp-cell-component.php';
require_once FITET_MONITOR_DIR . 'admin/includes/class-fitet-monitor-wp-table.php';

class Fitet_Monitor_Club_Table_Component extends Fitet_Monitor_Component {


	private $columns = [
		'cb' => '<input type="checkbox" />',
		'club' => 'Club',
		'lastUpdate' => 'Last Update',
	];

	private $bulk_actions = [
		'delete' => 'Delete',
		'update' => "Update"
	];

	public function initialize() {
		parent::initialize();
		$this->columns['club'] = __('Club', 'fitet-monitor');
		$this->columns['lastUpdate'] = __('Last Update', 'fitet-monitor');

		$this->bulk_actions['delete'] = __('Delete', 'fitet-monitor');
		$this->bulk_actions['update'] = __('Update', 'fitet-monitor');

	}


	public function components() {
		return ['clubCell' => new Fitet_Monitor_Club_Wp_Cell_Component($this->plugin_name, $this->version)];
	}

	public function process_data($data) {

		$wp_table = new Fitet_Monitor_Wp_Table();
		$wp_table->set_bulk_actions($this->bulk_actions);
		$wp_table->set_columns($this->columns);

		$items = array_map(function ($row) use ($wp_table) {

			$club_code = $row['clubCode'];
			$last_update = !empty($row['lastUpdate']) ? $row['lastUpdate'] : "N/A";

			$row['parentTable'] = $wp_table;

			return [
				'cb' => "<input type='checkbox name='clubCode[] value='$club_code' class='fm-club-table-cb'/>",
				'club' => $this->components['clubCell']->render($row),
				'clubCode' => $club_code,
				'lastUpdate' => $last_update,
			];
		}, $data);


		$wp_table->prepare_items($items);

		return ['table' => $wp_table->display()];
	}


}
