<?php
namespace phemto\lifecycle;

/**
 * Factory + Singleton lifecycle provider.
 *
 * @package phemto\lifecycle
 */
class Reused extends Lifecycle
{
	private $instance;

	function instantiate($dependencies)
	{
		if (!isset($this->instance)) {
			$this->instance = call_user_func_array(
				array(new \ReflectionClass($this->class), 'newInstance'),
				$dependencies
			);
		}

		return $this->instance;
	}
}