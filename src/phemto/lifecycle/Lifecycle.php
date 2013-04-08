<?php
namespace phemto\lifecycle;

use phemto\exception\CannotFindImplementation;

abstract class Lifecycle
{
	public $class;

	function __construct($class)
	{
		$this->class = $class;
		$this->triggerAutoload($class);
	}

	private function triggerAutoload($class)
	{
		if (!class_exists($class)) {
			throw new CannotFindImplementation($class);
		}
	}

	function isOneOf($candidates)
	{
		return in_array($this->class, $candidates);
	}

	abstract function instantiate($dependencies);
}