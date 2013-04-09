<?php
namespace phemto\acceptance;
use phemto\Phemto;

class UsualImplementation
{
}

class SpecialImplementation extends UsualImplementation
{
}

class ClassWithUsual
{
	function __construct(UsualImplementation $a) { $this->a = $a; }
}

interface SpecialInterface
{
}

class ClassWithSpecial implements SpecialInterface
{
	function __construct(UsualImplementation $a) { $this->a = $a; }
}

class CanUseDifferentDependencySetWithinAnInterfaceTest extends \PHPUnit_Framework_TestCase
{
	function testCanOverridePreferenceWhenInstantiatingSpecificInstance()
	{
		$injector = new Phemto();
		$injector->whenCreating('phemto\acceptance\ClassWithSpecial')->willUse('phemto\acceptance\SpecialImplementation');
		$injector->willUse('phemto\acceptance\UsualImplementation');
		$this->assertEquals(
			$injector->create('phemto\acceptance\ClassWithUsual'),
			new ClassWithUsual(new UsualImplementation())
		);
		$this->assertEquals(
			$injector->create('phemto\acceptance\ClassWithSpecial'),
			new ClassWithSpecial(new SpecialImplementation())
		);
	}

	function testCanOverridePreferenceWhenInstantiatingFromAnInterface()
	{
		$injector = new Phemto();
		$injector->whenCreating('phemto\acceptance\SpecialInterface')->willUse('phemto\acceptance\SpecialImplementation');
		$injector->willUse('phemto\acceptance\UsualImplementation');
		$special = $injector->create('phemto\acceptance\SpecialInterface');
		$this->assertEquals(
			$special,
			new ClassWithSpecial(new SpecialImplementation())
		);
	}
}