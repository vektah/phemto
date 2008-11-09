<?php
class CannotFindImplementation extends Exception { }
class CannotDetermineImplementation extends Exception { }

class Phemto {
    private $registry = array();
    private $variables = array();
    
    function willUse($preference) {
        $lifecycle = $preference instanceof Lifecycle ? $preference : new Factory($preference);
        array_unshift($this->registry, $lifecycle);
    }
    
    function forVariable($name) {
        return $this->variables[$name] = new Variable();
    }
    
    function whenCreating($interface) {
    }
    
    function call($method) {
    }
    
    function wrap($interface) {
    }
    
    function with($decorator) {
    }
    
    function create($interface, $repository = false) {
        $repository = $repository ? $repository : new ClassRepository();
        $lifecycle = $this->pick($repository->candidatesFor($interface));
        return $lifecycle->instantiate($this->createDependencies($lifecycle->class, $repository));
    }

    private function pick($candidates) {
        if (count($candidates) == 0) {
            throw new CannotFindImplementation();
        } elseif ($preference = $this->preferFrom($candidates)) {
            return $preference;
        } elseif (count($candidates) == 1) {
            return new Factory($candidates[0]);
        } else {
            throw new CannotDetermineImplementation();
        }
    }
    
    private function createDependencies($class, $repository) {
        $dependencies = array();
        foreach ($repository->getConstructorParameters($class) as $parameter) {
            $dependencies[] = $this->instantiateParameter($parameter, $repository);
        }
        return $dependencies;
    }
    
    private function instantiateParameter($parameter, $repository) {
        try {
            if ($hint = $parameter->getClass()) {
                return $this->create($hint->getName(), $repository);
            }
        } catch (Exception $e) {
            return $this->create($this->variables[$parameter->getName()]->interface, $repository);
        }
    }
    
    private function preferFrom($candidates) {
        foreach ($this->registry as $preference) {
            if ($preference->isOneOf($candidates)) {
                return $preference;
            }
        }
        return false;
    }
}

class Variable {
    public $interface;

    function willUse($interface) {
        $this->interface = $interface;
    }
}

abstract class Lifecycle {
    public $class;

	function __construct($class) {
		$this->class = $class;
	}
    
    function isOneOf($candidates) {
        return in_array($this->class, $candidates);
    }

    abstract function instantiate($dependencies);
}

class Factory extends Lifecycle {
	function instantiate($dependencies) {
		return call_user_func_array(
				array(new ReflectionClass($this->class), 'newInstance'),
				$dependencies);
	}
}

class Singleton extends Lifecycle {
    private $instance;
    
	function instantiate($dependencies) {
        if (! isset($this->instance)) {
            $this->instance = call_user_func_array(
                    array(new ReflectionClass($this->class), 'newInstance'),
                    $dependencies);
        }
        return $this->instance;
	}
}

class Sessionable extends Lifecycle {
    private $slot;
    
    function __construct($slot, $class) {
        $this->slot = $slot;
        parent::__construct($class);
    }
    
	function instantiate($dependencies) {
        if (! isset($_SESSION[$this->slot])) {
            $_SESSION[$this->slot] = call_user_func_array(
                    array(new ReflectionClass($this->class), 'newInstance'),
                    $dependencies);
        }
        return $_SESSION[$this->slot];
	}
}

class ClassRepository {
    private static $reflection = false;
    
    function __construct() {
        if (! self::$reflection) {
            self::$reflection = new ReflectionCache();
        }
        self::$reflection->refresh();
    }
    
    function candidatesFor($interface) {
        return array_merge(
                self::$reflection->concreteSubgraphOf($interface),
                self::$reflection->implementationsOf($interface));
    }
    
    function getConstructorParameters($class) {
        $reflection = self::$reflection->reflection($class);
        if ($constructor = $reflection->getConstructor()) {
            return $constructor->getParameters();
        }
        return array();
    }
}

class ReflectionCache {
    private $implementations_of = array();
    private $interfaces_of = array();
    private $reflections = array();
    private $subclasses = array();
    
    function refresh() {
        $this->buildIndex(array_diff(get_declared_classes(), $this->indexed()));
        $this->subclasses = array();
    }
    
    function implementationsOf($interface) {
        return isset($this->implementations_of[$interface]) ?
                $this->implementations_of[$interface] : array();
    }
    
    function concreteSubgraphOf($class) {
        if (! class_exists($class)) {
            return array();
        }
        if (! isset($this->subclasses[$class])) {
            $this->subclasses[$class] = $this->isConcrete($class) ? array($class) : array();
            foreach ($this->indexed() as $candidate) {
                if (is_subclass_of($candidate, $class) && $this->isConcrete($candidate)) {
                    $this->subclasses[$class][] = $candidate;
                }
            }
        }
        return $this->subclasses[$class];
    }
    
    function reflection($class) {
        if (! isset($this->reflections[$class])) {
            $this->reflections[$class] = new ReflectionClass($class);
        }
        return $this->reflections[$class];
    }
    
    private function isConcrete($class) {
        return ! $this->reflection($class)->isAbstract();
    }
    
    private function indexed() {
        return array_keys($this->interfaces_of);
    }
    
    private function buildIndex($classes) {
        foreach ($classes as $class) {
            $interfaces = class_implements($class);
            $this->interfaces_of[$class] = $interfaces;
            foreach ($interfaces as $interface) {
                $this->indexImplementation($interface, $class);
            }
        }
    }
    
    private function indexImplementation($interface, $class) {
        if (! isset($this->implementations_of[$interface])) {
            $this->implementations_of[$interface] = array();
        }
        $this->implementations_of[$interface][] = $class;
        $this->implementations_of[$interface] =
                array_values(array_unique($this->implementations_of[$interface]));
    }
}
?>