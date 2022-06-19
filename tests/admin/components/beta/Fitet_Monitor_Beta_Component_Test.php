<?php

require_once FITET_MONITOR_DIR . 'admin/components/beta/class-fitet-monitor-beta-component.php';
require_once TEST_DIR . 'util/Fitet_Monitor_Component_Wrapper.php';
require_once TEST_DIR . 'util/Wordpress_Double.php';

/**
 * Sample test case.
 */
class Fitet_Monitor_Beta_Component_Test extends Fitet_Monitor_Test_Case {

	/**
	 * @var Fitet_Monitor_Beta_Component
	 */
	private $fitet_monitor_beta_component;

	protected function post_construct() {
		$this->fitet_monitor_beta_component = new Fitet_Monitor_Beta_Component($this->plugin_name, 'x');
		$this->fitet_monitor_beta_component->initialize();
	}

	/** @test */
	public function shouldRenderComponent() {
		$inner = 123;
		$output = Fitet_Monitor_Component_Wrapper::render_wrapper($this->fitet_monitor_beta_component, ['innerBeta' => $inner]);
		$this->assertStringContainsString("I am beta component showing: $inner", $output);

	}
}
