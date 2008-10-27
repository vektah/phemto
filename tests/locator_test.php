<?php
require_once('simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../locator.php');

class AnyOldThing {
}

class WhenInstatiatingClasses extends UnitTestCase {

    function testCorrectClassIsInstantiated() {
		$locator = new Locator('AnyOldThing');
		$this->assertIsa($locator->instantiate(array()), 'AnyOldThing');
    }

	function testInstancesAreClones() {
		$locator = new Locator('AnyOldThing');
		$this->assertClone(
				$locator->instantiate(array()),
				$locator->instantiate(array()));
	}
}

class WhenInstatiatingSingletons extends UnitTestCase {

    function setUp() {
        $this->locator = new Singleton('AnyOldThing');
    }

    function testLocatorCanCreateClass() {
		$this->locator = new Singleton('AnyOldThing');
		$this->assertIsa($this->locator->instantiate(array()), 'AnyOldThing');
    }

	function testOnlyOneInstanceCreated() {
		$this->assertIdentical(
				$this->locator->instantiate(array()),
				$this->locator->instantiate(array()));
	}
}
?>