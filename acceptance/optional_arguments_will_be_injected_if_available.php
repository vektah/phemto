<?php
require_once('simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../phemto.php');

class MaybeThis { }

class AvailableOptionalHintedArgument {
    public $maybe;

    function __construct(MaybeThis $maybe = null) {
        $this->maybe = $maybe;
    }
}

class MissingOptionalHintedArguments {
    public $maybe;

    function __construct(MaybeNot $maybe = null) {
        $this->maybe = $maybe;
    }
}

class OptionalArgumentsWillBeInjectedIfAvailable extends UnitTestCase {
    function testHintedOptionalArgumentWillBeUsed() {
        $injector = new Phemto();
        $this->assertIdentical($injector->create('AvailableOptionalHintedArgument'),
                               new AvailableOptionalHintedArgument(new MaybeThis()));
    }

    function testCanInstantiateWithMissingOptionalArguments() {
        $injector = new Phemto();
        $this->assertIdentical($injector->create('MissingOptionalHintedArguments'),
                               new MissingOptionalHintedArguments());
    }
}
?>