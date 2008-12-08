<?php
require_once(dirname(__FILE__) . '/../simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../phemto.php');

class NeededForFirst { }
class NeededForSecond { }

class VariablesInConstructor {
    function __construct($first, $second) {
        $this->args = array($first, $second);
    }
}

class CanInjectDependenciesByVariableName extends UnitTestCase {
    function testExplicitlyNamedVariables() {
        $injector = new Phemto();
        $injector->forVariable('first')->willUse('NeededForFirst');
        $injector->forVariable('second')->willUse('NeededForSecond');
        $this->assertEqual(
                $injector->create('VariablesInConstructor'),
                new VariablesInConstructor(new NeededForFirst(), new NeededForSecond()));
    }
}
?>