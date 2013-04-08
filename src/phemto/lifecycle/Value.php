<?php
namespace phemto\lifecycle;

class Value extends Lifecycle
{
	private $instance;

	function __construct($instance)
	{
		$this->instance = $instance;
	}

	function instantiate($dependencies)
	{
		return $this->instance;
	}
}