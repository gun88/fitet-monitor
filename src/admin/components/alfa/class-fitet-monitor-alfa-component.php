<?php

require_once FITET_MONITOR_DIR . 'admin/components/class-fitet-monitor-component.php';
require_once FITET_MONITOR_DIR . 'admin/components/beta/class-fitet-monitor-beta-component.php';


class Fitet_Monitor_Alfa_Component extends Fitet_Monitor_Component {

	public function enqueue_styles() {

		parent::enqueue_styles();
		$additional = plugin_dir_url(__FILE__) . 'additional.css';
		wp_enqueue_style('Zzz_additional.css', $additional, array(), $this->version, 'all');

	}

	public function components() {
		return ['beta' => new Fitet_Monitor_Beta_Component($this->version)];
	}

	public function render($data = []) {
		$data = [];
		$data['contentAlfa'] = 'Alfettino';
		$data['contentBeta'] = [];
		$data['contentBeta']['innerBeta'] = 'innerBetino';
		?>
		<div class="fm-alfa-component">
		<p>I am alfa component showing: <?php echo($data['contentAlfa']); ?></p>
		<p>My sub-component is:</p>
		<?php $this->components['beta']->render($data['contentBeta']); ?>
	<?php }

}
