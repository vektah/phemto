<?php
require_once('simpletest/autorun.php');
require_once(dirname(__FILE__) . '/phemto.php');

class LoneClass { }
interface InterfaceWithOneImplementation { }
class OnlyImplementation implements InterfaceWithOneImplementation { }
interface InterfaceWithManyImplementations { }
class FirstImplementation implements InterfaceWithManyImplementations { }
class SecondImplementation implements InterfaceWithManyImplementations { }

class CanAutomaticallyInstantiateKnownInterfaces extends UnitTestCase {
	
    function testNamedClassInstantiatedAutomatically() {
		$injector = new Phemto();
		$this->assertIsA($injector->create('LoneClass'), 'LoneClass');
	}

    function testInterfaceWithOnlyOneCandidateIsInstantiatedAutomatically() {
		$injector = new Phemto();
		$this->assertIsA($injector->create('InterfaceWithOneImplementation'),
						 'OnlyImplementation');
	}

    function testWillThrowForUnknownClass() {
		$injector = new Phemto();
		$this->expectException(new CannotFindImplementation('NonExistent'));
		$injector->create('NonExistent');
	}
	
	function testWillThrowIfInterfaceUnspecified() {
		$injector = new Phemto();
		$this->expectException(
            new MultipleImplementationsPossible(
                'InterfaceWithManyImplementations', 
                array('FirstImplementation', 'SecondImplementation')));
		$injector->create('InterfaceWithManyImplementations');
	}
	
    function testCanBeConfiguredToPreferSpecificImplementation() {
		$injector = new Phemto();
		$injector->willUse('SecondImplementation');
		$this->assertIsA($injector->create('InterfaceWithManyImplementations'),
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
		$this->assertIdentical($injector->create('HintedConstructor'),
							   new HintedConstructor(new OnlyImplementation()));
	}
	
	function testRepeatedHintJustGetsTwoSeparateInstances() {
		$injector = new Phemto();
		$injector->willUse('SecondImplementation');
		$this->assertEqual(
				$injector->create('RepeatedHintConstructor'),
				new RepeatedHintConstructor(new SecondImplementation(), new SecondImplementation()));
	}
}

class CanInjectDependenciesByVariableName extends UnitTestCase {
	function testExplicitlyNamedVariables() {
		$injector = new Phemto();
		$injector->forVariable('first')->willUse('FirstImplementation');
		$injector->forVariable('second')->willUse('SecondImplementation');
		$this->assertEqual(
				$injector->create('RepeatedHintConstructor'),
				new RepeatedHintConstructor(new FirstImplementation(), new SecondImplementation()));
	}
    
    function testTypeHintsTakePrecedence() {
        // or do they? specify one or the other
    }

}

class CanUseDifferentDependencySetWithinAnInterface extends UnitTestCase {
	function testCanOverridePreferenceWhenInstantiatingSpecificInstance() {
		$injector = new Phemto();
		$injector->willUse('FirstImplementation');
		$injector->whenCreating('Hinted')->willUse('SecondImplementation');
		$this->assertEqual(
				$injector->create('HintedConstructorWithDependencyChoice'),
				new HintedConstructorWithDependencyChoice(new SecondImplementation()));
	}
}

class CanInstantiateObjectsAsSingletons extends UnitTestCase {
	function testSameInstanceIsReusedForSingleton() {
		$injector = new Phemto();
		$injector->willUse(new Singleton('LoneClass'));
		$this->assertSame(
				$injector->create('LoneClass'),
				$injector->create('LoneClass'));
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
		$lone = $injector->create('LoneClass');
		$this->assertSame($lone, $_SESSION['slot']);
	}
	
	function testSessionableInstancePulledFromSessionIfExists() {
		$_SESSION['slot'] = new LoneClass();
		$injector = new Phemto();
		$injector->willUse(new Sessionable('slot', 'LoneClass'));
		$this->assertSame($injector->create('LoneClass'), $_SESSION['slot']);
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
		$this->assertIdentical($injector->create('NeedsInitToCompleteConstruction'),
							   $expected);
	}
}

interface Bare { }
class BareImplementation implements Bare { }
class WrapperForBare {
	function __construct(Bare $bare) { $this->bare = $bare; }
}

class MustBeEasyToAppendToWiringFile extends UnitTestCase {
    // "everything must be override-able"
}

class MustBeCleanSyntaxForDecoratorsAndFilters extends UnitTestCase {
	function testCanWrapWithDecorator() {
		$injector = new Phemto();
		$injector->whenCreating('Bare')->wrapWith('WrapperForBare');

		$this->assertIdentical($injector->create('Bare'),
							   new WrapperForBare(new BareImplementation()));
	}
}

class CanEasilyCreateNewLifecycles extends UnitTestCase {
}

class WorksWithNamespaces extends UnitTestCase {
}

class AsMuchAsPossibleWorksWithAutoload extends UnitTestCase {
}
?>