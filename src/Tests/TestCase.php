<?php

namespace Tests;

use Nette;
use Nette\Diagnostics\Debugger;



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
	 * @param \PHPUnit_Framework_TestResult $result
	 *
	 * @return \PHPUnit_Framework_TestResult
	 */
	public function run(\PHPUnit_Framework_TestResult $result = NULL)
	{
		$this->setPreserveGlobalState(false);
		return parent::run($result);
	}



	/**
	 * @param string $dir
	 * @param string $namespace
	 */
	protected function autoloadCompiled($dir, $namespace = '')
	{
		// Register autoloader
		$this->loaders[] = $loader = new \Enhancer\Loaders\CompiledClassLoader();
		$loader->register(true);
		$loader->add($namespace, $dir);
	}



	/**
	 * @param string $dir
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



	/**
	 * @param string $file
	 *
	 * @throws \Nette\InvalidStateException
	 * @return array
	 */
	protected static function parseCompilerTestFile($file)
	{
		$cases = $case = array();
		foreach (explode('<?php', file_get_contents($file)) as $part) {
			if (substr($part, 0, 2) === '#e') {
				list($input) = explode('?>', substr($part, 2), 2);
				$case[0] = '<?php' . $input;

			} elseif (substr($part, 0, 2) === '#c') {
				list($output) = explode('?>', substr($part, 2), 2);
				$case[1] = '<?php' . $output;

				if (!isset($case[0])) {
					throw new \Nette\InvalidStateException("Invalid test file"); // todo: verbose?
				}

				$cases[basename($file) . '#' . count($cases)] = $case;
				$case = array();
			}
		}

		return $cases;
	}



	/**
	 * @param string $includeFile
	 *
	 * @throws \ErrorException
	 * @throws \Exception
	 */
	protected static function safelyIncludeFile($includeFile)
	{
		Debugger::$strictMode = TRUE;
		Debugger::tryError();

		try {
			Nette\Utils\LimitedScope::load($includeFile);
		} catch (\Exception $e) { }

		if (Debugger::catchError($error)) {
			/** @var \ErrorException  $error */
			throw $error;

		} elseif (isset($e)) {
			throw $e;
		}
	}

}
