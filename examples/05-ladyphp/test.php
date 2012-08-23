<?php
require_once __DIR__ . '/../bootstrap.php';

// Setup enhancer
require_once __DIR__ . '/lady.php';
require_once __DIR__ . '/LadyPhpEnhancer.php';
\Enhancer\EnhancerStream::$enhancer = new LadyPhpEnhancer;


// Register autoloader
$loader = new \Enhancer\Loaders\ClassLoader;
$loader->register(true);
$loader->add('', __DIR__ . '/classes');


/*****************  tests  *****************j*d*/


$movie = new Movie("The Dark Night Returns", 10);
var_dump($movie->name, $movie->rating);


$fruit = new Fruit;
$fruit
	->addApples(1)
	->addApples(2);
echo $fruit->countApples(), "\n";
