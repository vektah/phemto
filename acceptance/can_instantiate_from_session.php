<?php
require_once('simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../phemto.php');

class SerialiseMe { }

class CanInstantiateFromSessions extends UnitTestCase {
    function tearDown() {
        $_SESSION['slot'] = false;
    }

    function testSessionableInstanceWrittenToSession() {
        $injector = new Phemto();
        $injector->willUse(new Sessionable('slot', 'SerialiseMe'));
        $_SESSION['slot'] = false;
        $lone = $injector->create('SerialiseMe');
        $this->assertSame($lone, $_SESSION['slot']);
    }

    function testSessionableInstancePulledFromSessionIfExists() {
        $_SESSION['slot'] = new SerialiseMe();
        $injector = new Phemto();
        $injector->willUse(new Sessionable('slot', 'SerialiseMe'));
        $this->assertSame($injector->create('SerialiseMe'), $_SESSION['slot']);
    }
}
?>