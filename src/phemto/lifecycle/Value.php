<?php
namespace phemto\lifecycle;

use phemto\Context;

/**
 * Always returns a set value.
 *
 * @package phemto\lifecycle
 */
class Value extends Lifecycle
{
	private $instance;

	function __construct($instance)
	{
		$this->instance = $instance;
		if(is_object($instance)) {
			$this->class = get_class($instance);
		}
	}

	function instantiate(Context $context, $nesting)
	{
		return $this->instance;
	}
}