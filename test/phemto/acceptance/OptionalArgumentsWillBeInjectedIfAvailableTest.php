<?php
namespace phemto\acceptance;
use phemto\Phemto;

class MaybeThis
{
}

class AvailableOptionalHintedArgument
{
	public $maybe;

	function __construct(MaybeThis $maybe = null)
	{
		$this->maybe = $maybe;
	}
}

class MissingOptionalHintedArguments
{
	public $maybe;

	function __construct(MaybeNot $maybe = null)
	{
		$this->maybe = $maybe;
	}
}

class OptionalArgument
{
	public $maybe;

	function __construct($maybe = null)
	{
		$this->maybe = $maybe;
	}
}

class ManyOptionalArguments
{
	public $maybe, $never, $unlikely;

	function __construct($maybe = null, $never = null, $unlikely = null)
	{
		$this->maybe = $maybe;
		$this->never = $never;
		$this->unlikely = $unlikely;
	}
}

class OptionalArgumentsInSetter
{
	public $maybe, $never, $unlikely;

	function readyToGo($maybe = null, $never = null, $unlikely = null)
	{
		$this->maybe = $maybe;
		$this->never = $never;
		$this->unlikely = $unlikely;
	}
}

class OptionalArgumentsWillBeInjectedIfAvailableTest extends \PHPUnit_Framework_TestCase
{
	function testHintedOptionalArgumentWillBeUsed()
	{
		$injector = new Phemto();
		$this->assertEquals(
			$injector->create('phemto\acceptance\AvailableOptionalHintedArgument'),
			new AvailableOptionalHintedArgument(new MaybeThis())
		);
	}

	function testCanInstantiateWithMissingOptionalArguments()
	{
		$injector = new Phemto();
		$this->assertEquals(
			$injector->create('phemto\acceptance\MissingOptionalHintedArguments'),
			new MissingOptionalHintedArguments()
		);
	}

	function testOptionalArgumentWillBeInjectedIfNamed()
	{
		$injector = new Phemto();
		$injector->forVariable('maybe')->willUse('phemto\acceptance\MaybeThis');
		$this->assertEquals(
			$injector->create('phemto\acceptance\OptionalArgument'),
			new OptionalArgument(new MaybeThis())
		);
	}

	function testUnnamedArgumentSkipped()
	{
		$injector = new Phemto();
		$this->assertEquals(
			$injector->create('phemto\acceptance\OptionalArgument'),
			new OptionalArgument(null)
		);
	}

	function testInjectionDoesNotStopAtFirstMissingArgument()
	{
		$injector = new Phemto();
		$injector->forVariable('maybe')->willUse('phemto\acceptance\MaybeThis');
		$injector->forVariable('unlikely')->willUse('phemto\acceptance\MaybeThis');
		$this->assertEquals(
			$injector->create('phemto\acceptance\ManyOptionalArguments'),
			new ManyOptionalArguments(new MaybeThis(), null, new MaybeThis())
		);
	}

	function testSetterInjectionStopsAtFirstMissingArgument()
	{
		$injector = new Phemto();
		$injector->forVariable('maybe')->willUse('phemto\acceptance\MaybeThis');
		$injector->forVariable('unlikely')->willUse('phemto\acceptance\MaybeThis');
		$injector->forType('phemto\acceptance\OptionalArgumentsInSetter')->call('readyToGo');
		$expected = new OptionalArgumentsInSetter();
		$expected->readyToGo(new MaybeThis(), null, new MaybeThis());
		$this->assertEquals($injector->create('phemto\acceptance\OptionalArgumentsInSetter'), $expected);
	}
}