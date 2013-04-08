<?php
namespace phemto\acceptance;
use phemto\Phemto;

interface Hinted
{
}

class NeededForConstructor
{
}

class HintedConstructor implements Hinted
{
	function __construct(NeededForConstructor $one)
	{
		$this->one = $one;
	}
}

class HintedConstructorWithDependencyChoice implements Hinted
{
	function __construct(InterfaceWithManyImplementations $alternate)
	{
		$this->alternate = $alternate;
	}
}

class RepeatedHintConstructor
{
	function __construct(NeededForConstructor $first, NeededForConstructor $second)
	{
		$this->args = array($first, $second);
	}
}

class CanAutomaticallyInjectTypeHintedDependenciesTest extends \PHPUnit_Framework_TestCase
{
	function testSimpleDependenciesAreFulfilledAutomatically()
	{
		$injector = new Phemto();
		$this->assertEquals(
			$injector->create('phemto\acceptance\HintedConstructor'),
			new HintedConstructor(new NeededForConstructor())
		);
	}

	function testRepeatedHintJustGetsTwoSeparateInstances()
	{
		$injector = new Phemto();
		$this->assertEquals(
			$injector->create('phemto\acceptance\RepeatedHintConstructor'),
			new RepeatedHintConstructor(new NeededForConstructor(), new NeededForConstructor())
		);
	}
}