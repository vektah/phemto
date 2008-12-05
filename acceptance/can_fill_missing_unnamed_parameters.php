<?php
require_once(dirname(__FILE__) . '/../simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../phemto.php');

class ClassWithAnyOldParameters {
    function __construct($a, $b) { @$this->a = $a; @$this->b = $b; }
}

class CanInstantiateWithAnonymousParameters extends UnitTestCase {
    function testCanFillMissingParametersWithExplicitValues() {
		$injector = new Phemto();
        $this->assertIdentical(
                $injector->with(3, 5)->create('ClassWithAnyOldParameters'),
                new ClassWithAnyOldParameters(3, 5));
    }

    function testCanUseShorterSyntacticForm() {
		$injector = new Phemto();
        $this->assertIdentical(
                $injector->create('ClassWithAnyOldParameters', 3, 5),
                new ClassWithAnyOldParameters(3, 5));
    }
}
?>