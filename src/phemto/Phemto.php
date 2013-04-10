<?php
namespace phemto;

use phemto\exception\CannotDetermineImplementation;
use phemto\exception\MissingDependency;
use phemto\repository\ClassRepository;

/**
 * Forward facing api / dependency container.
 *
 * @package phemto
 */
class Phemto
{
	/**
	 * @var Context
	 */
	private $top;
	private $named_parameters = array();
	private $unnamed_parameters = array();

	function __construct()
	{
		$this->top = new Context($this);
	}

	function willUse($preference)
	{
		$this->top->willUse($preference);
	}

	/**
	 * @param $name
	 * @return Variable
	 */
	function forVariable($name)
	{
		return $this->top->forVariable($name);
	}

	/**
	 * @param $type
	 * @return Context
	 */
	function whenCreating($type)
	{
		return $this->top->whenCreating($type);
	}

	/**  */
	function forType($type)
	{
		return $this->top->forType($type);
	}

	/**
	 * @return IncomingParameters
	 */
	function fill()
	{
		$names = func_get_args();

		return new IncomingParameters($names, $this);
	}

	function with()
	{
		$values = func_get_args();
		$this->unnamed_parameters = array_merge($this->unnamed_parameters, $values);

		return $this;
	}

	function create()
	{
		$values = func_get_args();
		$type = array_shift($values);
		$this->unnamed_parameters = array_merge($this->unnamed_parameters, $values);
		$this->repository = new ClassRepository();
		$object = $this->top->create($type);
		$this->named_parameters = array();

		return $object;
	}

	/**
	 * Call a method called $method on the object instance $instance.
	 *
	 * @param object $instance
	 * @param string $method
	 *
	 * @return mixed the result from the call.
	 */
	public function call($instance, $method)
	{
		return $this->top->call($instance, $method);
	}

	function pickFactory($type, $candidates)
	{
		throw new CannotDetermineImplementation($type);
	}

	function settersFor($class)
	{
		return array();
	}

	function wrappersFor($type)
	{
		return array();
	}

	function useParameters($parameters)
	{
		$this->named_parameters = array_merge($this->named_parameters, $parameters);
	}

	/**
	 * @param \ReflectionParameter $parameter
	 * @param $nesting
	 * @return mixed
	 * @throws exception\MissingDependency
	 */
	function instantiateParameter($parameter, $nesting)
	{
		if (isset($this->named_parameters[$parameter->getName()])) {
			return $this->named_parameters[$parameter->getName()];
		}
		if ($value = array_shift($this->unnamed_parameters)) {
			return $value;
		}
		if ($parameter->isDefaultValueAvailable()) {
			return $parameter->getDefaultValue();
		}
		throw new MissingDependency("Missing dependency '{$parameter->getName()}'");
	}

	function repository()
	{
		return $this->repository;
	}
}