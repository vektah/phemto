<?php
require_once('simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../phemto.php');

interface Number {
    function getValue();
}

class One implements Number {
    function getValue() { return 1; }
}

class Two implements Number {
    function getValue() { return 2; }
}

class Single extends One { }
class Lonely extends Single { }

class WhenInstantiatingWithoutDependencies extends UnitTestCase {

    function testCancreateSimpleClassWithoutDependencies() {
        $injector = new Phemto();
        $injector->willUse('One');
        $this->assertIsA($injector->create('One'), 'One');
    }

    function testCancreateClassFromInterfaceWithoutDependencies() {
        $injector = new Phemto();
        $injector->willUse('One');
        $this->assertIsa($injector->create('Number'), 'One');
    }

    function testCancreateSubclassForSuperclass() {
        $injector = new Phemto();
        $injector->willUse('Single');
        $this->assertIsA($injector->create('One'), 'Single');
    }

    function testCancreateSubclassTwoDeepFromSuperclass() {
        $injector = new Phemto();
        $injector->willUse('Lonely');
        $this->assertIsA($injector->create('One'), 'Lonely');
    }

    function testUsesLastRegisteredClassToFillDependency() {
        $injector = new Phemto();
        $injector->willUse('One');
        $injector->willUse('Two');
        $this->assertIsA($injector->create('Number'), 'Two');
    }

    function testMissingClassTriggersException() {
        $injector = new Phemto();
        try {
            $injector->willUse('NoClassForThis');
            $this->fail('Missing class did not throw');
        } catch (Exception $exception) {
        }
    }
}

class Doubler {
    public $result;

    function __construct(Number $a_number) {
        $this->result = $a_number->getValue() * 2;
    }
}

class Adder implements Number {
    public $result;

    function __construct(One $a_one, Two $a_two) {
        $this->result = $a_one->getValue() + $a_two->getValue();
    }

    function getValue() {
        return $this->result;
    }
}

class WhenInstantiatingWithParameters extends UnitTestCase {

    function testCanFulfillSimpleConstructorDependency() {
        $injector = new Phemto();
        $injector->willUse('Two');
        $injector->willUse('Doubler');
        $result = $injector->create('Doubler');
        $this->assertEqual($result->result, 4);
    }

    function testMultipleConstructorDependency() {
        $injector = new Phemto();
        $injector->willUse('One');
        $injector->willUse('Two');
        $injector->willUse('Adder');
        $result = $injector->create('Adder');
        $this->assertEqual($result->result, 3);
    }

    function testNestedConstructorDependency() {
        $injector = new Phemto();
        $injector->willUse('One');
        $injector->willUse('Two');
        $injector->willUse('Adder');
        $injector->willUse('Doubler');
        $result = $injector->create('Doubler');
        $this->assertEqual($result->result, 6);
    }
}

interface Message { }

class Greeting implements Message {
	public $name;

	function __construct($name) {
		$this->name = $name;
	}

	function getMessage() {
		return 'Hello ' . $this->name;
	}
}

class Increaser {
	public $result;

	function __construct($start, Number $number, $extra = 0) {
		$this->result = $start + $number->getValue() + $extra;
	}
}

class WhenFillingConstructorDependencies extends UnitTestCase {

	function testCanInitialiseWithAStringParameter() {
        $injector = new Phemto();
        $injector->willUse('Greeting');
		$message = $injector->create('Greeting', array('friend'));
		$this->assertEqual($message->getMessage(), 'Hello friend');
	}

	function testCanInitialiseInterfaceWithStringParameter() {
        $injector = new Phemto();
        $injector->willUse('Greeting');
		$message = $injector->create('Message', array('friend'));
		$this->assertEqual($message->getMessage(), 'Hello friend');
	}

	function testCanMixIncomingParametersWithDependencies() {
        $injector = new Phemto();
        $injector->willUse('Increaser');
        $injector->willUse('One');
		$increaser = $injector->create('Increaser', array(13));
		$this->assertEqual($increaser->result, 14);
	}

	function testCanMixOptionalParametersWithInjection() {
        $injector = new Phemto();
        $injector->willUse('Increaser');
        $injector->willUse('Two');
		$increaser = $injector->create('Increaser', array(13, 10));
		$this->assertEqual($increaser->result, 25);
	}
}

class WhenManagingLifecycle extends UnitTestCase {

	function testOnlyEverOneInstanceWhenRegisteredAsSingleton() {
        $injector = new Phemto();
        $injector->willUse(new Singleton('One'));
        $this->assertIdentical(
        		$injector->create('Number'),
        		$injector->create('Number'));
	}

	function testCopyCreatedWhenRegisteredAsMultiple() {
        $injector = new Phemto();
        $injector->willUse('One');
        $this->assertClone(
        		$injector->create('Number'),
        		$injector->create('Number'));
	}

	function testCanInstantiateSingletonWithParameters() {
        $injector = new Phemto();
        $injector->willUse(new Singleton('Greeting', array('me')));
        $message = $injector->create('Message');
        $this->assertEqual($message->getMessage(), 'Hello me');
	}
}

class MessageToYou extends LocatorDecorator implements PhemtoLocator {
	function instantiate($dependencies) {
		$object = parent::instantiate($dependencies);
		$object->name = 'you';
		return $object;
	}
}

class WhenApplyingLocationDecorators extends UnitTestCase {
	
	function testCanAffectConstructionOnTheWayThrough() {
		$injector = new Phemto();
		$injector->willUse(new MessageToYou('Greeting'));
        $message = $injector->create('Message', array('me'));
        $this->assertEqual($message->getMessage(), 'Hello you');
	}
}

class Charm {
    function __construct($a) { }
}

class WhenPassingParameters extends UnitTestCase {
    function setUp() {
        $this->injector = new Phemto;
    }
    
    function testCanPassAnEmptyString() {
        $this->injector->willUse('Charm');
        $this->injector->create('Charm', array(''));
    }
    
    function testCanPassBooleanFalse() {
        $this->injector->willUse('Charm');
        $this->injector->create('Charm', array(false));
    }
    
    function testCanPassIntegerZero() {
        $this->injector->willUse('Charm');
        $this->injector->create('Charm', array(0));
    }
    
    function testCanPassEmptyArray() {
        $this->injector->willUse('Charm');
        $this->injector->create('Charm', array(array()));
    }
    
    function testCanPassNull() {
        $this->injector->willUse('Charm');
        $this->injector->create('Charm', array(null)); 
    }
}

?>