<?php
require_once('simpletest/autorun.php');

class UnitTests extends TestSuite {
	function __construct() {
		parent::__construct();
		$this->collect(dirname(__FILE__) . '/tests', new SimplePatternCollector('/_test.php/'));
	}
}
?>