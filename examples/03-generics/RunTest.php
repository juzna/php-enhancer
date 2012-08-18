<?php

namespace GenericsExample;

use Nette\Diagnostics\Debugger;



require_once __DIR__ . '/GenericsEnhancer.php';

/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
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
	 * @param string $file
	 *
	 * @throws \Nette\InvalidStateException
	 * @return array
	 */
	private static function parseCompilerTestFile($file)
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
		$this->runUsage("enhance://$usageCase");
	}



	/**
	 * @return array
	 */
	public function dataRunUsages_Compiled()
	{
		\Enhancer\EnhancerStream::$enhancer = new \GenericsEnhancer();
		$this->compileUsages();
		$this->enhancerAutoload(__DIR__ . '/output', 'GenericsExample');

		$tests = array();
		foreach (glob(__DIR__ . '/output/GenericsExample/usage/*.php') as $test) {
			$tests[basename($test)] = array($test);
		}

		return $tests;
	}



	/**
	 * @dataProvider dataRunUsages_Compiled
	 *
	 * @param string $usageCase
	 *
	 * @throws \Exception
	 */
	public function testRunUsages_Compiled($usageCase)
	{
		$this->runUsage($usageCase);
	}



	/**
	 * @param string $includeFile
	 * @throws \Exception
	 */
	private function runUsage($includeFile)
	{
		Debugger::$strictMode = TRUE;
		Debugger::tryError();

		try {
			include_once $includeFile;

		} catch (\Exception $e) {
		}

		if (Debugger::catchError($error)) {
			/** @var \ErrorException  $error */
			throw $error;

		} elseif (isset($e)) {
			throw $e;
		}
	}



	/**
	 * Crawls the GenericsExample directory and translates all the files.
	 */
	private function compileUsages()
	{
		$usages = \Nette\Utils\Finder::findFiles("*.php")->from(__DIR__ . '/GenericsExample');
		foreach ($usages as $file) {
			/** @var \SplFileInfo $file */

			$outputPath = __DIR__ . '/output/' . str_replace(__DIR__ . '/', '', $file->getRealPath());
			if (self::isFresh($file, $outputPath)) {
					continue; // if newer, not compile again
			}

			@mkdir(dirname($outputPath), 0777, true);
			file_put_contents($outputPath, file_get_contents('enhance://' . $file->getRealPath()));
		}
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
