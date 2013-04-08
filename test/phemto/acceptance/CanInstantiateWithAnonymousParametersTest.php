<?php
namespace phemto\acceptance;
use phemto\Phemto;

class ClassWithAnyOldParameters
{
	function __construct($a, $b)
	{
		@$this->a = $a;
		@$this->b = $b;
	}
}

class CanInstantiateWithAnonymousParametersTest extends \PHPUnit_Framework_TestCase
{
	function testCanFillMissingParametersWithExplicitValues()
	{
		$injector = new Phemto();
		$this->assertEquals(
			$injector->with(3, 5)->create('phemto\acceptance\ClassWithAnyOldParameters'),
			new ClassWithAnyOldParameters(3, 5)
		);
	}

	function testCanUseShorterSyntacticForm()
	{
		$injector = new Phemto();
		$this->assertEquals(
			$injector->create('phemto\acceptance\ClassWithAnyOldParameters', 3, 5),
			new ClassWithAnyOldParameters(3, 5)
		);
	}
}