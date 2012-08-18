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
	private $loaders = array();



	/**
	 * @param $dir
	 * @param string $namespace
	 */
	protected function enhancerAutoload($dir, $namespace = '')
	{
		// Register autoloader
		$this->loaders[] = $loader = new \Enhancer\Loaders\ClassLoader;
		$loader->register(true);
		$loader->add($namespace, $dir);
	}



	/**
	 * @return \Enhancer\Loaders\ClassLoader|mixed
	 */
	protected function unregisterLoader()
	{
		if ($loader = array_pop($this->loaders)) {
			/** @var \Enhancer\Loaders\ClassLoader $loader */
			$loader->unregister();
		}

		return $loader;
	}



	public function tearDown()
	{
		while ($this->unregisterLoader());
	}

}
