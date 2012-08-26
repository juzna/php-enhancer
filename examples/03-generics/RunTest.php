<?php

namespace GenericsExample;

use Enhancer\Generics\GenericsEnhancer;


require_once __DIR__ . '/bootstrap.php';


/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @runTestsInSeparateProcesses
 */
class RunTest extends \Tests\TestCase
{

	public function setUp()
	{
//		\Enhancer\EnhancerStream::$enhancer = new \GenericsEnhancer();
	}



	/*****************  compile-tests  *****************j*d*/

	/**
	 * @return array
	 */
	public function dataCompiledOutput_testsDirectory()
	{
		$allCases = array();

		$tests = \Nette\Utils\Finder::findFiles("*.phpt")->from(__DIR__ . '/compile-tests');
		foreach ($tests as $file) {
			/** @var \SplFileInfo $file */

			// parse
			$cases = self::parseCompilerTestFile($file->getRealPath());
			$this->assertNotEmpty($cases, "Test case {$file->getRealPath()} is empty.");

			// merge
			$allCases = array_merge($allCases, $cases);
		}

		return $allCases;
	}



	/**
	 * @dataProvider dataCompiledOutput_testsDirectory
	 */
	public function testCompiledOutput_testsDirectory($input, $expectedOutput)
	{
		$enhancer = new GenericsEnhancer();
		$output = $enhancer->enhance($input);
		$this->assertSame($expectedOutput, $output);
	}



	/*****************  semantic tests  *****************j*d*/

	/**
	 * @return array
	 */
	public function dataRunUsages_Compiled()
	{
//		\Enhancer\EnhancerStream::$enhancer = new GenericsEnhancer();
		if ($errors = $this->compileUsages()) {
			// return $errors;
			// TODO: throw or run at least those tests which compiled sucessfully?
		}

		$tests = array();
		foreach (glob(__DIR__ . '/output/tests/*.php') as $test) {
			$tests[basename($test)] = array($test);
		}

		return $tests;
	}



	/**
	 * @dataProvider dataRunUsages_Compiled
	 * @param string $usageCase
	 * @throws \Exception
	 */
	public function testRunUsages_Compiled($usageCase)
	{
		if ($usageCase instanceof \Exception) {
			throw $usageCase;
		}

		\Enhancer\EnhancerStream::$debug = TRUE;

		$this->autoloadCompiled(__DIR__ . '/output', 'GenericsExample');
		$this->safelyIncludeFile($usageCase);
	}



	/**
	 * @return array
	 */
	public function dataRunUsages_Live()
	{
		\Enhancer\EnhancerStream::$enhancer = new GenericsEnhancer();

		$tests = array();
		foreach (glob(__DIR__ . '/tests/*.php') as $test) {
			$tests[basename($test)] = array($test);
		}

		return $tests;
	}



	/**
	 * @dataProvider dataRunUsages_Live
	 *
	 * @param string $usageCase
	 *
	 * @throws \Exception
	 */
	public function testRunUsages_Live($usageCase)
	{
		\Enhancer\EnhancerStream::$debug = TRUE;

		$this->enhancerAutoload(__DIR__, 'GenericsExample');
		$this->safelyIncludeFile("enhance://$usageCase");
	}



	/**
	 * Crawls the GenericsExample directory and translates all the files.
	 */
	private static function compileUsages()
	{
		return \Enhancer\CompileHelper::compileDirs(
					\Enhancer\EnhancerStream::$enhancer,
					array(
						__DIR__ . '/model',
						__DIR__ . '/tests'
					)
				);
	}

}
