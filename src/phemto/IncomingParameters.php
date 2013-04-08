<?php
namespace phemto;

class IncomingParameters
{
	private $injector;

	/**
	 * @param array $names
	 * @param Phemto $injector
	 */
	function __construct($names, $injector)
	{
		$this->names = $names;
		$this->injector = $injector;
	}

	function with()
	{
		$values = func_get_args();
		$this->injector->useParameters(array_combine($this->names, $values));

		return $this->injector;
	}
}