<?php
namespace phemto\repository;

use phemto\exception\SetterDoesNotExist;

/**
 * The actual DI container
 *
 * @package phemto\repository
 */
class ClassRepository
{
	/**
	 * @var ReflectionCache
	 */
	private static $reflection = false;

	function __construct()
	{
		if (!static::$reflection) {
			static::$reflection = new ReflectionCache();
		}
		static::$reflection->refresh();
	}

	function candidatesFor($interface)
	{
		return array_merge(
			static::$reflection->concreteSubgraphOf($interface),
			static::$reflection->implementationsOf($interface)
		);
	}

	function isSupertype($class, $type)
	{
		$supertypes = array_merge(
			array($class),
			static::$reflection->interfacesOf($class),
			static::$reflection->parentsOf($class)
		);

		return in_array($type, $supertypes);
	}

	function getConstructorParameters($class)
	{
		$reflection = static::$reflection->reflection($class);
		if ($constructor = $reflection->getConstructor()) {
			return $constructor->getParameters();
		}

		return array();
	}

	/**
	 * Gets a list of reflection paramaters for the given class+method
	 *
	 * @param string $class        The name of the class
	 * @param string $method       The name of the method
	 * @return \ReflectionParameter[] a list of paramaters
	 * @throws SetterDoesNotExist
	 */
	function getParameters($class, $method)
	{
		$reflection = static::$reflection->reflection($class);
		if (!$reflection->hasMethod($method)) {
			throw new SetterDoesNotExist();
		}

		return $reflection->getMethod($method)->getParameters();
	}
}