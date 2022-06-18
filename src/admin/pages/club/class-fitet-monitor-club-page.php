<?php

require_once FITET_MONITOR_DIR . 'admin/pages/class-fitet-monitor-page.php';

class Fitet_Monitor_Club_Page extends Fitet_Monitor_Page {

	private $club_code;
	private $plugin_name;

	public function __construct($version, $plugin_name, $club_code) {
		parent::__construct($version);
		$this->club_code = $club_code;
		$this->plugin_name = $plugin_name;
	}

	public function render_page() {

		?>
		<form method="post">
			<h1 class="wp-heading-inline"><?php _e("Club Page", $this->plugin_name, 'fitet-monitor'); ?></h1>
			<p><?php _e("Custom!", $this->plugin_name, 'fitet-monitor'); ?></p>
			<hr class="wp-header-end">
			<input type="hidden" name="page" value="fitet-monitor">
			<input type="hidden" name="action" value="save">
			<input type="text" name="clubCode" value="<?php echo $this->club_code; ?>"/>
			<input type="submit" value="<?php _e("Save", 'fitet-monitor'); ?>">
		</form>
		<?php
	}
}
