<?php
require_once(dirname(__FILE__) . '/../simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../phemto.php');

class NotWithoutMe { }

class NeedsInitToCompleteConstruction {
    function init(NotWithoutMe $me) {
        @$this->me = $me;
    }
}

class CanUseSetterInjection extends UnitTestCase {
    function testCanCallSettersToCompleteInitialisation() {
        $injector = new Phemto();
        $injector->forType('NeedsInitToCompleteConstruction')->call('init');
        $expected = new NeedsInitToCompleteConstruction();
        $expected->init(new NotWithoutMe());
        $this->assertIdentical($injector->create('NeedsInitToCompleteConstruction'),
                               $expected);
    }
}
?>