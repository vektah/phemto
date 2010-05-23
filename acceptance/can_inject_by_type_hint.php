<?php
require_once(dirname(__FILE__) . '/../simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../phemto.php');

interface Hinted { }

class NeededForConstructor { }

class HintedConstructor implements Hinted {
    function __construct(NeededForConstructor $one) {
        $this->one = $one;
    }
}

class HintedConstructorWithDependencyChoice implements Hinted {
    function __construct(InterfaceWithManyImplementations $alternate) {
        $this->alternate = $alternate;
    }
}

class RepeatedHintConstructor {
    function __construct(NeededForConstructor $first, NeededForConstructor $second) {
        $this->args = array($first, $second);
    }
}

class CanAutomaticallyInjectTypeHintedDependencies extends UnitTestCase {
    function testSimpleDependenciesAreFulfilledAutomatically() {
        $injector = new Phemto();
        $this->assertIdentical($injector->create('HintedConstructor'),
                               new HintedConstructor(new NeededForConstructor()));
    }

    function testRepeatedHintJustGetsTwoSeparateInstances() {
        $injector = new Phemto();
        $injector->willUse('SecondImplementation');
        $this->assertEqual(
                $injector->create('RepeatedHintConstructor'),
                new RepeatedHintConstructor(new NeededForConstructor(), new NeededForConstructor()));
    }
}
?>