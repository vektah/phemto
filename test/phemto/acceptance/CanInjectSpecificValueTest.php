<?php
namespace phemto\acceptance;
use phemto\Phemto;

class WrapThing
{
	function __construct(Thing $thing) { $this->thing = $thing; }
}

class WrapAnything
{
	function __construct($thing) { $this->thing = $thing; }
}

class Thing
{
}

class CanInjectSpecificValueTest extends \PHPUnit_Framework_TestCase
{
	function testCanInjectSpecificInstance()
	{
		$injector = new Phemto();
		$injector->willUse(new Thing());
		$this->assertEquals(
			$injector->create('phemto\acceptance\WrapThing'),
			new WrapThing(new Thing())
		);
	}

	function testInjectingSpecificInstanceForNamedVariable()
	{
		$injector = new Phemto();
		$injector->forVariable('thing')->willUse(new Thing());
		$this->assertEquals(
			$injector->create('phemto\acceptance\WrapAnything'),
			new WrapAnything(new Thing())
		);
	}

	function testInjectingNonObject()
	{
		$injector = new Phemto();
		$injector->forVariable('thing')->willUse(100);
		$this->assertEquals(
			$injector->create('phemto\acceptance\WrapAnything'),
			new WrapAnything(100)
		);
	}

	function testInjectingString()
	{
		$injector = new Phemto();
		$injector->forVariable('thing')->useString('100');
		$this->assertEquals(
			$injector->create('phemto\acceptance\WrapAnything'),
			new WrapAnything('100')
		);
	}
}