<?php

namespace phemto\lifecycle;

use ReflectionClass;
use phemto\Context;

/**
 * Factory cached in session
 *
 * @package phemto\lifecycle
 */
class Sessionable extends Lifecycle
{
	private $slot;

	function __construct($class, $slot = false)
	{
		parent::__construct($class);
		$this->slot = $slot ? $slot : $class;
	}

	function instantiate(Context $context, $nesting)
	{
		@session_start();
		if (!isset($_SESSION[$this->slot])) {
			array_unshift($nesting, $this->class);

			$dependencies = $context->createDependencies(
				$context->repository()->getConstructorParameters($this->class),
				$nesting
			);

			$_SESSION[$this->slot] = call_user_func_array(
				array(new ReflectionClass($this->class), 'newInstance'),
				$dependencies
			);
		}

		return $_SESSION[$this->slot];
	}
}