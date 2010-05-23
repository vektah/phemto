<?php
require_once(dirname(__FILE__) . '/simpletest/autorun.php');

class UnitTests extends TestSuite {
	function __construct() {
		parent::__construct();
		$this->collect(dirname(__FILE__) . '/unit', new SimplePatternCollector('/_test.php/'));
	}
}
?>