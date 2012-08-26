<?php

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require_once __DIR__ . '/../vendor/autoload.php';
$loader->add('Enhancer', __DIR__ . '/../src/');
$loader->add('Tests', __DIR__ . '/../src/');
// NOTE: global $loader variable can be used by custom bootstrap scripts per example

// allow dump()
Nette\Diagnostics\Debugger::$strictMode = TRUE;
Nette\Diagnostics\Debugger::$productionMode = FALSE;
Nette\Diagnostics\Debugger::$maxLen = 10000;

// Add new wrapper
if ( ! stream_wrapper_register('enhance', 'Enhancer\\EnhancerStream')) {
	throw new ErrorException("Unable to register enhancer stream");
}
