<?php

require_once FITET_MONITOR_DIR . 'public/components/common/class-fitet-monitor-player-image-component.php';
require_once TEST_DIR . 'util/Fitet_Monitor_Component_Wrapper.php';
require_once TEST_DIR . 'util/Wordpress_Double.php';

/**
 * Sample test case.
 */
class Fitet_Monitor_Player_Image_Component_Test extends Fitet_Monitor_Test_Case {

	/**
	 * @var Fitet_Monitor_Club_Image_Component
	 */
	private $component;

	protected function post_construct() {
		$this->component = new Fitet_Monitor_Player_Image_Component('fitet-monitor', 'unit-test');
		$this->component->initialize();
	}

	/** @test */
	public function main_test() {
		$expected = "<span class='fm-player-image'><img src='/fitet-monitor-no-player-image.svg' alt='N/A'/></span>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, []);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<span class='fm-player-image'><img src='http://portale.fitet.org/images/atleti/1.jpg' alt='N/A' onError='this.onerror=null;this.src=\"/fitet-monitor-no-player-image.svg\";'/></span>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['playerId' => '1']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<span class='fm-player-image'><img src='/fitet-monitor-no-player-image.svg' alt='Foo Bar'/></span>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['playerName' => 'Foo Bar']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<span class='fm-player-image'><img src='http://portale.fitet.org/images/atleti/1.jpg' alt='Foo Bar' onError='this.onerror=null;this.src=\"/fitet-monitor-no-player-image.svg\";'/></span>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['playerId' => '1', 'playerName' => 'Foo Bar']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<a class='fm-player-image' href='/player.html'><img src='/fitet-monitor-no-player-image.svg' alt='N/A'/></a>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['playerPageUrl' => '/player.html']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<a class='fm-player-image' href='/player.html'><img src='http://portale.fitet.org/images/atleti/1.jpg' alt='N/A' onError='this.onerror=null;this.src=\"/fitet-monitor-no-player-image.svg\";'/></a>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['playerId' => '1', 'playerPageUrl' => '/player.html']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<a class='fm-player-image' href='/player.html'><img src='/fitet-monitor-no-player-image.svg' alt='Foo Bar'/></a>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['playerName' => 'Foo Bar', 'playerPageUrl' => '/player.html']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<a class='fm-player-image' href='/player.html'><img src='http://portale.fitet.org/images/atleti/1.jpg' alt='Foo Bar' onError='this.onerror=null;this.src=\"/fitet-monitor-no-player-image.svg\";'/></a>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['playerId' => '1', 'playerName' => 'Foo Bar', 'playerPageUrl' => '/player.html']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

	}

	/** @test */
	public function player_image_test() {
		$expected = "<span class='fm-player-image'><img src='customLogo.jpg' onError='this.onerror=null;this.src=\"/fitet-monitor-no-player-image.svg\";' alt='N/A'/></span>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['playerImage'=>'customLogo.jpg']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<span class='fm-player-image'><img src='customLogo.jpg' onError='this.onerror=null;this.src=\"/fitet-monitor-no-player-image.svg\";' alt='N/A'/></span>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['clubCode' => '1','playerImage'=>'customLogo.jpg']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<span class='fm-player-image'><img src='customLogo.jpg' onError='this.onerror=null;this.src=\"/fitet-monitor-no-player-image.svg\";' alt='Foo Bar'/></span>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['playerName' => 'Foo Bar','playerImage'=>'customLogo.jpg']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<span class='fm-player-image'><img src='customLogo.jpg' onError='this.onerror=null;this.src=\"/fitet-monitor-no-player-image.svg\";' alt='Foo Bar'/></span>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['clubCode' => '1', 'playerName' => 'Foo Bar','playerImage'=>'customLogo.jpg']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<a class='fm-player-image' href='/club.html'><img src='customLogo.jpg' onError='this.onerror=null;this.src=\"/fitet-monitor-no-player-image.svg\";' alt='N/A'/></a>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['playerPageUrl' => '/club.html','playerImage'=>'customLogo.jpg']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<a class='fm-player-image' href='/club.html'><img src='customLogo.jpg' onError='this.onerror=null;this.src=\"/fitet-monitor-no-player-image.svg\";' alt='N/A'/></a>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['clubCode' => '1', 'playerPageUrl' => '/club.html','playerImage'=>'customLogo.jpg']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<a class='fm-player-image' href='/club.html'><img src='customLogo.jpg' onError='this.onerror=null;this.src=\"/fitet-monitor-no-player-image.svg\";' alt='Foo Bar'/></a>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['playerName' => 'Foo Bar', 'playerPageUrl' => '/club.html','playerImage'=>'customLogo.jpg']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

		$expected = "<a class='fm-player-image' href='/club.html'><img src='customLogo.jpg' onError='this.onerror=null;this.src=\"/fitet-monitor-no-player-image.svg\";' alt='Foo Bar'/></a>";
		$actual = Fitet_Monitor_Component_Wrapper::render_wrapper($this->component, ['clubCode' => '1', 'playerName' => 'Foo Bar', 'playerPageUrl' => '/club.html','playerImage'=>'customLogo.jpg']);
		$this->assertXmlStringEqualsXmlString($expected, $actual);

	}

}
