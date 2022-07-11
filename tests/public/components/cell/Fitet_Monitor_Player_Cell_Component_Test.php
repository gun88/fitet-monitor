<?php

require_once FITET_MONITOR_DIR . 'public/components/cells/class-fitet-monitor-player-cell-component.php';
require_once TEST_DIR . 'util/Fitet_Monitor_Component_Wrapper.php';
require_once TEST_DIR . 'util/Wordpress_Double.php';

/**
 * Sample test case.
 */
class Fitet_Monitor_Player_Cell_Component_Test extends Fitet_Monitor_Test_Case {

	/**
	 * @var Fitet_Monitor_Player_Cell_Component
	 */
	private $component;

	protected function post_construct() {
		$this->component = new Fitet_Monitor_Player_Cell_Component('fitet-monitor', 'unit-test');
		$this->component->initialize();
		Fitet_Monitor_Component_Wrapper::mock_render($this->component);
	}

	/** @test */
	public function main_test() {
		$expected = "<div class='fm-player-cell'>__mocked__<span class='fm-player-name'>N/A</span></div>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, []);
		$this->assertXmlStringEqualsXmlString($expected, $actual);;

		$expected = "<div class='fm-player-cell'>__mocked__<span class='fm-player-name'>My Player</span></div>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['playerName' => 'My Player']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<div class='fm-player-cell'>__mocked__<a href='/player.html' class='fm-player-name'>N/A</a></div>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['playerPageUrl' => '/player.html']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<div class='fm-player-cell'>__mocked__<a href='/player.html' class='fm-player-name'>My Player</a></div>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['playerName' => 'My Player', 'playerPageUrl' => '/player.html']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

	}

}
