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

	function testInjectGlobally()
	{
		$injector = new Phemto();
		$statically = new Thing();

		$injector->willUse($statically);

		$this->assertSame(
			$statically,
			$injector->create('phemto\acceptance\Thing')
		);
	}

	public function testInjectGlobalDependency()
	{
		$injector = new Phemto();

		$statically = new Thing();

		$injector->willUse($statically);

		$thing = $injector->create('phemto\acceptance\WrapThing');

		$this->assertSame(
			$statically,
			$thing->thing
		);
	}

	public function testPreInjectedDependency()
	{
		$injector = new Phemto();

		$wrap = new WrapAnything(new Thing());

		$injector->willUse($wrap);

		$anything = $injector->create('phemto\acceptance\WrapAnything');
		$this->assertEquals(
			$wrap,
			$anything
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