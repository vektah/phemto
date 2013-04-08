<?php
namespace phemto\acceptance;
use phemto\Phemto;

class NotWithoutMe
{
}

class NeedsInitToCompleteConstruction
{
	function init(NotWithoutMe $me)
	{
		@$this->me = $me;
	}
}

class CanUseSetterInjectionTest extends \PHPUnit_Framework_TestCase
{
	function testCanCallSettersToCompleteInitialisation()
	{
		$injector = new Phemto();
		$injector->forType('phemto\acceptance\NeedsInitToCompleteConstruction')->call('init');
		$expected = new NeedsInitToCompleteConstruction();
		$expected->init(new NotWithoutMe());
		$this->assertEquals(
			$injector->create('phemto\acceptance\NeedsInitToCompleteConstruction'),
			$expected
		);
	}
}