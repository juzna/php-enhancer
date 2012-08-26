<?php
require_once __DIR__ . '/../bootstrap.php';

// Setup enhancer
$loader = new \Composer\Autoload\ClassLoader;
$loader->register();
$loader->add('Enhancer\Generics', __DIR__ . '/src/');

Enhancer\EnhancerStream::$enhancer = new Enhancer\Generics\GenericsEnhancer;



// Register autoloader
$loader = new \Enhancer\Loaders\ClassLoader;
$loader->register(true);
$loader->add('GenericsExample', __DIR__ . '/model');


