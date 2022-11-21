<?php

require_once __DIR__ . '/../../vendor/phpunit/phpunit/src/Framework/TestCase.php';

use PHPUnit\Framework\TestCase;


class Fitet_Monitor_Test_Case extends TestCase {

	public function __construct($name = null, $data = [], $dataName = '') {
		parent::__construct($name, $data, $dataName);
		$this->post_construct();
	}

	protected function post_construct() {}

}
