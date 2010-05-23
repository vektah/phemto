<?php
require_once(dirname(__FILE__) . '/../simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../phemto.php');

class WrapThing {
    function __construct(Thing $thing) { $this->thing = $thing; }
}

class WrapAnything {
    function __construct($thing) { $this->thing = $thing; }
}

class Thing { }

class CanInjectSpecificValue extends UnitTestCase {
    function testCanInjectSpecificInstance() {
        $injector = new Phemto();
        $injector->willUse(new Thing());
        $this->assertIdentical($injector->create('WrapThing'),
                               new WrapThing(new Thing()));
    }

    function testInjectingSpecificInstanceForNamedVariable() {
        $injector = new Phemto();
        $injector->forVariable('thing')->willUse(new Thing());
        $this->assertIdentical($injector->create('WrapAnything'),
                               new WrapAnything(new Thing()));
    }

    function testInjectingNonObject() {
        $injector = new Phemto();
        $injector->forVariable('thing')->willUse(100);
        $this->assertIdentical($injector->create('WrapAnything'),
                               new WrapAnything(100));
    }

    function testInjectingString() {
        $injector = new Phemto();
        $injector->forVariable('thing')->useString('100');
        $this->assertIdentical($injector->create('WrapAnything'),
                               new WrapAnything('100'));
    }
}
?>