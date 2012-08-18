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
		$this->enhancerAutoload(__DIR__ . '/classes', 'GenericsExample');
	}



	/***/
	public function test()
	{
		$this->fail();
	}

}
