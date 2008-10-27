<?php
require_once('simpletest/unit_tester.php');
require_once('simpletest/reporter.php');

$test = new TestSuite('Unit tests');
$test->addFile(dirname(__FILE__) . '/locator_test.php');
$test->addFile(dirname(__FILE__) . '/phemto_test.php');
$test->addFile(dirname(__FILE__) . '/lazy_test.php');
if (TextReporter::inCli()) {
	return $test->run(new TextReporter()) ? 0 : 1;
}
$test->run(new HtmlReporter());
?>