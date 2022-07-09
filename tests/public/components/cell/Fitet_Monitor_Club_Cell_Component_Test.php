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
	}

	/** @test */
	public function main_test() {
		$expected = "<div class='fm-club-cell'><span class='fm-club-image'><img src='/fitet-monitor-no-logo.svg' alt='N/A' /></span><span class='fm-club-name'>N/A</span></div>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, []);
		$this->assertXmlStringEqualsXmlString($expected, $actual);;

		$expected = "<div class='fm-club-cell'><span class='fm-club-image'><img src='http://portale.fitet.org/images/societa/5.jpg' alt='N/A' onError='this.onerror=null;this.src=\"/fitet-monitor-no-logo.svg\";'/></span><span class='fm-club-name'>N/A</span></div>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['clubCode' => '5']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<div class='fm-club-cell'><span class='fm-club-image'><img src='/fitet-monitor-no-logo.svg' alt='My Club'/></span><span class='fm-club-name'>My Club</span></div>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['clubName' => 'My Club']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<div class='fm-club-cell'><span class='fm-club-image'><img src='http://portale.fitet.org/images/societa/5.jpg' alt='My Club' onError='this.onerror=null;this.src=\"/fitet-monitor-no-logo.svg\";'/></span><span class='fm-club-name'>My Club</span></div>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['clubCode' => '5', 'clubName' => 'My Club']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<div class='fm-club-cell'><a href='/club.html' class='fm-club-image'><img src='/fitet-monitor-no-logo.svg' alt='N/A'/></a><a href='/club.html' class='fm-club-name'>N/A</a></div>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['clubPageUrl' => '/club.html']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<div class='fm-club-cell'><a href='/club.html' class='fm-club-image'><img src='http://portale.fitet.org/images/societa/5.jpg' alt='N/A' onError='this.onerror=null;this.src=\"/fitet-monitor-no-logo.svg\";'/></a><a href='/club.html' class='fm-club-name'>N/A</a></div>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['clubCode' => '5', 'clubPageUrl' => '/club.html']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<div class='fm-club-cell'><a href='/club.html' class='fm-club-image'><img src='/fitet-monitor-no-logo.svg' alt='My Club'/></a><a href='/club.html' class='fm-club-name'>My Club</a></div>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['clubName' => 'My Club', 'clubPageUrl' => '/club.html']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<div class='fm-club-cell'><a class='fm-club-image' href='/club.html'><img src='http://portale.fitet.org/images/societa/5.jpg' alt='My Club' onError='this.onerror=null;this.src=\"/fitet-monitor-no-logo.svg\";'/></a><a class='fm-club-name' href='/club.html'>My Club</a></div>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['clubCode' => '5', 'clubName' => 'My Club', 'clubPageUrl' => '/club.html']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);
	}

}
