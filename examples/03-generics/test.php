<?php
define('DO_TRANSLATE', true);
define('AUTOLOAD_TRANSLATED', true);


require_once __DIR__ . '/../bootstrap.php';

// Setup enhancer
require_once __DIR__ . '/GenericsEnhancer.php';
Enhancer\EnhancerStream::$enhancer = new GenericsEnhancer;

// Register autoloader
if (AUTOLOAD_TRANSLATED) { // autoload translated files instead of direct compilation (debugging)
	$loader = new \Composer\Autoload\ClassLoader();
	$loader->register(true);
	$loader->add('', __DIR__ . '/output/classes');

} else {
	$loader = new Enhancer\Loaders\ClassLoader;
	$loader->register(true);
	$loader->add('', __DIR__ . '/classes');
}


/*****************  translate  *****************j*d*/

if (DO_TRANSLATE) {
	// translate and save all files (for debugging)
	foreach(Nette\Utils\Finder::findFiles("*.php")->from(__DIR__ . '/usage', __DIR__ . '/classes') as /** @var SplFileInfo $file */ $file) {
		$relativePath = substr($file->getRealPath(), strlen(__DIR__) + 1);
		$outputPath = __DIR__ . '/output/' . $relativePath;

	//	echo "$relativePath -> $outputPath\n";

		@mkdir(dirname($outputPath), 0777, true);
		file_put_contents($outputPath, file_get_contents("enhance://{$file->getRealPath()}"));
	}
}


/*****************  globals  *****************j*d*/
$em = NULL;


/*****************  tests  *********************/

echo "Running tests... \n";

foreach (glob(__DIR__ . '/usage/*.php') as $test) {
	echo "- running " , basename($test), "\n\n";
	include_once "enhance://$test";
}

echo "\n\n", "done", "\n";
