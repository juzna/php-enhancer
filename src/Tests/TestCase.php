<?php

namespace Tests;

use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class TestCase extends \PHPUnit_Framework_TestCase
{

	/**
	 * @var \Enhancer\Loaders\ClassLoader
	 */
	private $loader;



	/**
	 * @param $dir
	 * @param string $namespace
	 */
	protected function enhancerAutoload($dir, $namespace = '')
	{
		// Register autoloader
		$this->loader = new \Enhancer\Loaders\ClassLoader;
		$this->loader->register(true);
		$this->loader->add($namespace, $dir);
	}



	public function tearDown()
	{
		if ($this->loader) {
			$this->loader->unregister();
		}
	}

}
