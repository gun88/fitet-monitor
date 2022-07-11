<?php

require_once FITET_MONITOR_DIR . 'public/components/cells/class-fitet-monitor-team-cell-component.php';
require_once TEST_DIR . 'util/Fitet_Monitor_Component_Wrapper.php';
require_once TEST_DIR . 'util/Wordpress_Double.php';

/**
 * Sample test case.
 */
class Fitet_Monitor_Team_Cell_Component_Test extends Fitet_Monitor_Test_Case {

	/**
	 * @var Fitet_Monitor_Team_Cell_Component
	 */
	private $component;

	protected function post_construct() {
		$this->component = new Fitet_Monitor_Team_Cell_Component('fitet-monitor', 'unit-test');
		$this->component->initialize();
		Fitet_Monitor_Component_Wrapper::mock_render($this->component);
	}

	/** @test */
	public function main_test() {
		$expected = "<div class='fm-team-cell'>__mocked__<span class='fm-team-name'>N/A</span></div>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, []);
		$this->assertXmlStringEqualsXmlString($expected, $actual);;

		$expected = "<div class='fm-team-cell'>__mocked__<span class='fm-team-name'>My Player</span></div>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['teamName' => 'My Player']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<div class='fm-team-cell'>__mocked__<a href='/team.html' class='fm-team-name'>N/A</a></div>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['teamPageUrl' => '/team.html']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<div class='fm-team-cell'>__mocked__<a href='/team.html' class='fm-team-name'>My Player</a></div>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['teamName' => 'My Player', 'teamPageUrl' => '/team.html']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);
	}

}
