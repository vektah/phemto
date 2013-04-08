<?php
namespace phemto\acceptance;
use phemto\Phemto;

interface InterfaceWithOneImplementation
{
}

class OnlyImplementation implements InterfaceWithOneImplementation
{
}

interface InterfaceWithManyImplementations
{
}

class FirstImplementation implements InterfaceWithManyImplementations
{
}

class SecondImplementation implements InterfaceWithManyImplementations
{
}

class CanAutomaticallyInstantiateKnownInterfacesTest extends \PHPUnit_Framework_TestCase
{

	function testInterfaceWithOnlyOneCandidateIsInstantiatedAutomatically()
	{
		$injector = new Phemto();
		$this->assertInstanceOf(
			'phemto\acceptance\OnlyImplementation',
			$injector->create('phemto\acceptance\InterfaceWithOneImplementation')
		);
	}

	function testWillThrowForUnknownClass()
	{
		$injector = new Phemto();
		$this->setExpectedException('phemto\exception\CannotFindImplementation');
		$injector->create('phemto\acceptance\NonExistent');
	}

	function testWillThrowIfInterfaceUnspecified()
	{
		$injector = new Phemto();
		$this->setExpectedException('phemto\exception\CannotDetermineImplementation');
		$injector->create('phemto\acceptance\InterfaceWithManyImplementations');
	}

	function testCanBeConfiguredToPreferSpecificImplementation()
	{
		$injector = new Phemto();
		$injector->willUse('phemto\acceptance\SecondImplementation');
		$this->assertInstanceOf(
			'phemto\acceptance\SecondImplementation',
			$injector->create('phemto\acceptance\InterfaceWithManyImplementations')
		);
	}
}