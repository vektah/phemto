<?php
namespace phemto;

use phemto\exception\CannotFindImplementation;
use phemto\exception\MissingDependency;
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

	/**
	 * A map of class name to lifecycle objects.
	 *
	 * @var Lifecycle[]
	 */
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
		$this->registry[$lifecycle->class] = $lifecycle;
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

		try {
			if ($wrapper = $context->hasWrapper($type, $nesting)) {
				array_unshift($nesting, $wrapper);
				return $this->create($wrapper, $nesting);
			}
			$instance = $lifecycle->instantiate($context, $nesting);
		} catch (MissingDependency $e) {
			$e->prependMessage("While creating $type: ");
			throw $e;
		}
		$this->invokeSetters($context, $nesting, $lifecycle->class, $instance);

		return $instance;
	}

	/**
	 * Pick a lifecycle for the given type from the available candidates.
	 *
	 * @param string   $type          type
	 * @param string[] $candidates    list of types that can satisfy this type
	 * @return Lifecycle              A lifecycle object for this type
	 *
	 * @throws exception\CannotFindImplementation
	 */
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
			array_unshift($nesting, $class);

			$context->invoke(
				$instance,
				$setter,
				$context->createDependencies(
					$this->repository()->getParameters($class, $setter),
					$nesting
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
			$values[] = $this->instantiateParameter($parameter, $nesting);
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

		try {
			if ($hint) {
				return $this->create($hint->getName(), $nesting);
			} elseif (isset($this->variables[$parameter->getName()])) {
				if ($this->variables[$parameter->getName()]->preference instanceof Lifecycle) {
					return $this->variables[$parameter->getName()]->preference->instantiate($this, $nesting);
				} elseif (!is_string($this->variables[$parameter->getName()]->preference)) {
					return $this->variables[$parameter->getName()]->preference;
				}

				return $this->create($this->variables[$parameter->getName()]->preference, $nesting);
			}
		} catch (MissingDependency $e) {
			if($parameter->getClass()) {
				$e->prependMessage("While creating {$parameter->getClass()->getName()}: ");
			} else {
				$e->prependMessage("While creating {$parameter->getName()}: ");
			}
			throw $e;
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

	/**
	 * Picks the best candidate from a list.
	 *
	 * @param string[]  $candidates  A list of possible candidates
	 * @return null
	 */
	private function preferFrom($candidates)
	{
		foreach($candidates as $candidate) {
			if(isset($this->registry[$candidate])) {
				return $this->registry[$candidate];
			}
		}

		return false;
	}

	/**
	 * @return ClassRepository
	 */
	function repository()
	{
		return $this->parent->repository();
	}
}