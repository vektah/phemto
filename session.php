<?php
// This isn't serious code, just a demo.

require_once(dirname(__FILE__) . '/locator.php');

class SuperglobalSessionLocator extends PhemtoFactory {
	private $parameters;

	function __construct($class, $parameters = array()) {
		$this->__construct($class);
		$this->parameters = $parameters;
	}

	function instantiate($dependencies) {
		if (! isset($_SESSION[$this->class])) {
			$_SESSION[$this->class] = parent::instantiate($dependencies);
		}
		return $_SESSION[$this->class];
	}

	function getParameters($parameters) {
		return $this->parameters;
	}
}
?>