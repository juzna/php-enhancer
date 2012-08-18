<?php

namespace Enhancer\Loaders;

use Composer;

/**
 * @internal
 */
class CompiledClassLoader extends Composer\Autoload\ClassLoader
{

	public function findFile($class)
	{
		$ret = parent::findFile($class);
		if ($ret && file_exists($ret) && ($e = \Enhancer\Utils\PhpSyntax::check($ret))) {
			throw $e;
		}
		return $ret;
	}

}
