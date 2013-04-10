<?php

namespace phemto\acceptance;

use phemto\Phemto;

class RandomDependency {

}

class ClassWithMethods {
	public $dependency;

	public function aMethod(RandomDependency $dependency)
	{
		$this->dependency = $dependency;
		return 'ok';
	}
}

class CanUseMethodVariableInjectionTest extends \PHPUnit_Framework_TestCase {
	public function testCanCallMethodWithParam()
	{
		$di = new Phemto();

		$instance = $di->create('phemto\acceptance\ClassWithMethods');

		$this->assertEquals('ok', $di->call($instance, 'aMethod'));

		$this->assertEquals(new RandomDependency(), $instance->dependency);
	}
}