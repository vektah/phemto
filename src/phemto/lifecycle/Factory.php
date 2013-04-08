<?php
namespace phemto\lifecycle;

/**
 * Creates a new object each time its required.
 *
 * @package phemto\lifecycle
 */
class Factory extends Lifecycle
{
	function instantiate($dependencies)
	{
		return call_user_func_array(
			array(new \ReflectionClass($this->class), 'newInstance'),
			$dependencies
		);
	}
}