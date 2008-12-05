<?php
require_once(dirname(__FILE__) . '/../simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../phemto.php');

class LoneClass { }
class ClassWithManySubclasses { }
class FirstSubclass extends ClassWithManySubclasses { }
class SecondSubclass extends ClassWithManySubclasses { }
abstract class AbstractClass { }
class ConcreteSubclass extends AbstractClass { }

class CanAutomaticallyInstantiateKnownClasses extends UnitTestCase {

    function testNamedClassInstantiatedAutomatically() {
		$injector = new Phemto();
		$this->assertIsA($injector->create('LoneClass'), 'LoneClass');
	}

    function testWillUseOnlySubclassIfParentIsAbstract() {
		$injector = new Phemto();
		$this->assertIsA($injector->create('AbstractClass'),
						 'ConcreteSubclass');
    }

    function testCanBeConfiguredToPreferSpecificSubclass() {
		$injector = new Phemto();
		$injector->willUse('SecondSubclass');
		$this->assertIsA($injector->create('ClassWithManySubclasses'),
						 'SecondSubclass');
	}
}
?>