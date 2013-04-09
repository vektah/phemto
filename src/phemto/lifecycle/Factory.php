<?php
namespace phemto\lifecycle;

use phemto\Context;

/**
 * Creates a new object each time its required.
 *
 * @package phemto\lifecycle
 */
class Factory extends Lifecycle
{
	function instantiate(Context $context, $nesting)
	{
		array_unshift($nesting, $this->class);
		$dependencies = $context->createDependencies($context->repository()->getConstructorParameters($this->class), $nesting);
		return call_user_func_array(
			array(new \ReflectionClass($this->class), 'newInstance'),
			$dependencies
		);
	}
}