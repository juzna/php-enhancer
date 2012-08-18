<?php

namespace GenericsExample;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class RunTest extends \Tests\TestCase
{

	public function setUp()
	{
		require_once __DIR__ . '/GenericsEnhancer.php';
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
				$case[0] = $input;

			} elseif (substr($part, 0, 2) === '#c') {
				list($output) = explode('?>', substr($part, 2), 2);
				$case[1] = $output;

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
		$enhancer = \Enhancer\EnhancerStream::$enhancer;
		$this->assertSame($expectedOutput, $enhancer->enhance($input));
	}



	public function testRunUsages()
	{
		$this->compileUsages();
		$this->fail();

		$this->enhancerAutoload(__DIR__ . '/output', 'GenericsExample');
		$this->enhancerAutoload(__DIR__, 'GenericsExample');

		echo "Running tests... \n";

		foreach (glob(__DIR__ . '/usage/*.php') as $test) {
			echo "- running ", basename($test), "\n\n";

			if (RUN_TRANSLATED) {
				include_once __DIR__ . '/output/' . substr($test, strlen(__DIR__) + 1);

			} else {
				include_once "enhance://$test";
			}
		}

		echo "\n\n", "done", "\n";
	}



	/**
	 * Crawls the GenericsExample directory and translates all the files.
	 */
	protected function compileUsages()
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
		$enhancerRefl = new \ReflectionClass(\Enhancer\EnhancerStream::$enhancer);
		return file_exists($outputPath)
			&& filemtime($outputPath) > filemtime($file->getRealPath())
			&& filemtime($outputPath) > filemtime($enhancerRefl->getFileName());
	}

}
