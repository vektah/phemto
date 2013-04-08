<?php
namespace phemto\acceptance;
use phemto\Phemto;

class LoneClass
{
}

class ClassWithManySubclasses
{
}

class FirstSubclass extends ClassWithManySubclasses
{
}

class SecondSubclass extends ClassWithManySubclasses
{
}

abstract class AbstractClass
{
}

class ConcreteSubclass extends AbstractClass
{
}

class CanAutomaticallyInstantiateKnownClassesTest extends \PHPUnit_Framework_TestCase
{

	function testNamedClassInstantiatedAutomatically()
	{
		$injector = new Phemto();
		$this->assertInstanceOf('phemto\acceptance\LoneClass', $injector->create('phemto\acceptance\LoneClass'));
	}

	function testWillUseOnlySubclassIfParentIsAbstract()
	{
		$injector = new Phemto();
		$this->assertInstanceOf(
			'phemto\acceptance\AbstractClass',
			$injector->create('phemto\acceptance\AbstractClass')
		);
	}

	function testCanBeConfiguredToPreferSpecificSubclass()
	{
		$injector = new Phemto();
		$injector->willUse('phemto\acceptance\SecondSubclass');
		$this->assertInstanceOf(
			'phemto\acceptance\SecondSubclass',
			$injector->create('phemto\acceptance\ClassWithManySubclasses')
		);
	}
}