<?php

require_once FITET_MONITOR_DIR . 'public/components/common/class-fitet-monitor-team-image-component.php';
require_once TEST_DIR . 'util/Fitet_Monitor_Component_Wrapper.php';
require_once TEST_DIR . 'util/Wordpress_Double.php';

/**
 * Sample test case.
 */
class Fitet_Monitor_Team_Image_Component_Test extends Fitet_Monitor_Test_Case {

	/**
	 * @var Fitet_Monitor_Team_Image_Component
	 */
	private $component;

	protected function post_construct() {
		$this->component = new Fitet_Monitor_Team_Image_Component('fitet-monitor', 'unit-test');
		$this->component->initialize();
	}

	/** @test */
	public function main_test() {
		$expected = "<span class='fm-team-image'><img src='/fitet-monitor-no-club-image.svg' alt='N/A'/></span>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, []);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<span class='fm-team-image'><img src='http://portale.fitet.org/images/societa/1.jpg' alt='N/A' onError='this.onerror=null;this.src=\"/fitet-monitor-no-club-image.svg\";'/></span>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['clubCode' => '1']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<span class='fm-team-image'><img src='/fitet-monitor-no-club-image.svg' alt='Foo Bar'/></span>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['teamName' => 'Foo Bar']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<span class='fm-team-image'><img src='http://portale.fitet.org/images/societa/1.jpg' alt='Foo Bar' onError='this.onerror=null;this.src=\"/fitet-monitor-no-club-image.svg\";'/></span>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['clubCode' => '1', 'teamName' => 'Foo Bar']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<a class='fm-team-image' href='/club.html'><img src='/fitet-monitor-no-club-image.svg' alt='N/A'/></a>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['teamPageUrl' => '/club.html']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<a class='fm-team-image' href='/club.html'><img src='http://portale.fitet.org/images/societa/1.jpg' alt='N/A' onError='this.onerror=null;this.src=\"/fitet-monitor-no-club-image.svg\";'/></a>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['clubCode' => '1', 'teamPageUrl' => '/club.html']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<a class='fm-team-image' href='/club.html'><img src='/fitet-monitor-no-club-image.svg' alt='Foo Bar'/></a>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['teamName' => 'Foo Bar', 'teamPageUrl' => '/club.html']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<a class='fm-team-image' href='/club.html'><img src='http://portale.fitet.org/images/societa/1.jpg' alt='Foo Bar' onError='this.onerror=null;this.src=\"/fitet-monitor-no-club-image.svg\";'/></a>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['clubCode' => '1', 'teamName' => 'Foo Bar', 'teamPageUrl' => '/club.html']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

	}

	/** @test */
	public function team_image_test() {
		$expected = "<span class='fm-team-image'><img src='customLogo.jpg' onError='this.onerror=null;this.src=\"/fitet-monitor-no-club-image.svg\";' alt='N/A'/></span>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['clubLogo'=>'customLogo.jpg']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<span class='fm-team-image'><img src='customLogo.jpg' onError='this.onerror=null;this.src=\"/fitet-monitor-no-club-image.svg\";' alt='N/A'/></span>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['clubCode' => '1','clubLogo'=>'customLogo.jpg']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<span class='fm-team-image'><img src='customLogo.jpg' onError='this.onerror=null;this.src=\"/fitet-monitor-no-club-image.svg\";' alt='Foo Bar'/></span>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['teamName' => 'Foo Bar','clubLogo'=>'customLogo.jpg']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<span class='fm-team-image'><img src='customLogo.jpg' onError='this.onerror=null;this.src=\"/fitet-monitor-no-club-image.svg\";' alt='Foo Bar'/></span>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['clubCode' => '1', 'teamName' => 'Foo Bar','clubLogo'=>'customLogo.jpg']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<a class='fm-team-image' href='/club.html'><img src='customLogo.jpg' onError='this.onerror=null;this.src=\"/fitet-monitor-no-club-image.svg\";' alt='N/A'/></a>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['teamPageUrl' => '/club.html','clubLogo'=>'customLogo.jpg']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<a class='fm-team-image' href='/club.html'><img src='customLogo.jpg' onError='this.onerror=null;this.src=\"/fitet-monitor-no-club-image.svg\";' alt='N/A'/></a>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['clubCode' => '1', 'teamPageUrl' => '/club.html','clubLogo'=>'customLogo.jpg']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<a class='fm-team-image' href='/club.html'><img src='customLogo.jpg' onError='this.onerror=null;this.src=\"/fitet-monitor-no-club-image.svg\";' alt='Foo Bar'/></a>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['teamName' => 'Foo Bar', 'teamPageUrl' => '/club.html','clubLogo'=>'customLogo.jpg']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<a class='fm-team-image' href='/club.html'><img src='customLogo.jpg' onError='this.onerror=null;this.src=\"/fitet-monitor-no-club-image.svg\";' alt='Foo Bar'/></a>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['clubCode' => '1', 'teamName' => 'Foo Bar', 'teamPageUrl' => '/club.html','clubLogo'=>'customLogo.jpg']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

	}

}
