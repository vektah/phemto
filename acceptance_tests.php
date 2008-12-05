<?php
require_once('simpletest/autorun.php');
require_once(dirname(__FILE__) . '/phemto.php');

class LoneClass { }
class ClassWithManySubclasses { }
class FirstSubclass extends ClassWithManySubclasses { }
class SecondSubclass extends ClassWithManySubclasses { }
abstract class AbstractClass { }
class ConcreteSubclass extends AbstractClass { }

class CanAutomaticallyInstantiateKnownClasses extends UnitTestCase {

    function testNamedClassInstantiatedAutomatically() {
		$injector = new Phemto();
		$this->assertIsA($injector->create('LoneClass'), 'LoneClass');
	}

    function testWillUseOnlySubclassIfParentIsAbstract() {
		$injector = new Phemto();
		$this->assertIsA($injector->create('AbstractClass'),
						 'ConcreteSubclass');
    }

    function testCanBeConfiguredToPreferSpecificSubclass() {
		$injector = new Phemto();
		$injector->willUse('SecondSubclass');
		$this->assertIsA($injector->create('ClassWithManySubclasses'),
						 'SecondSubclass');
	}
}

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

interface Hinted { }

class HintedConstructor implements Hinted {
	function __construct(InterfaceWithOneImplementation $one) {
		$this->one = $one;
	}
}

class HintedConstructorWithDependencyChoice implements Hinted {
	function __construct(InterfaceWithManyImplementations $alternate) {
		$this->alternate = $alternate;
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

    function TODO_testTypeHintsTakePrecedence() {
        // or do they? specify one or the other
    }

}

class CanUseDifferentDependencySetWithinAnInterface extends UnitTestCase {
	function testCanOverridePreferenceWhenInstantiatingSpecificInstance() {
		$injector = new Phemto();
		$injector->whenCreating('HintedConstructorWithDependencyChoice')->willUse('SecondImplementation');
		$injector->willUse('FirstImplementation');
		$this->assertEqual(
				$injector->create('HintedConstructorWithDependencyChoice'),
				new HintedConstructorWithDependencyChoice(new SecondImplementation()));
	}

	function testCanOverridePreferenceWhenInstantiatingInterface() {
		$injector = new Phemto();
		$injector->whenCreating('Hinted')->willUse('SecondImplementation');
		$injector->willUse('FirstImplementation');
		$this->assertEqual(
				$injector->create('HintedConstructorWithDependencyChoice'),
				new HintedConstructorWithDependencyChoice(new SecondImplementation()));
	}
}

class CanInstantiateObjectsAsSingletons extends UnitTestCase {
	function testSameInstanceCanBeReusedWithinFactory() {
		$injector = new Phemto();
		$injector->willUse(new Reused('LoneClass'));
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

class MustHaveCleanSyntaxForDecoratorsAndFilters extends UnitTestCase {
	function testCanWrapWithDecorator() {
		$injector = new Phemto();
		$injector->whenCreating('Bare')->wrapWith('WrapperForBare');
		$this->assertIdentical($injector->create('Bare'),
							   new WrapperForBare(new BareImplementation()));
	}
}

class ClassWithParameters {
    function __construct($a, $b) { @$this->a = $a; @$this->b = $b; }
}

class CanInstantiateWithNamedParameters extends UnitTestCase {
    function testCanFillMissingParametersWithExplicitValues() {
		$injector = new Phemto();
        $this->assertIdentical(
                $injector->fill('a', 'b')->with(3, 5)->create('ClassWithParameters'),
                new ClassWithParameters(3, 5));
    }
}

class CanInstantiateWithAnonymousParameters extends UnitTestCase {
    function testCanFillMissingParametersWithExplicitValues() {
		$injector = new Phemto();
        $this->assertIdentical(
                $injector->with(3, 5)->create('ClassWithParameters'),
                new ClassWithParameters(3, 5));
    }

    function TODO_testCanUseShorterSyntacticForm() {
		$injector = new Phemto();
        $this->assertIdentical(
                $injector->create('ClassWithParameters', 3, 5),
                new ClassWithParameters(3, 5));
    }
}

class MustBeEasyToAppendMoreConfigurationToAnExistingWiringFile extends UnitTestCase {
    // "everything must be override-able"
}

class CanEasilyCreateNewLifecycles extends UnitTestCase {
}

class WorksWithNamespaces extends UnitTestCase {
}

class AsMuchAsPossibleWorksWithAutoload extends UnitTestCase {
}
?>