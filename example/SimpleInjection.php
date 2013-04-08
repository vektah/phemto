<?php
use phemto\Phemto;

require_once(__DIR__ . '/../vendor/autoload.php');

$di = new Phemto();

class Config
{
	public $bar = 'asdf';
}
// Would be done during startup once we resolve which config to load.
$di->willUse('Config');

class Database
{
	public function __construct(Config $config)
	{
		$this->config = $config;
	}
}
$di->willUse('Database');

var_dump($di->create('Database'));