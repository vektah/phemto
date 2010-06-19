<?php
require_once(dirname(__FILE__) . '/../simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../phemto.php');

function __autoload($class) {
    $source = dirname(__FILE__) . '/../tests/samples/autoload.' . strtolower($class) . '.php';
    if (file_exists($source)) {
        require_once($source);
    }
}

class AsMuchAsPossibleWorksWithAutoload extends UnitTestCase {
    function testCanInstantiateClassFoundWithAutoload() {
        $injector = new Phemto();
        $this->assertIsA($injector->create('FindMe'), 'FindMe');
    }
    
    function testCanFillHintedDependencyFromAutoload() {
        $injector = new Phemto();
        $object = $injector->create('FindMyHint');
        $this->assertIsA($object, 'FindMyHint');
        $this->assertIsA($object->dependency, 'MyHint');
    }
    
    function testCanFillNamedDependencyFromAutoload() {
        $injector = new Phemto();
        $injector->forVariable('dependency')->willUse('MyDependency');
        $object = $injector->create('FindMyDependency');
        $this->assertIsA($object, 'FindMyDependency');
        $this->assertIsA($object->dependency, 'MyDependency');
    }
    
    function testCanFillNamedDependencyFromAutoloadInContext() {
        $injector = new Phemto();
        $injector->whenCreating('FindMyDependency')->forVariable('dependency')->willUse('MyDependency');
        $object = $injector->create('FindMyDependency');
        $this->assertIsA($object, 'FindMyDependency');
        $this->assertIsA($object->dependency, 'MyDependency');
    }
    
    function testCanFulfilInterfaceFromAutoloadedClass() {
        $injector = new Phemto();
        $injector->willUse('MyImplementation');
        $object = $injector->create('FindMyInterfaceHint');
        $this->assertIsA($object, 'FindMyInterfaceHint');
        $this->assertIsA($object->dependency, 'MyImplementation');
    }
}
?>