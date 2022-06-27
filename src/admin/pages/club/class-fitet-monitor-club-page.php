<?php

require_once FITET_MONITOR_DIR . 'admin/includes/class-fitet-monitor-page.php';
require_once FITET_MONITOR_DIR . 'admin/components/club-data/class-fitet-monitor-club-data-component.php';
require_once FITET_MONITOR_DIR . 'admin/components/club-config/class-fitet-monitor-club-config-component.php';

class Fitet_Monitor_Club_Page extends Fitet_Monitor_Page {

	private $club;

	public function __construct($version, $plugin_name, $club) {
		parent::__construct($plugin_name, $version);
		$this->club = $club;
	}

	public function components() {

		return [
			'clubDataComponent' => new Fitet_Monitor_Club_Data_Add_Component($this->plugin_name, $this->version),
			'clubConfigComponent' => new Fitet_Monitor_Club_Config_Component($this->plugin_name, $this->version)
		];
	}

	public function initialize_data() {
		return [
			'title' => __("Club Page", 'fitet-monitor'),
			'action' => $this->club ? 'edit' : 'add',
			'advancedLabel' => __('Advanced Configuration'),
			'clubDataComponent' => $this->components['clubDataComponent']->render($this->club),
			'clubConfigComponent' => $this->components['clubConfigComponent']->render($this->club),
			'messagePool' => $this->prepare_messages(),
			'submitButton' => get_submit_button(__('Save', 'fitet-monitor'), 'primary large', 'submit', true, $this->club == null ? 'disabled' : ''),
		];
	}


	public function prepare_messages() {
		$messagePool = '';
		if (isset($_GET['message']) && 'already_exist' === $_GET['message']) {
			$messagePool = '<div id="message" class="notice notice-error is-dismissible"><p>' . __('Chosen club already exist') . '</p></div>';
		}
		return $messagePool;
	}
}
