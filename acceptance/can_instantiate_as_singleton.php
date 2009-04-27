<?php
require_once('simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../phemto.php');

class CreateMeOnce { }

class CanInstantiateObjectsAsSingletons extends UnitTestCase {
    function testSameInstanceCanBeReusedWithinFactory() {
        $injector = new Phemto();
        $injector->willUse(new Reused('CreateMeOnce'));
        $this->assertSame(
                $injector->create('CreateMeOnce'),
                $injector->create('CreateMeOnce'));
    }
}
?>