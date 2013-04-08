<?php

namespace phemto;

use phemto\repository\ReflectionCache;

interface A {}
interface B {}
class Imp1 implements A {}
class Imp2 implements A, B {}

class ReflectionCacheTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @var ReflectionCache
	 */
	public $cache;

	public function setUp(){
		$this->cache = new ReflectionCache();
		$this->cache->refresh();
	}

	function testCanFindImplementationsFromInterface()
	{
		$this->assertEquals(
			array('phemto\Imp1', 'phemto\Imp2'),
			$this->cache->implementationsOf('phemto\A')
		);
	}

	function testCanFindInterfacesOfImp1()
	{
		$this->assertEquals(array('phemto\A'), $this->cache->interfacesOf('phemto\Imp1'));
	}

	function testCanFindInterfacesOfImp2()
	{
		$this->assertEquals(array('phemto\A', 'phemto\B'), $this->cache->interfacesOf('phemto\Imp2'));
	}
}

?>