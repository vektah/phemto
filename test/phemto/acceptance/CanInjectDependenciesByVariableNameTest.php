<?php
namespace phemto\acceptance;
use phemto\Phemto;

class NeededForFirst
{
}

class NeededForSecond
{
}

class VariablesInConstructor
{
	function __construct($first, $second)
	{
		$this->args = array($first, $second);
	}
}

class CanInjectDependenciesByVariableNameTest extends \PHPUnit_Framework_TestCase
{
	function testExplicitlyNamedVariables()
	{
		$injector = new Phemto();
		$injector->forVariable('first')->willUse('phemto\acceptance\NeededForFirst');
		$injector->forVariable('second')->willUse('phemto\acceptance\NeededForSecond');
		$this->assertEquals(
			$injector->create('phemto\acceptance\VariablesInConstructor'),
			new VariablesInConstructor(new NeededForFirst(), new NeededForSecond())
		);
	}
}