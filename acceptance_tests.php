<?php
require_once('simpletest/autorun.php');
require_once('phemto/phemto.php');


class LoneClass { }
interface InterfaceWithOneImplementation { }
class OnlyImplementation implements InterfaceWithOneImplementation { }
interface InterfaceWithManyImplementations { }
class FirstImplementation implements InterfaceWithManyImplementations { }
class SecondImplementation implements InterfaceWithManyImplementations { }

class CanAutomaticallyInstantiateKnownInterfaces extends UnitTestCase {
	
    function testNamedClassInstantiatedAutomatically() {
		$injector = new Phemto();
		$this->assertIsA(
            $injector->instantiate('LoneClass'), 
            'LoneClass');
	}

    function testInterfaceWithOnlyOneCandidateIsInstantiatedAutomatically() {
		$injector = new Phemto();
		$this->assertIsA(
            $injector->instantiate('InterfaceWithOneImplementation'),
            'OnlyImplementation');
	}

    function testWillThrowForUnknownClass() {
		$injector = new Phemto();
		$this->expectException(new CannotFindImplementation('NonExistent'));
		$injector->instantiate('NonExistent');
	}
	
	function testWillThrowIfInterfaceUnspecified() {
		$injector = new Phemto();
		$this->expectException(
            new MultipleImplementationsPossible(
                'InterfaceWithManyImplementations', 
                array('FirstImplementation', 'SecondImplementation')));
		$injector->instantiate('InterfaceWithManyImplementations');
	}
	
    function testCanBeConfiguredToPreferSpecificImplementation() {
		$injector = new Phemto();
		$injector->willUse('SecondImplementation');
		$this->assertIsA(
            $injector->instantiate('InterfaceWithManyImplementations'),
			'SecondImplementation');
	}
}
interface Hinted { }

class HintedConstructor implements Hinted {
	function __construct(InterfaceWithOneImplementation $one) {
		$this->one = $one;
	}
}

class HintedConstructorWithDependencyChoice implements Hinted {
	function __construct(InterfaceWithManyImplementations $alternate) {
		$this->altternate = $alternate;
	}
}

class RepeatedHintConstructor {
	function __construct(InterfaceWithManyImplementations $first, InterfaceWithManyImplementations $second) {
		$this->args = array($first, $second);
	}
}

class CanAutomaticallyInjectTypeHintedDependencies extends UnitTestCase {
	function testSimpleDependenciesAreFulfilledAutomatically() {
		$injector = new Phemto();
		$this->assertIdentical(
            $injector->instantiate('HintedConstructor'),
			new HintedConstructor(new OnlyImplementation()));
	}
	
	function testRepeatedHintJustGetsTwoSeparateInstances() {
		$injector = new Phemto();
		$injector->willUse('SecondImplementation');
		$this->assertEqual(
            $injector->instantiate('RepeatedHintConstructor'),
            new RepeatedHintConstructor(
                new SecondImplementation(), 
                new SecondImplementation()));
	}
}
/*

class CanInjectDependenciesByVariableName extends UnitTestCase {
	function testExplicitlyNamedVariables() {
		$injector = new Phemto();
		$injector->forVariable('first')->willUse('FirstImplementation');
		$injector->forVariable('second')->willUse('SecondImplementation');
		$this->assertEqual(
				$injector->instantiate('RepeatedHintConstructor'),
				new RepeatedHintConstructor(new FirstImplementation(), new SecondImplementation()));
	}
}

class CanUseDifferentDependencySetWithinAnInterface extends UnitTestCase {
	function testCanOverridePreferenceWhenInstantiatingSpecificInstance() {
		$injector = new Phemto();
		$injector->willUse('FirstImplementation');
		$injector->whenCreating('Hinted')->willUse('SecondImplementation');
		$this->assertEqual(
				$injector->instantiate('HintedConstructorWithDependencyChoice'),
				new HintedConstructorWithDependencyChoice(new SecondImplementation()));
	}
}

#
# This next one, the car stuff, is just a suggestion.
#

abstract class Car {

    function __construct(Steering $steering) {
        $this->steering = $steering;
    }
    function getSteering() {
        return get_class($this->steering);
    }
}
class Jaguar extends Car {}
class MiniCooper extends Car {}

interface Steering {}
class RightHandDrive implements Steering {}
class LeftHandDrive implements Steering {}

class CanSetDifferentPreferencesForInstancesOfTheSameClass extends UnitTestCase {
    function test() {
		$globe = new Phemto();
        $britain = $globe->getSubgraph();
        $america = $globe->getSubgraph();

        $globe->willUse('Jaguar');
        $america->willUse('LeftHandDrive');
        $britain->willUse('RightHandDrive');
        
        $car = $america->instantiate('Car');
        $this->assertEqual(
            $car->getSteering(), 
            'LeftHandDrive');

        $car = $britain->instantiate('Car');
        $this->assertEqual(
            $car->getSteering(), 
            'RightHandDrive');
    }
}
class CanInstantiateObjectsAsSingletons extends UnitTestCase {
	function testSameInstanceIsReusedForSingleton() {
		$injector = new Phemto();
		$injector->willUse(new Singleton('LoneClass'));
		$this->assertSame(
				$injector->instantiate('LoneClass'),
				$injector->instantiate('LoneClass'));
	}
}

class CanInstantiateFromSessions extends UnitTestCase {
	function tearDown() {
		$_SESSION['slot'] = false;
	}
	
	function testSessionableInstanceWrittenToSession() {
		$injector = new Phemto();
		$injector->willUse(new Sessionable('slot', 'LoneClass'));
		$_SESSION['slot'] = false;
		$lone = $injector->instantiate('LoneClass');
		$this->assertSame($lone, $_SESSION['slot']);
	}
	
	function testSessionableInstancePulledFromSessionIfExists() {
		$_SESSION['slot'] = new LoneClass();
		$injector = new Phemto();
		$injector->willUse(new Sessionable('slot', 'LoneClass'));
		$this->assertSame($injector->instantiate('LoneClass'), $_SESSION['slot']);
	}
}

class NeedsInitToCompleteConstruction {
	function init(LoneClass $lone) {
		$this->lone = $lone;
	}
}

class CanUseSetterInjection extends UnitTestCase {
	function testCanCallSettersToCompleteInitialisation() {
		$injector = new Phemto();
		$injector->whenCreating('NeedsInitToCompleteConstruction')->call('init');
		$expected = new NeedsInitToCompleteConstruction();
		$expected->init(new LoneClass());
		$this->assertIdentical($injector->instantiate('NeedsInitToCompleteConstruction'),
							   $expected);
	}
}

interface Bare { }
class BareImplementation implements Bare { }
class WrapperForBare {
	function __construct(Bare $bare) { $this->bare = $bare; }
}

class MustBeCleanSyntaxForDecoratorsAndFilters extends UnitTestCase {
	function testCanWrapWithDecorator() {
		$injector = new Phemto();
		$injector->whenCreating('Bare')->wrapWith('WrapperForBare');
		$this->assertIdentical($injector->instantiate('Bare'),
							   new WrapperForBare(new BareImplementation()));
	}
}

class WorksWithNamespaces extends UnitTestCase {
}

class AsMuchAsPossibleWorksWithAutoload extends UnitTestCase {
}
*/
?>