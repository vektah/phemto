<?php
namespace phemto\acceptance;

use phemto\Phemto;

interface Bare
{
}

class BareImplementation implements Bare
{
}

class WrapperForBare
{
	function __construct(Bare $bare) { $this->bare = $bare; }
}

class MustHaveCleanSyntaxForDecoratorsAndFiltersTest extends \PHPUnit_Framework_TestCase
{
	function testCanWrapWithDecorator()
	{
		$injector = new Phemto();
		$injector->whenCreating('phemto\acceptance\Bare')->wrapWith('phemto\acceptance\WrapperForBare');
		$this->assertEquals(
			$injector->create('phemto\acceptance\Bare'),
			new WrapperForBare(new BareImplementation())
		);
	}
}