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

class OptionalArgument {
    public $maybe;

    function __construct($maybe = null) {
        $this->maybe = $maybe;
    }
}

class ManyOptionalArguments {
    public $maybe, $never, $unlikely;

    function __construct($maybe = null, $never = null, $unlikely = null) {
        $this->maybe = $maybe;
        $this->never = $never;
        $this->unlikely = $unlikely;
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

    function testOptionalArgumentWillBeInjectedIfNamed() {
        $injector = new Phemto();
        $injector->forVariable('maybe')->willUse('MaybeThis');
        $this->assertIdentical($injector->create('OptionalArgument'),
                               new OptionalArgument(new MaybeThis()));
    }

    function testUnnamedArgumentSkipped() {
        $injector = new Phemto();
        $this->assertIdentical($injector->create('OptionalArgument'),
                               new OptionalArgument(null));
    }

    function testInjectionStopsAtFirstMissingArgument() {
        $injector = new Phemto();
        $injector->forVariable('maybe')->willUse('MaybeThis');
        $injector->forVariable('unlikely')->willUse('MaybeThis');
        $this->assertIdentical($injector->create('ManyOptionalArguments'),
                               new ManyOptionalArguments(new MaybeThis(), null, null));
    }
}
?>