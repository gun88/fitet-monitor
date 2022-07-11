<?php

require_once FITET_MONITOR_DIR . 'public/components/cells/class-fitet-monitor-club-cell-component.php';
require_once TEST_DIR . 'util/Fitet_Monitor_Component_Wrapper.php';
require_once TEST_DIR . 'util/Wordpress_Double.php';

/**
 * Sample test case.
 */
class Fitet_Monitor_Club_Cell_Component_Test extends Fitet_Monitor_Test_Case {

	/**
	 * @var Fitet_Monitor_Club_Cell_Component
	 */
	private $component;

	protected function post_construct() {
		$this->component = new Fitet_Monitor_Club_Cell_Component('fitet-monitor', 'unit-test');
		$this->component->initialize();
		Fitet_Monitor_Component_Wrapper::mock_render($this->component);
	}

	/** @test */
	public function main_test() {
		$expected = "<div class='fm-club-cell'>__mocked__<span class='fm-club-name'>N/A</span></div>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, []);
		$this->assertXmlStringEqualsXmlString($expected, $actual);;

		$expected = "<div class='fm-club-cell'>__mocked__<span class='fm-club-name'>My Player</span></div>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['clubName' => 'My Player']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<div class='fm-club-cell'>__mocked__<a href='/club.html' class='fm-club-name'>N/A</a></div>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['clubPageUrl' => '/club.html']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<div class='fm-club-cell'>__mocked__<a href='/club.html' class='fm-club-name'>My Player</a></div>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['clubName' => 'My Player', 'clubPageUrl' => '/club.html']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);
	}

}
