<?php
require_once('simpletest/autorun.php');

class AcceptanceTests extends TestSuite {
	function __construct() {
		parent::__construct();
		$this->collect(dirname(__FILE__) . '/acceptance', new SimplePatternCollector('/\.php/'));
	}
}
?>