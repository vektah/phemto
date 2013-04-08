<?php

namespace phemto\lifecycle;

use ReflectionClass;

class Sessionable extends Lifecycle
{
	private $slot;

	function __construct($class, $slot = false)
	{
		parent::__construct($class);
		$this->slot = $slot ? $slot : $class;
	}

	function instantiate($dependencies)
	{
		@session_start();
		if (!isset($_SESSION[$this->slot])) {
			$_SESSION[$this->slot] = call_user_func_array(
				array(new ReflectionClass($this->class), 'newInstance'),
				$dependencies
			);
		}

		return $_SESSION[$this->slot];
	}
}

?>