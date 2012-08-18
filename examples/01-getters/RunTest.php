<?php

namespace GettersExample;



require_once __DIR__ . '/GettersEnhancer.php';

/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class RunTest extends \Tests\TestCase
{

	public function setUp()
	{
		\Enhancer\EnhancerStream::$enhancer = new \GettersEnhancer();
		$this->enhancerAutoload(__DIR__, 'GettersExample');
	}



	public function test()
	{
		$movie = new Movie("The Dark Night Returns", 10);

		// methods generated by enhancer
		$this->assertSame("The Dark Night Returns", $movie->getName());
		$this->assertSame(10, $movie->getRating());

		// methods are real
		$this->assertSame(array(
			'getName',
			'setName',
			'getRating',
			'setRating',
			'__construct'
		), get_class_methods($movie));
	}

}
