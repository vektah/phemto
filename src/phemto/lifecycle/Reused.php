<?php
namespace phemto\lifecycle;

use phemto\Context;

/**
 * Factory + Singleton lifecycle provider.
 *
 * @package phemto\lifecycle
 */
class Reused extends Lifecycle
{
	private $instance;

	function instantiate(Context $context, $nesting)
	{
		if (!isset($this->instance)) {
			array_unshift($nesting, $this->class);

			$dependencies = $context->createDependencies(
				$context->repository()->getConstructorParameters($this->class),
				$nesting
			);
			$this->instance = call_user_func_array(
				array(new \ReflectionClass($this->class), 'newInstance'),
				$dependencies
			);
		}

		return $this->instance;
	}
}