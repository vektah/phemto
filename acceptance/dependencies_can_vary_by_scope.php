<?php
require_once('simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../phemto.php');

class UsualImplementation { }
class SpecialImplementation extends UsualImplementation { }

class ClassWithUsual {
    function __construct(UsualImplementation $a) { $this->a = $a; }
}

interface SpecialInterface { }
class ClassWithSpecial implements SpecialInterface {
    function __construct(UsualImplementation $a) { $this->a = $a; }
}

class CanUseDifferentDependencySetWithinAnInterface extends UnitTestCase {
    function testCanOverridePreferenceWhenInstantiatingSpecificInstance() {
        $injector = new Phemto();
        $injector->whenCreating('ClassWithSpecial')->willUse('SpecialImplementation');
        $injector->willUse('UsualImplementation');
        $this->assertEqual(
                $injector->create('ClassWithUsual'),
                new ClassWithUsual(new UsualImplementation()));
        $this->assertEqual(
                $injector->create('ClassWithSpecial'),
                new ClassWithSpecial(new SpecialImplementation()));
    }

    function testCanOverridePreferenceWhenInstantiatingFromAnInterface() {
        $injector = new Phemto();
        $injector->whenCreating('SpecialInterface')->willUse('SpecialImplementation');
        $injector->willUse('UsualImplementation');
        $this->assertEqual(
                $injector->create('SpecialInterface'),
                new ClassWithSpecial(new SpecialImplementation()));
    }
}
?>