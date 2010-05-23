<?php
require_once(dirname(__FILE__) . '/simpletest/autorun.php');

class AcceptanceTests extends TestSuite {
    function __construct() {
        parent::__construct();
        $this->collect(dirname(__FILE__) . '/acceptance', new SimplePatternCollector('/\.php/'));
    }
}
?>