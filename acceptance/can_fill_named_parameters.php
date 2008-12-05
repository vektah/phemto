<?php
require_once(dirname(__FILE__) . '/../simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../phemto.php');

class ClassWithParameters {
    function __construct($a, $b) { @$this->a = $a; @$this->b = $b; }
}

class CanInstantiateWithNamedParameters extends UnitTestCase {
    function testCanFillMissingParametersWithExplicitValues() {
		$injector = new Phemto();
        $this->assertIdentical(
                $injector->fill('a', 'b')->with(3, 5)->create('ClassWithParameters'),
                new ClassWithParameters(3, 5));
    }
}
?>