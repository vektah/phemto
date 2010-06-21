<?php
require_once(dirname(__FILE__) . '/../simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../phemto.php');

class SerialiseMe { }

class HoldsSessionable {
    public $dependency;
    
    function __construct(SerialiseMe $dependency) {
        $this->dependency = $dependency;
    }
}

class CanInstantiateFromSessions extends UnitTestCase {
    function tearDown() {
        $_SESSION['slot'] = false;
        $_SESSION['SerialiseMe'] = false;
    }

    function testSessionableInstanceWrittenToSession() {
        $injector = new Phemto();
        $injector->willUse(new Sessionable('SerialiseMe'));
        $this->assertSame($injector->create('SerialiseMe'), $_SESSION['SerialiseMe']);
    }

    function testCanOverideSessionSlot() {
        $injector = new Phemto();
        $injector->willUse(new Sessionable('SerialiseMe', 'slot'));
        $this->assertSame($injector->create('SerialiseMe'), $_SESSION['slot']);
    }

    function testSessionableInstancePulledFromSessionIfExists() {
        $_SESSION['slot'] = new SerialiseMe();
        $injector = new Phemto();
        $injector->willUse(new Sessionable('SerialiseMe', 'slot'));
        $this->assertSame($injector->create('SerialiseMe'), $_SESSION['slot']);
    }
    
    function testSameInstanceFromSessionWithinSameProcess() {
        $injector = new Phemto();
        $injector->willUse(new Sessionable('SerialiseMe'));
        $this->assertSame($injector->create('SerialiseMe'), $injector->create('SerialiseMe'));
    }
    
    function TODO_testSessionableWorksWithinContext() {
        $injector = new Phemto();
        $injector->whenCreating('HoldsSessionable')->willUse(new Sessionable('SerialiseMe'));
        $holder = $injector->create('HoldsSessionable');
        $this->assertSame($holder->dependency, $_SESSION['SerialiseMe']);
    }
}
?>