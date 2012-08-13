<?php
require_once __DIR__ . '/../bootstrap.php';

// Setup enhancer
require_once __DIR__ . '/ClassHookEnhancer.php';
\Enhancer\EnhancerStream::$enhancer = new ClassHookEnhancer;


// Register autoloader
$loader = new \Enhancer\Loaders\ClassLoader;
$loader->register(true);
$loader->add('', __DIR__ . '/classes');

// echo file_get_contents("enhance://" . __DIR__ . '/classes/MovieFactory.php');


/*****************  hooks  *****************j*d*/


class ClassHookHelper
{
	public static function newInstance($className /*, $args */)
	{
		$args = func_get_args();
		array_shift($args);
		$cnt = count($args);

		echo "LOG: Creating instance of '$className' with $cnt arguments\n";

		$refl = new ReflectionClass($className);
		$ret = $refl->newInstanceArgs($args);

		echo "LOG: ... done\n";

		return $ret;
	}
}


/*****************  tests  *****************j*d*/


$movie = MovieFactory::createMovie("The Dark Night Returns", 10);
dump($movie);
