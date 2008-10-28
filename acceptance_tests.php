<?php
require_once('simpletest/autorun.php');

class LoneClass { }
interface InterfaceWithOneImplementation { }
class OnlyImplementation implements InterfaceWithOneImplementation { }
interface InterfaceWithManyImplementations { }
class FirstImplementation implements InterfaceWithManyImplementations { }
class SecondImplementation implements InterfaceWithManyImplementations { }

class CanAutomaticallyInstantiateKnownInterfaces extends UnitTestCase {
	function testNamedClassInstantiatedAutomatically() {
		$injector = new Phemto();
		$this->assertIsA($injector->instantiate('LoneClass'), 'LoneClass');
	}
	
	function testInterfaceWithOnlyOneCandidateIsInstantiatedAutomatically() {
		$injector = new Phemto();
		$this->assertIsA($injector->instantiate('InterfaceWithOneImplementation'),
						 'OnlyImplementation');
	}
	
	function testWillThrowForUnknownClass() {
		$injector = new Phemto();
		$this->expectException(new CannotFindImplementation('NonExistent'));
		$injector->instantiate('NonExistent');
	}
	
	function testWillThrowIfInterfaceUnspecified() {
		$injector = new Phemto();
		$this->expectException(new MultipleImplementationsPossible('InterfaceWithManyImplementations'));
		$injector->instantiate('InterfaceWithManyImplementations');
	}
	
	function testCanBeConfiguredToPreferSpecificImplementation() {
		$injector = new Phemto();
		$injector->willUse('SecondImplementation');
		$this->assertIsA($injector->instantiate('InterfaceWithManyImplementations'),
						 'SecondImplementation');
	}
}

interface Hinted { }

class HintedConstructor implements Hinted {
	function __construct(InterfaceWithOneImplementation $one) {
		$this->args = func_get_args();
	}
}

class HintedConstructorWithDependencyChoice implements Hinted {
	function __construct(InterfaceWithManyImplementations $alternate) {
		$this->args = func_get_args();
	}
}

class RepeatedHintConstructor {
	function __construct(InterfaceWithManyImplementations $first, InterfaceWithManyImplementations $second) {
		$this->args = func_get_args();
	}
}

class CanAutomaticallyInjectTypeHintedDependencies extends UnitTestCase {
	function testSimpleDependenciesAreFulfilledAutomatically() {
		$injector = new Phemto();
		$this->assertIdentical($injector->instantiate('HintedConstructor'),
							   new HintedConstructor(new OnlyImplementation()));
	}
	
	function testRepeatedHintJustGetsTwoSeparateInstances() {
		$injector = new Phemto();
		$injector->willUse('SecondImplementation');
		$this->assertEqual(
				$injector->instantiate('RepeatedHintConstructor'),
				new RepeatedHintConstructor(new SecondImplementation(), new SecondImplementation()));
	}
}

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

class CanCallSettersToCompleteInitialisation extends UnitTestCase {
}

class WorksWithNamespaces extends UnitTestCase {
}

class AsMuchAsPossibleWorksWithAutoload extends UnitTestCase {
}
?>