<?php

require_once FITET_MONITOR_DIR . 'public/components/common/class-fitet-monitor-club-image-component.php';
require_once TEST_DIR . 'util/Fitet_Monitor_Component_Wrapper.php';
require_once TEST_DIR . 'util/Wordpress_Double.php';

/**
 * Sample test case.
 */
class Fitet_Monitor_Club_Image_Component_Test extends Fitet_Monitor_Test_Case {

	/**
	 * @var Fitet_Monitor_Club_Image_Component
	 */
	private $component;

	protected function post_construct() {
		$this->component = new Fitet_Monitor_Club_Image_Component('fitet-monitor', 'unit-test');
		$this->component->initialize();
	}

	/** @test */
	public function main_test() {
		$expected = "<span class='fm-club-image'><img src='/fitet-monitor-no-logo.svg' alt='N/A'/></span>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, []);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<span class='fm-club-image'><img src='http://portale.fitet.org/images/societa/1.jpg' alt='N/A' onError='this.onerror=null;this.src=\"/fitet-monitor-no-logo.svg\";'/></span>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['clubCode' => '1']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<span class='fm-club-image'><img src='/fitet-monitor-no-logo.svg' alt='Foo Bar'/></span>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['clubName' => 'Foo Bar']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<span class='fm-club-image'><img src='http://portale.fitet.org/images/societa/1.jpg' alt='Foo Bar' onError='this.onerror=null;this.src=\"/fitet-monitor-no-logo.svg\";'/></span>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['clubCode' => '1', 'clubName' => 'Foo Bar']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<a class='fm-club-image' href='/club.html'><img src='/fitet-monitor-no-logo.svg' alt='N/A'/></a>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['clubPageUrl' => '/club.html']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<a class='fm-club-image' href='/club.html'><img src='http://portale.fitet.org/images/societa/1.jpg' alt='N/A' onError='this.onerror=null;this.src=\"/fitet-monitor-no-logo.svg\";'/></a>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['clubCode' => '1', 'clubPageUrl' => '/club.html']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<a class='fm-club-image' href='/club.html'><img src='/fitet-monitor-no-logo.svg' alt='Foo Bar'/></a>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['clubName' => 'Foo Bar', 'clubPageUrl' => '/club.html']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<a class='fm-club-image' href='/club.html'><img src='http://portale.fitet.org/images/societa/1.jpg' alt='Foo Bar' onError='this.onerror=null;this.src=\"/fitet-monitor-no-logo.svg\";'/></a>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['clubCode' => '1', 'clubName' => 'Foo Bar', 'clubPageUrl' => '/club.html']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

	}

}
