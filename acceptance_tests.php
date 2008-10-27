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
		$injector->prefer('SecondImplementation');
		$this->assertIsA($injector->instantiate('InterfaceWithManyImplementations'),
						 'SecondImplementation');
	}
}

class CanAutomaticallyInjectTypeHintedDependencies extends UnitTestCase {
}

class CanInjectDependenciesByVariableName extends UnitTestCase {
}

class CanUseDifferentDependencySetWithinAnInterface extends UnitTestCase {
}

class CanInstantiateObjectsAsSingletons extends UnitTestCase {
}

class CanInstantiateFromSessions extends UnitTestCase {
}

class CanCallSettersToCompleteInitialisation extends UnitTestCase {
}

class WorksWithNamespaces extends UnitTestCase {
}

class WorksWithAutoload extends UnitTestCase {
}
?>