<?php

require_once FITET_MONITOR_DIR . 'admin/pages/class-fitet-monitor-page.php';
require_once FITET_MONITOR_DIR . 'admin/components/alfa/class-fitet-monitor-alfa-component.php';

class Fitet_Monitor_Summary_Page extends Fitet_Monitor_Page {

	public function components() {
		$component = [];
		$component['alfa'] = new Fitet_Monitor_Alfa_Component($this->version);
		$component['beta'] = new Fitet_Monitor_Beta_Component($this->version);
		return $component;
	}

	public function render_page() {
		$data = [];
		?>
		<div class="fm-summary-page" class="wrap">
			<p>Hello!</p>
			<p><?php echo "The time is " . date("h:i:sa"); ?></p>
			<?php ($this->components['alfa'])->render($data); ?>
		</div>
		<?php
	}

}
