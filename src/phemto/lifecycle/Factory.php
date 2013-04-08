<?php
namespace phemto\lifecycle;

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