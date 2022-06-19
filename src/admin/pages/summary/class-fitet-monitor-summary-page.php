<?php

require_once FITET_MONITOR_DIR . 'admin/includes/class-fitet-monitor-page.php';
require_once FITET_MONITOR_DIR . 'admin/components/table/class-fitet-monitor-table-component.php';

class Fitet_Monitor_Summary_Page extends Fitet_Monitor_Page {

	public function components() {
		return ['table' => new Fitet_Monitor_Table_Component($this->version)];
	}

	public function initialize_data() {
		$messagePool = '';
		if (isset($_GET['message']) && 'deleted' === $_GET['message']) {
			$messagePool = '<div id="message" class="updated notice is-dismissible"><p>Delete operation completed</p></div>';
		}
		if (isset($_GET['message']) && 'added' === $_GET['message']) {
			$messagePool = '<div id="message" class="updated notice is-dismissible"><p>Club added successfully</p></div>';
		}
		return [
			'pageTitle' => __(get_admin_page_title()),
			'addButton' => __('Add Club', 'plugin'),
			'messagePool' => $messagePool,
			'table' => $this->components['table']->render([
					'contentino' => 'ssss!!! $$$',
					'betas' => 3,
				]
			)];
	}

	public function render_page() {
		echo parent::render();
		//$this->table->display();
	}


}
