<?php

require_once FITET_MONITOR_DIR . 'admin/includes/class-fitet-monitor-page.php';
require_once FITET_MONITOR_DIR . 'admin/components/table/class-fitet-monitor-table-component.php';

class Fitet_Monitor_Summary_Page extends Fitet_Monitor_Page {

	public function components() {
		return ['table' => new Fitet_Monitor_Table_Component($this->plugin_name, $this->version)];
	}

	public function initialize_data() {
		$data = [];
		for ($club_code = 0; $club_code < 10; $club_code++) {
			$data [] = [
				'clubCode' => $club_code,
				'clubName' => "Club $club_code",
				'lastUpdate' => 'Last Update',
			];
		}

		$messagePool = $this->prepare_messages();
		return [
			'pageTitle' => __(get_admin_page_title(), 'fitet-monitor'),
			'addButton' => __('Add Club', 'fitet-monitor'),
			'messagePool' => $messagePool,
			'table' => $this->components['table']->render($data)];
	}

	public function prepare_messages() {
		$messagePool = '';
		if (isset($_GET['message']) && 'deleted' === $_GET['message']) {
			$messagePool = '<div id="message" class="updated notice is-dismissible"><p>Delete operation completed</p></div>';
		}
		if (isset($_GET['message']) && 'added' === $_GET['message']) {
			$messagePool = '<div id="message" class="updated notice is-dismissible"><p>Club added successfully</p></div>';
		}
		return $messagePool;
	}


}
