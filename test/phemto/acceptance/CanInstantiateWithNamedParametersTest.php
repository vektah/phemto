<?php
namespace phemto\acceptance;
use phemto\Phemto;

class ClassWithParameters
{
	function __construct($a, $b)
	{
		@$this->a = $a;
		@$this->b = $b;
	}
}

class CanInstantiateWithNamedParametersTest extends \PHPUnit_Framework_TestCase
{
	function testCanFillMissingParametersWithExplicitValues()
	{
		$injector = new Phemto();
		$this->assertEquals(
			$injector->fill('a', 'b')->with(3, 5)->create('phemto\acceptance\ClassWithParameters'),
			new ClassWithParameters(3, 5)
		);
	}
}
