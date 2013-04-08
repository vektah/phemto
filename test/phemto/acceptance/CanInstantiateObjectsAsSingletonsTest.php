<?php
namespace phemto\acceptance;
use phemto\Phemto;
use phemto\lifecycle\Reused;

class CreateMeOnce
{
}

class CanInstantiateObjectsAsSingletonsTest extends \PHPUnit_Framework_TestCase
{
	function testSameInstanceCanBeReusedWithinFactory()
	{
		$injector = new Phemto();
		$injector->willUse(new Reused('phemto\acceptance\CreateMeOnce'));
		$this->assertSame(
			$injector->create('phemto\acceptance\CreateMeOnce'),
			$injector->create('phemto\acceptance\CreateMeOnce')
		);
	}
}