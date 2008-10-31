<?php
require_once(dirname(__FILE__) . '/locator.php');

class CannotFindImplementation extends Exception {
    function __construct($interface) {
        $this->message = "No class registered for interface $interface";
    }
}

class MultipleImplementationsPossible extends Exception {

    var $message = 'Found [%d] candidates [%s] for requested [%s]. You must configure Phemto to use one of the candidates.';

    function __construct($interface, $candidates) {
        $this->message = sprintf(
            $this->message, 
            count($candidates), 
            implode(',', $candidates), 
            $interface);
    }
}

class Phemto {
    protected $registry = array();

    function willUse($service) {
        if (! $service instanceof PhemtoLocator) {
            $service = new Locator($service);
        }
        $this->registerLocator($service);
        return $this;
    }

    protected function registerLocator($locator) {
        $interfaces = $locator->getInterfaces();
        foreach ($interfaces as $interface) {
            $this->registry[$interface] = $locator;
        }
    }

    function instantiate($interface, $parameters = array()) {
        if( !array_key_exists($interface, $this->registry)) {
            $this->_registerUnknown($interface);
        }
        if (! isset($this->registry[$interface])) {
            throw new CannotFindImplementation($interface);
        }
        $locator = $this->registry[$interface];
        $dependencies = $this->instantiateDependencies(
        		$locator->getReflection(),
        		$locator->getParameters($parameters));
        return $locator->instantiate($dependencies);
    }

    protected function _registerUnknown($interface) {
        if(in_array($interface, get_declared_classes())) {
            $this->willUse($interface);
            return;
        }
        if( !in_array($interface, get_declared_interfaces())) {
            return;
        }
        $classes = $this->_getImplementationsOf($interface);
        if(1 == count($classes)) {
            $this->willUse(array_shift($classes));
            return;
        } else {
            throw(new MultipleImplementationsPossible($interface, $classes));
        }
    }

    protected function _getImplementationsOf($interface) {
        $implementations = array();
        foreach(get_declared_classes() as $class) {
            if(in_array($interface, class_implements($class))) {
                $implementations[] = $class;
            }
        }
        return $implementations;
    }
    
    protected function instantiateDependencies($reflection, $supplied) {
    	$dependencies = array();
        if ($constructor = $reflection->getConstructor()) {
            foreach ($constructor->getParameters() as $parameter) {
            	if ($interface = $parameter->getClass()) {
            		$dependencies[] = $this->instantiate($interface->getName());
                    // $this->_getParametersFor($interface)
            	} elseif (count($supplied)) {
            		$dependencies[] = array_shift($supplied);
            	}
            }
        }
        return $dependencies;
    }

    function pass($parameters) {
        $this->_parameters = $parameters;
        return $this;
    }
    
    function to($interface) {
        $this->registry[$interface]->setParameters($this->_parameters);
        return $this;
    }
}
?>