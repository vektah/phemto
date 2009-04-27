<?php
require_once('simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../phemto.php');

interface InterfaceWithOneImplementation { }
class OnlyImplementation implements InterfaceWithOneImplementation { }
interface InterfaceWithManyImplementations { }
class FirstImplementation implements InterfaceWithManyImplementations { }
class SecondImplementation implements InterfaceWithManyImplementations { }

class CanAutomaticallyInstantiateKnownInterfaces extends UnitTestCase {

    function testInterfaceWithOnlyOneCandidateIsInstantiatedAutomatically() {
        $injector = new Phemto();
        $this->assertIsA($injector->create('InterfaceWithOneImplementation'),
                         'OnlyImplementation');
    }

    function testWillThrowForUnknownClass() {
        $injector = new Phemto();
        $this->expectException('CannotFindImplementation');
        $injector->create('NonExistent');
    }

    function testWillThrowIfInterfaceUnspecified() {
        $injector = new Phemto();
        $this->expectException('CannotDetermineImplementation');
        $injector->create('InterfaceWithManyImplementations');
    }

    function testCanBeConfiguredToPreferSpecificImplementation() {
        $injector = new Phemto();
        $injector->willUse('SecondImplementation');
        $this->assertIsA($injector->create('InterfaceWithManyImplementations'),
                         'SecondImplementation');
    }
}
?>