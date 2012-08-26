<?php
require_once __DIR__ . '/bootstrap.php';


$errors = \Enhancer\CompileHelper::compileDirs(
	Enhancer\EnhancerStream::$enhancer,
	__DIR__,
	array(
		__DIR__ . '/model',
		__DIR__ . '/tests'
	)
);
dump($errors); // FIXME: better
