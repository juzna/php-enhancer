<?php

namespace Enhancer\Builder\Php;
use Nette\Utils\PhpGenerator\Property;
use Nette\Utils\PhpGenerator\Method;


/**
 * Helps building PHP code
 *
 * @author Jan Dolecek <juzna.cz@gmail.com>
 */
class PhpBuilder extends \Enhancer\Builder\Builder
{

	public function createProperty($name)
	{
		$p = new Property;
		$p->setName($name);
		return $p;
	}

	public function createMethod($name)
	{
		$m = new Method;
		$m->setName($name);
		return $m;
	}

}
