<?php

require_once FITET_MONITOR_DIR . 'admin/components/class-fitet-monitor-component.php';


class Fitet_Monitor_Beta_Component extends Fitet_Monitor_Component {

	public function render($data = []) { ?>
		<div class="fm-beta-component">
		<p>I am beta component showing: <?php echo($data['innerBeta']); ?></p>
		<p>I have <b>no</b> sub components!</p>
	<?php }

}
