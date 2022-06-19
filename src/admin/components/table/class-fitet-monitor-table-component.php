<?php

require_once FITET_MONITOR_DIR . 'common/includes/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'admin/components/beta/class-fitet-monitor-beta-component.php';
require_once FITET_MONITOR_DIR . 'admin/components/table/class-wp-list-table.php';

class Fitet_Monitor_Table_Component extends Fitet_Monitor_Component {

	public $items;


	public function components() {
		return ['beta' => new Fitet_Monitor_Beta_Component($this->version)];
	}

	public function process_data($data) {
		$WP_List_Table2 = new WP_List_Table2();
		ob_start();
		try {
			$WP_List_Table2->display();
		} catch (Throwable $e) {
			$data['error'] = ($e->getMessage());

		}
		return $data;
	}
}
