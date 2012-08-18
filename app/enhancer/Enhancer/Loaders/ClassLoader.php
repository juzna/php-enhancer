<?php

namespace Enhancer\Loaders;

use Composer;

/**
 * Composer's autoloader with enhancing enabled
 *
 * @author Jan Dolecek <juzna.cz@gmail.com>
 */
class ClassLoader extends Composer\Autoload\ClassLoader
{

	public function findFile($class)
	{
		$ret = parent::findFile($class);
		return $ret ? "enhance://$ret" : $ret;
	}

}
