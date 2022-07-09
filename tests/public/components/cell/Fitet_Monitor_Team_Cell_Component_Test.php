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
	}

	/** @test */
	public function main_test() {
		$expected = "<div class='fm-team-cell'><span class='fm-team-image'><img src='/fitet-monitor-no-logo.svg' alt='N/A' /></span><span class='fm-team-name'>N/A</span></div>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, []);
		$this->assertXmlStringEqualsXmlString($expected, $actual);;

		$expected = "<div class='fm-team-cell'><span class='fm-team-image'><img src='http://portale.fitet.org/images/societa/5.jpg' alt='N/A' onError='this.onerror=null;this.src=\"/fitet-monitor-no-logo.svg\";'/></span><span class='fm-team-name'>N/A</span></div>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['clubCode' => '5']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<div class='fm-team-cell'><span class='fm-team-image'><img src='/fitet-monitor-no-logo.svg' alt='My Team'/></span><span class='fm-team-name'>My Team</span></div>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['teamName' => 'My Team']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<div class='fm-team-cell'><span class='fm-team-image'><img src='http://portale.fitet.org/images/societa/5.jpg' alt='My Team' onError='this.onerror=null;this.src=\"/fitet-monitor-no-logo.svg\";'/></span><span class='fm-team-name'>My Team</span></div>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['clubCode' => '5', 'teamName' => 'My Team']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<div class='fm-team-cell'><a href='/team.html' class='fm-team-image'><img src='/fitet-monitor-no-logo.svg' alt='N/A'/></a><a href='/team.html' class='fm-team-name'>N/A</a></div>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['teamPageUrl' => '/team.html']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<div class='fm-team-cell'><a href='/team.html' class='fm-team-image'><img src='http://portale.fitet.org/images/societa/5.jpg' alt='N/A' onError='this.onerror=null;this.src=\"/fitet-monitor-no-logo.svg\";'/></a><a href='/team.html' class='fm-team-name'>N/A</a></div>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['clubCode' => '5', 'teamPageUrl' => '/team.html']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<div class='fm-team-cell'><a href='/team.html' class='fm-team-image'><img src='/fitet-monitor-no-logo.svg' alt='My Team'/></a><a href='/team.html' class='fm-team-name'>My Team</a></div>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['teamName' => 'My Team', 'teamPageUrl' => '/team.html']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<div class='fm-team-cell'><a class='fm-team-image' href='/team.html'><img src='http://portale.fitet.org/images/societa/5.jpg' alt='My Team' onError='this.onerror=null;this.src=\"/fitet-monitor-no-logo.svg\";'/></a><a class='fm-team-name' href='/team.html'>My Team</a></div>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['clubCode' => '5', 'teamName' => 'My Team', 'teamPageUrl' => '/team.html']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);
	}

}
