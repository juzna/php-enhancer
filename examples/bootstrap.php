<?php
// Register enhancer stream wrapper
use Enhancer\EnhancerStream;

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require_once __DIR__ . '/../vendor/autoload.php';
$loader->add('Enhancer', __DIR__ . '/../src/');


// Load and start PHP enhancer
//require_once __DIR__ . '/../src/Enhancer/EnhancerStream.php';
//require_once __DIR__ . '/../src/Enhancer/Enhancer.php';

// NOTE: do this in a particular example
// EnhancerStream::$enhancer = new Enhancer\Enhancers\DemoEnhancer;

// Add new wrapper
if ( ! stream_wrapper_register('enhance', 'Enhancer\\EnhancerStream')) {
	throw new ErrorException("Unable to register enhancer stream");
}
