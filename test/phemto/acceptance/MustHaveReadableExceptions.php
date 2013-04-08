<?php

namespace phemto\acceptance;

use phemto\Phemto;
use phemto\exception\MissingDependency;


class ClassWithMissingDependency {
	public function __construct($d) { }
}

class DoubleNestedMissingDependency {
	public function __construct(ClassWithMissingDependency $d) { }
}

class MustHaveReadableExceptions extends \PHPUnit_Framework_TestCase {
	public function test_exception_message_for_missing_dependencies(){
		$di = new Phemto();

		$this->setExpectedException('phemto\exception\MissingDependency', 'While creating phemto\acceptance\ClassWithMissingDependency: Missing dependency \'d\'');
		$di->create('phemto\acceptance\ClassWithMissingDependency');
	}

	public function test_exception_message_for_double_nested_missing_dependencies()
	{
		$di = new Phemto();

		$this->setExpectedException(
			'phemto\exception\MissingDependency',
			'While creating phemto\acceptance\DoubleNestedMissingDependency: While creating phemto\acceptance\ClassWithMissingDependency: Missing dependency \'d\''
		);
		$di->create('phemto\acceptance\DoubleNestedMissingDependency');
	}
}