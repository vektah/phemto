<?php
require_once(dirname(__FILE__) . '/../simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../phemto.php');

interface Bare { }
class BareImplementation implements Bare { }
class WrapperForBare {
    function __construct(Bare $bare) { $this->bare = $bare; }
}

class MustHaveCleanSyntaxForDecoratorsAndFilters extends UnitTestCase {
    function testCanWrapWithDecorator() {
        $injector = new Phemto();
        $injector->whenCreating('Bare')->wrapWith('WrapperForBare');
        $this->assertIdentical($injector->create('Bare'),
                               new WrapperForBare(new BareImplementation()));
    }
}
?>