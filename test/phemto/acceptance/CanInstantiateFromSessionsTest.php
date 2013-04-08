<?php
namespace phemto\acceptance;
use phemto\Phemto;
use phemto\lifecycle\Sessionable;


class SerialiseMe
{
}

class HoldsSessionable
{
	public $dependency;

	function __construct(SerialiseMe $dependency)
	{
		$this->dependency = $dependency;
	}
}

class CanInstantiateFromSessionsTest extends \PHPUnit_Framework_TestCase
{
	function tearDown()
	{
		$_SESSION['slot'] = false;
		$_SESSION['phemto\acceptance\SerialiseMe'] = false;
	}

	function testSessionableInstanceWrittenToSession()
	{
		$injector = new Phemto();
		$injector->willUse(new Sessionable('phemto\acceptance\SerialiseMe'));
		$this->assertSame($injector->create('phemto\acceptance\SerialiseMe'), $_SESSION['phemto\acceptance\SerialiseMe']);
	}

	function testCanOverideSessionSlot()
	{
		$injector = new Phemto();
		$injector->willUse(new Sessionable('phemto\acceptance\SerialiseMe', 'slot'));
		$this->assertSame($injector->create('phemto\acceptance\SerialiseMe'), $_SESSION['slot']);
	}

	function testSessionableInstancePulledFromSessionIfExists()
	{
		$_SESSION['slot'] = new SerialiseMe();
		$injector = new Phemto();
		$injector->willUse(new Sessionable('phemto\acceptance\SerialiseMe', 'slot'));
		$this->assertSame($injector->create('phemto\acceptance\SerialiseMe'), $_SESSION['slot']);
	}

	function testSameInstanceFromSessionWithinSameProcess()
	{
		$injector = new Phemto();
		$injector->willUse(new Sessionable('phemto\acceptance\SerialiseMe'));
		$this->assertSame($injector->create('phemto\acceptance\SerialiseMe'), $injector->create('phemto\acceptance\SerialiseMe'));
	}

	function TODO_testSessionableWorksWithinContext()
	{
		$injector = new Phemto();
		$injector->whenCreating('phemto\acceptance\HoldsSessionable')->willUse(new Sessionable('phemto\acceptance\SerialiseMe'));
		$holder = $injector->create('phemto\acceptance\HoldsSessionable');
		$this->assertSame($holder->dependency, $_SESSION['SerialiseMe']);
	}
}