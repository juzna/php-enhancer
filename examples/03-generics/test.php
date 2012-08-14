<?php

require_once __DIR__ . '/../bootstrap.php';

// Setup enhancer
require_once __DIR__ . '/GenericsEnhancer.php';
Enhancer\EnhancerStream::$enhancer = new GenericsEnhancer;

// Register autoloader
$loader = new Enhancer\Loaders\ClassLoader;
$loader->register(true);
$loader->add('', __DIR__ . '/classes');


/*****************  translate  *****************j*d*/

echo "Translating files... \n";

@mkdir($dstDir = __DIR__ . '/output/');
foreach (glob(__DIR__ . '/usage/*.php') as $test) {
	file_put_contents($dstDir . basename($test), file_get_contents("enhance://$test"));
}


/*****************  tests  *********************/

echo "Running tests... \n";

foreach (glob(__DIR__ . '/usage/*.php') as $test) {
	echo "- running " , basename($test), "\n\n";
	include_once "enhance://$test";
}

echo "\n\n", "done", "\n";
