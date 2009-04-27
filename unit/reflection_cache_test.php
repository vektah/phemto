<?php
require_once('simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../repository.php');

interface A { }
interface B { }
class Imp1 implements A { }
class Imp2 implements A, B { }

class TestOfReflectionCache extends UnitTestCase {

    function testCanFindImplementationsFromInterface() {
        $this->assertIdentical($this->reflection()->implementationsOf('A'),
                               array('Imp1', 'Imp2'));
    }

    function testCanFindInterfaces() {
        $this->assertIdentical($this->reflection()->interfacesOf('Imp1'), array('A'));
        $this->assertIdentical($this->reflection()->interfacesOf('Imp2'), array('A', 'B'));
    }

    private function reflection() {
        $reflection = new ReflectionCache();
        $reflection->refresh();
        return $reflection;
    }
}
?>