<?php
namespace phemto;

use phemto\exception\CannotFindImplementation;
use phemto\lifecycle\Factory;
use phemto\lifecycle\Lifecycle;
use phemto\lifecycle\Value;
use phemto\repository\ClassRepository;

class Context
{
	/**
	 * @var Context|Phemto
	 */
	private $parent;

	private $registry = array();
	/**
	 * @var Variable[]
	 */
	private $variables = array();
	/**
	 * @var Context[]
	 */
	private $contexts = array();
	/**
	 * @var Type[]
	 */
	private $types = array();

	private $wrappers = array();

	function __construct($parent)
	{
		$this->parent = $parent;
	}

	function willUse($preference)
	{
		if ($preference instanceof Lifecycle) {
			$lifecycle = $preference;
		} elseif (is_object($preference)) {
			$lifecycle = new Value($preference);
		} else {
			$lifecycle = new Factory($preference);
		}
		array_unshift($this->registry, $lifecycle);
	}

	function forVariable($name)
	{
		return $this->variables[$name] = new Variable($this);
	}

	function whenCreating($type)
	{
		if (!isset($this->contexts[$type])) {
			$this->contexts[$type] = new Context($this);
		}

		return $this->contexts[$type];
	}

	function forType($type)
	{
		if (!isset($this->types[$type])) {
			$this->types[$type] = new Type();
		}

		return $this->types[$type];
	}

	function wrapWith($type)
	{
		array_push($this->wrappers, $type);
	}

	function create($type, $nesting = array())
	{
		$lifecycle = $this->pickFactory($type, $this->repository()->candidatesFor($type));
		$context = $this->determineContext($lifecycle->class);
		if ($wrapper = $context->hasWrapper($type, $nesting)) {
			return $this->create($wrapper, $this->cons($wrapper, $nesting));
		}
		$instance = $lifecycle->instantiate(
			$context->createDependencies(
				$this->repository()->getConstructorParameters($lifecycle->class),
				$this->cons($lifecycle->class, $nesting)
			)
		);
		$this->invokeSetters($context, $nesting, $lifecycle->class, $instance);

		return $instance;
	}

	function pickFactory($type, $candidates)
	{
		if (count($candidates) == 0) {
			throw new CannotFindImplementation($type);
		} elseif ($preference = $this->preferFrom($candidates)) {
			return $preference;
		} elseif (count($candidates) == 1) {
			return new Factory($candidates[0]);
		} else {
			return $this->parent->pickFactory($type, $candidates);
		}
	}

	function hasWrapper($type, $already_applied)
	{
		foreach ($this->wrappersFor($type) as $wrapper) {
			if (!in_array($wrapper, $already_applied)) {
				return $wrapper;
			}
		}

		return false;
	}

	private function invokeSetters($context, $nesting, $class, $instance)
	{
		foreach ($context->settersFor($class) as $setter) {
			$context->invoke(
				$instance,
				$setter,
				$context->createDependencies(
					$this->repository()->getParameters($class, $setter),
					$this->cons($class, $nesting)
				)
			);
		}
	}

	private function settersFor($class)
	{
		$setters = isset($this->types[$class]) ? $this->types[$class]->setters : array();

		return array_values(
			array_unique(
				array_merge(
					$setters,
					$this->parent->settersFor($class)
				)
			)
		);
	}

	function wrappersFor($type)
	{
		return array_values(
			array_merge(
				$this->wrappers,
				$this->parent->wrappersFor($type)
			)
		);
	}

	function createDependencies($parameters, $nesting)
	{
		$values = array();
		foreach ($parameters as $parameter) {
			try {
				$values[] = $this->instantiateParameter($parameter, $nesting);
			} catch (Exception $e) {
				if ($parameter->isOptional()) {
					break;
				}
				throw $e;
			}
		}

		return $values;
	}

	/**
	 * @param \ReflectionParameter $parameter
	 * @param $nesting
	 * @return mixed|Value
	 */
	private function instantiateParameter($parameter, $nesting)
	{
		$hint = null;
		try {
			$hint = $parameter->getClass();
		} catch(\ReflectionException $e) {}

		if ($hint) {
			return $this->create($hint->getName(), $nesting);
		} elseif (isset($this->variables[$parameter->getName()])) {
			if ($this->variables[$parameter->getName()]->preference instanceof Lifecycle) {
				return $this->variables[$parameter->getName()]->preference->instantiate(array());
			} elseif (!is_string($this->variables[$parameter->getName()]->preference)) {
				return $this->variables[$parameter->getName()]->preference;
			}

			return $this->create($this->variables[$parameter->getName()]->preference, $nesting);
		}

		return $this->parent->instantiateParameter($parameter, $nesting);
	}

	private function determineContext($class)
	{
		foreach ($this->contexts as $type => $context) {
			if ($this->repository()->isSupertype($class, $type)) {
				return $context;
			}
		}

		return $this;
	}

	private function invoke($instance, $method, $arguments)
	{
		call_user_func_array(array($instance, $method), $arguments);
	}

	private function preferFrom($candidates)
	{
		foreach ($this->registry as $preference) {
			if ($preference->isOneOf($candidates)) {
				return $preference;
			}
		}

		return false;
	}

	private function cons($head, $tail)
	{
		array_unshift($tail, $head);

		return $tail;
	}

	/**
	 * @return ClassRepository
	 */
	function repository()
	{
		return $this->parent->repository();
	}
}