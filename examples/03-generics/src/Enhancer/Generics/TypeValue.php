<?php

namespace Enhancer\Generics;

/**
 * Particular instance of TypeArgument
 */
class TypeValue extends TypeArgument
{
	public $actualClass;



	public static function createValue(TypeArgument $arg, $actualClass)
	{
		if ( ! $arg->matches($actualClass)) throw new \InvalidArgumentException("Incompatible type");

		$ret = new self($arg->name, $arg->className, $arg->extends);
		$ret->actualClass = $actualClass;

		return $ret;
	}

}
