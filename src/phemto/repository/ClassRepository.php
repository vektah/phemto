<?php
namespace phemto\repository;

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

	function getParameters($class, $method)
	{
		$reflection = static::$reflection->reflection($class);
		if (!$reflection->hasMethod($method)) {
			throw new SetterDoesNotExist();
		}

		return $reflection->getMethod($method)->getParameters();
	}
}