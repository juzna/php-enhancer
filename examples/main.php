<?php
/**
 * Main file, prepares enhancer and executes the app with magic syntax in it
 */

// Load and start PHP enhancer
require_once __DIR__ . '/enhancer/EnhancerStream.php';
require_once __DIR__ . '/enhancer/Enhancer.php';

EnhancerStream::$enhancer = new Enhancer;

// Add new wrapper
stream_wrapper_register('enhance', 'EnhancerStream') or die("Unable to register enhancer stream");

// Register it for include/require (fwrapper extension)
function enhancer_stream_wrapper($file) {
	if (!preg_match('~^enhance://~', $file)) return "enhance://$file";
}
fwrapper_register('enhancer_stream_wrapper');



// Run the app with magic syntax
include __DIR__ . '/includes/01.php';
