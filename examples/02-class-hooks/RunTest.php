<?php

namespace ClassHookExample;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class RunTest extends \Tests\TestCase
{

	public function setUp()
	{
		require_once __DIR__ . '/ClassHookEnhancer.php';
		\Enhancer\EnhancerStream::$enhancer = new \ClassHookEnhancer();
		$this->enhancerAutoload(__DIR__ , 'ClassHookExample');
	}



	public function test()
	{
		ob_start();
		$movie = MovieFactory::createMovie("The Dark Night Returns", 10);
		$log = ob_get_clean();

		$this->assertInstanceOf('ClassHookExample\Movie', $movie);
		$this->assertSame(
			"LOG: Creating instance of 'ClassHookExample\\Movie' with 2 arguments\n" .
			"LOG: ... done\n",
			$log
		);

		$this->assertSame("The Dark Night Returns", $movie->name);
		$this->assertSame(10, $movie->rating);
	}

}
