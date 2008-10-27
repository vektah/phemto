<?php
require_once(dirname(__FILE__) . '/../phemto.php');
require_once(dirname(__FILE__) . '/../lazy.php');

if (isset($argv[3]) && $argv[1] == 'preload') {
	InterfaceCache::setPath(dirname(__FILE__) . '/temp/cache');
    $injector = new Phemto();
    $injector->register(new LazyInclude(
            $argv[3],
            dirname(__FILE__) . '/' . $argv[2]));
	print_r($injector->instantiate($argv[3]));
	exit();
}

class WhenCachingInterfaces extends UnitTestCase {
	function setUp() {
		InterfaceCache::setPath(dirname(__FILE__) . '/temp/cache');
		InterfaceCache::clear();
	}
	
	function tearDown() {
		InterfaceCache::clear();
	}
	
	function testCacheIsWriteable() {
		file_put_contents(dirname(__FILE__) . '/temp/cache', 'Hello');
		$this->assertEqual(
				file_get_contents(dirname(__FILE__) . '/temp/cache'),
				'Hello',
				'You need to make the temp folder writeable for this test');
	}
	
	function testCanRestoreSimpleInterface() {
		InterfaceCache::setInterfaces('source', array('interface'));
		$this->assertIdentical(
				InterfaceCache::getInterfaces('source'),
				array('interface'));
	}
}

class WhenLazyIncluding extends UnitTestCase {
	function setUp() {
		InterfaceCache::setPath(dirname(__FILE__) . '/temp/cache');
		InterfaceCache::clear();
	}
	
	function tearDown() {
		InterfaceCache::clear();
	}
	
	function testCodeIsLoadedFirstTimeRegardless() {
        $injector = new Phemto();
        $injector->register(new LazyInclude(
                'ClassOne',
                dirname(__FILE__) . '/samples/sample_1.php'));
        $this->assertTrue(class_exists('ClassOne'));
	}

    function testCodeIsNotLoadedUntilTheInstantiateCall() {
		$preload = __FILE__ . ' preload samples/sample_2.php ClassTwo';
		`php $preload`;
		InterfaceCache::refresh();
        $injector = new Phemto();
        $injector->register(new LazyInclude(
                'ClassTwo',
                dirname(__FILE__) . '/samples/sample_2.php'));
        $this->assertFalse(class_exists('ClassTwo'));
        $this->assertIsa($injector->instantiate('ClassTwo'), 'ClassTwo');
    }

	function testSourceInterfaceReloadedIfNewerThanCacheVersion() {
		$this->copy('samples/sample_3.php', 'temp/sample_3.php');
		$preload = __FILE__ . ' preload temp/sample_3.php ClassThree';
		`php $preload`;
		InterfaceCache::refresh();
		sleep(1);
		$this->copy('samples/sample_3.php', 'temp/sample_3.php');
        $injector = new Phemto();
        $injector->register(new LazyInclude(
                'ClassThree',
                dirname(__FILE__) . '/temp/sample_3.php'));
        $this->assertTrue(class_exists('ClassThree'));
	}
	
	function copy($source, $destination) {
		$source = dirname(__FILE__) . '/' . $source;
		$destination = dirname(__FILE__) . '/' . $destination;
		file_put_contents($destination, file_get_contents($source));
	}
}
?>