<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/GenericsEnhancer.php';

\Enhancer\EnhancerStream::$enhancer = new \GenericsEnhancer();

$loader = new \Enhancer\Loaders\ClassLoader;
$loader->register(true);
$loader->add('GenericsExample', __DIR__);
//$loader->add('', __DIR__ . '/GenericsExample/classes');

//$file = $argv[1];
//echo "Running file $file\n";

//include 'enhance://' . __DIR__ . '/GenericsExample/usage/test01.php';
//include __DIR__ . '/output/GenericsExample/usage/test01.php';

