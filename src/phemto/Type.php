<?php

namespace phemto;


class Type
{
	public $setters = array();

	function call($method)
	{
		array_unshift($this->setters, $method);
	}
}