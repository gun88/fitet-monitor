<?php

require_once FITET_MONITOR_DIR . 'admin/components/beta/class-fitet-monitor-beta-component.php';

/**
 * Sample test case.
 */
class Fitet_Monitor_Beta_Component_Test extends Fitet_Monitor_Test_Case {

	/**
	 * @var Fitet_Monitor_Beta_Component
	 */
	private $fitet_monitor_beta_component;

	protected function post_construct() {
		$this->fitet_monitor_beta_component = new Fitet_Monitor_Beta_Component('x');
	}

	/** @test */
	public function shouldRenderComponent() {
		$inner = 123;
		ob_start();
		$this->fitet_monitor_beta_component->render(['innerBeta' => $inner]);
		$output = ob_get_clean();
		$this->assertStringContainsString("I am beta component showing: $inner", $output);

	}
}
