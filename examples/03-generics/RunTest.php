<?php

namespace GenericsExample;



if (!defined('NETTE')) {
	require_once __DIR__ . '/../bootstrap.php';
}
require_once __DIR__ . '/GenericsEnhancer.php';

/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @runTestsInSeparateProcesses
 */
class RunTest extends \Tests\TestCase
{

	public function setUp()
	{
		\Enhancer\EnhancerStream::$enhancer = new \GenericsEnhancer();
	}



	/**
	 * @return array
	 */
	public function dataCompiledOutput_testsDirectory()
	{
		$allCases = array();

		$tests = \Nette\Utils\Finder::findFiles("*.phpt")->from(__DIR__ . '/tests');
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
		$enhancer = new \GenericsEnhancer();
		$output = $enhancer->enhance($input);
		$this->assertSame($expectedOutput, $output);
	}



	/**
	 * @return array
	 */
	public function dataRunUsages_Compiled()
	{
		\Enhancer\EnhancerStream::$enhancer = new \GenericsEnhancer();
		if ($errors = $this->compileUsages()) {
			return $errors;
		}
		$this->autoloadCompiled(__DIR__ . '/output', 'GenericsExample');

		$tests = array();
		foreach (glob(__DIR__ . '/output/GenericsExample/usage/*.php') as $test) {
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
		$this->safelyIncludeFile($usageCase);
	}



	/**
	 * @return array
	 */
	public function dataRunUsages_Live()
	{
		\Enhancer\EnhancerStream::$enhancer = new \GenericsEnhancer();
		$this->enhancerAutoload(__DIR__, 'GenericsExample');

		$tests = array();
		foreach (glob(__DIR__ . '/GenericsExample/usage/*.php') as $test) {
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
		$this->safelyIncludeFile("enhance://$usageCase");
	}



	/**
	 * Crawls the GenericsExample directory and translates all the files.
	 */
	private static function compileUsages()
	{
		$errors = array();

		$usages = \Nette\Utils\Finder::findFiles("*.php")->from(__DIR__ . '/GenericsExample');
		foreach ($usages as $file) {
			/** @var \SplFileInfo $file */

			$outputPath = __DIR__ . '/output/' . str_replace(__DIR__ . '/', '', $file->getRealPath());
			if (self::isFresh($file, $outputPath)) {
					continue; // if newer, not compile again
			}

			@mkdir(dirname($outputPath), 0777, true);
			file_put_contents($outputPath, file_get_contents('enhance://' . $file->getRealPath()));

			if ($e = \Enhancer\Utils\PhpSyntax::check($outputPath)) {
				$errors[] = array($e);
			}
		}

		return $errors;
	}



	/**
	 * @param \SplFileInfo $file
	 * @param string $outputPath
	 *
	 * @return bool
	 */
	private static function isFresh(\SplFileInfo $file, $outputPath)
	{
		$enhancerRefl = new \ReflectionClass('GenericsEnhancer');
		return file_exists($outputPath)
			&& filemtime($outputPath) > filemtime($file->getRealPath())
			&& filemtime($outputPath) > filemtime($enhancerRefl->getFileName());
	}

}
