<?php

/**
 * My Application bootstrap file.
 */
use Nette\Application\Routers\Route;


// Load Nette Framework
require LIBS_DIR . '/autoload.php';


// Configure application
$configurator = new Nette\Config\Configurator;

// Enable Nette Debugger for error visualisation & logging
//$configurator->setDebugMode($configurator::AUTO);
$configurator->enableDebugger(__DIR__ . '/../log');

// Enable RobotLoader - this will load all classes automatically
$configurator->setTempDirectory(__DIR__ . '/../temp');
//$configurator->createRobotLoader()
//	->addDirectory(APP_DIR . '/enhancer')
//	->addDirectory(APP_DIR . '/presenters')
//	->addDirectory(LIBS_DIR)
//	->register();
require_once LIBS_DIR . '/juzna/php-enhancer/examples/05-ladyphp/lady.php';
require_once LIBS_DIR . '/juzna/php-enhancer/examples/05-ladyphp/LadyPhpEnhancer.php';


// Enhanced loader
if ( ! stream_wrapper_register('enhance', 'Enhancer\\EnhancerStream')) {
	throw new ErrorException("Unable to register enhancer stream");
}

Enhancer\EnhancerStream::$enhancer = new LadyPhpEnhancer;
$loader = new Enhancer\Loaders\ClassLoader;
$loader->register(true);
$loader->add('', __DIR__ . '/model');
$loader->add('', __DIR__ . '/presenters');


// Create Dependency Injection container from config.neon file
$configurator
	->addConfig(__DIR__ . '/config/config.neon', FALSE)
	->addConfig(__DIR__ . '/config/config.local.neon', FALSE);
$container = $configurator->createContainer();

// Setup router
$container->router[] = new Route('index.php', 'Homepage:default', Route::ONE_WAY);
$container->router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');


// Configure and run the application!
$container->application->run();
