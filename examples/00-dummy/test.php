<?php
require_once __DIR__ . '/../bootstrap.php';

// Setup enhancer
require_once __DIR__ . '/DummyEnhancer.php';
\Enhancer\EnhancerStream::$enhancer = new DummyEnhancer;


// Register autoloader
$loader = new \Enhancer\Loaders\ClassLoader;
$loader->register(true);
$loader->add('', __DIR__ . '/classes');


/*****************  tests  *****************j*d*/

$greeter = new Greeter;
$greeter->greet();
