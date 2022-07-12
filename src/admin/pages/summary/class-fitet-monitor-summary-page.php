<?php

require_once FITET_MONITOR_DIR . 'admin/includes/class-fitet-monitor-page.php';
require_once FITET_MONITOR_DIR . 'admin/components/club-table/class-fitet-monitor-club-table-component.php';

class Fitet_Monitor_Summary_Page extends Fitet_Monitor_Page {

	private $clubs;

	public function __construct($plugin_name, $version, $clubs) {
		parent::__construct($plugin_name, $version);
		$this->clubs = $clubs;
	}

	public function components() {
		return ['table' => new Fitet_Monitor_Club_Table_Component($this->plugin_name, $this->version)];
	}

	public function initialize_data() {

		return [
			'pageTitle' => __(get_admin_page_title(), 'fitet-monitor'),
			'addButton' => __('Add Club', 'fitet-monitor'),
			'messagePool' => $this->prepare_messages(),
			'table' => $this->components['table']->render($this->clubs)];
	}

	public function prepare_messages() {
		$messagePool = '';
		if (isset($_GET['message']) && 'deleted' === $_GET['message']) {
			$messagePool = '<div id="message" class="updated notice is-dismissible"><p>' . __('Delete operation completed', 'fitet-monitor') . '</p></div>';
		}
		if (isset($_GET['message']) && 'added' === $_GET['message']) {
			$messagePool = '<div id="message" class="updated notice is-dismissible"><p>' . __('Club added successfully', 'fitet-monitor') . '</p></div>';
		}
		if (isset($_GET['message']) && 'edited' === $_GET['message']) {
			$messagePool = '<div id="message" class="updated notice is-dismissible"><p>' . __('Club edited successfully', 'fitet-monitor') . '</p></div>';
		}
		return $messagePool;
	}


}
