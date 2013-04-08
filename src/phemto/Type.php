<?php

namespace phemto;

/**
 * Used in calling setters for a class.
 *
 * @package phemto
 */
class Type
{
	public $setters = array();

	function call($method)
	{
		array_unshift($this->setters, $method);
	}
}