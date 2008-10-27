<?php
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

    function testCanInstantiateSimpleClassWithoutDependencies() {
        $injector = new Phemto();
        $injector->register('One');
        $this->assertIsA($injector->instantiate('One'), 'One');
    }

    function testCanInstantiateClassFromInterfaceWithoutDependencies() {
        $injector = new Phemto();
        $injector->register('One');
        $this->assertIsa($injector->instantiate('Number'), 'One');
    }

    function testCanInstantiateSubclassForSuperclass() {
        $injector = new Phemto();
        $injector->register('Single');
        $this->assertIsA($injector->instantiate('One'), 'Single');
    }

    function testCanInstantiateSubclassTwoDeepFromSuperclass() {
        $injector = new Phemto();
        $injector->register('Lonely');
        $this->assertIsA($injector->instantiate('One'), 'Lonely');
    }

    function testUsesLastRegisteredClassToFillDependency() {
        $injector = new Phemto();
        $injector->register('One');
        $injector->register('Two');
        $this->assertIsA($injector->instantiate('Number'), 'Two');
    }

    function testMissingClassTriggersException() {
        $injector = new Phemto();
        try {
            $injector->register('NoClassForThis');
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
        $injector->register('Two');
        $injector->register('Doubler');
        $result = $injector->instantiate('Doubler');
        $this->assertEqual($result->result, 4);
    }

    function testMultipleConstructorDependency() {
        $injector = new Phemto();
        $injector->register('One');
        $injector->register('Two');
        $injector->register('Adder');
        $result = $injector->instantiate('Adder');
        $this->assertEqual($result->result, 3);
    }

    function testNestedConstructorDependency() {
        $injector = new Phemto();
        $injector->register('One');
        $injector->register('Two');
        $injector->register('Adder');
        $injector->register('Doubler');
        $result = $injector->instantiate('Doubler');
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
        $injector->register('Greeting');
		$message = $injector->instantiate('Greeting', array('friend'));
		$this->assertEqual($message->getMessage(), 'Hello friend');
	}

	function testCanInitialiseInterfaceWithStringParameter() {
        $injector = new Phemto();
        $injector->register('Greeting');
		$message = $injector->instantiate('Message', array('friend'));
		$this->assertEqual($message->getMessage(), 'Hello friend');
	}

	function testCanMixIncomingParametersWithDependencies() {
        $injector = new Phemto();
        $injector->register('Increaser');
        $injector->register('One');
		$increaser = $injector->instantiate('Increaser', array(13));
		$this->assertEqual($increaser->result, 14);
	}

	function testCanMixOptionalParametersWithInjection() {
        $injector = new Phemto();
        $injector->register('Increaser');
        $injector->register('Two');
		$increaser = $injector->instantiate('Increaser', array(13, 10));
		$this->assertEqual($increaser->result, 25);
	}
}

class WhenManagingLifecycle extends UnitTestCase {

	function testOnlyEverOneInstanceWhenRegisteredAsSingleton() {
        $injector = new Phemto();
        $injector->register(new Singleton('One'));
        $this->assertIdentical(
        		$injector->instantiate('Number'),
        		$injector->instantiate('Number'));
	}

	function testCopyInstantiatedWhenRegisteredAsMultiple() {
        $injector = new Phemto();
        $injector->register('One');
        $this->assertClone(
        		$injector->instantiate('Number'),
        		$injector->instantiate('Number'));
	}

	function testCanInstantiateSingletonWithParameters() {
        $injector = new Phemto();
        $injector->register(new Singleton('Greeting', array('me')));
        $message = $injector->instantiate('Message');
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
		$injector->register(new MessageToYou('Greeting'));
        $message = $injector->instantiate('Message', array('me'));
        $this->assertEqual($message->getMessage(), 'Hello you');
	}
}

class Charm {
    function __construct($a) {
    }
}
/*  bugfix
*/
class WhenPassingParameters extends UnitTestCase {

    function setUp() {
        $this->injector = new Phemto;
    }
    function testCanPassAnEmptyString() {
        $this->injector->register('Charm');
        $this->injector->instantiate('Charm', array(''));
        #$this->assertNoErrors();
    }
    function testCanPassBooleanFalse() {
        $this->injector->register('Charm');
        $this->injector->instantiate('Charm', array(false));
        #$this->assertNoErrors();
    }
    function testCanPassIntegerZero() {
        $this->injector->register('Charm');
        $this->injector->instantiate('Charm', array(0));
        #$this->assertNoErrors();
    }
    function testCanPassEmptyArray() {
        $this->injector->register('Charm');
        $this->injector->instantiate('Charm', array(array()));
        #$this->assertNoErrors();
    }
    function testCanPassNull() {
        $this->injector->register('Charm');
        $this->injector->instantiate('Charm', array(null)); 
        #$this->assertNoErrors();
    }
}
class WhenRegisteringClasses extends UnitTestCase {

    function testCanRegisterFluently() {
        $injector = new Phemto;
        $injector
            ->register('Stdclass')
            ->register('Stdclass');
        #$this->assertNoErrors();
    }
}
class Bicycle {
    function __construct(Person $rider) {
        $this->_rider = $rider;
    }
    function getRiderName() {
        return $this->_rider->getName();
    }
}
class Person {
    function __construct($name) {
        $this->name = $name;
    }
    function getName() {
        return $this->name;
    }
}
class WhenPassingParametersToDependencies extends UnitTestCase {

    function __construct() {
        $this->injector = new Phemto;
        $this->injector
            ->register('Bicycle')
            ->register('Person');
    }
    function testCanPassParametersToADependencyWhenInstantiatingTheClient() {
        $bike = $this->injector
            ->pass(array('Jake'))->to('Person')
            ->instantiate('Bicycle');
        $this->assertEqual($bike->getRiderName(), 'Jake');
    }
    function testRememberLastParametersPassed() {
        $bike_0 = $this->injector
            ->pass(array('Jake'))->to('Person')
            ->instantiate('Bicycle');
        
        $bike_1 = $this->injector->instantiate('Bicycle');
        $this->assertEqual($bike_1->getRiderName(), 'Jake');
    }
}
?>
