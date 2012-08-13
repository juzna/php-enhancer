<?php
// Register enhancer stream wrapper
use Enhancer\EnhancerStream;

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require_once __DIR__ . '/../vendor/autoload.php';
$loader->add('Enhancer', __DIR__ . '/../src/');

// allow dump()
Nette\Diagnostics\Debugger::$productionMode = FALSE;

// Add new wrapper
if ( ! stream_wrapper_register('enhance', 'Enhancer\\EnhancerStream')) {
	throw new ErrorException("Unable to register enhancer stream");
}
