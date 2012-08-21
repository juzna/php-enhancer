<?php

namespace ORM;

/**
 * Base entity class
 *
 * @author Jan Dolecek <juzna.cz@gmail.com>
 */
class Entity
{


	/*****************  these will generate some code  *****************j*d*/

	public static function attr(\Enhancer\Builder\Php\PhpBuilder $builder, $name, $type)
	{
		$ucName = ucfirst($name);
		if (self::isScalarType($type)) $type = NULL;

		return array(
			$builder
				->createProperty($name)
				->setVisibility('private'),

			$builder
				->createMethod("get$ucName")
				->setVisibility('public')
				->setBody("return \$this->$name;"),

			$builder
				->createMethod("set$ucName")
				->setVisibility('public')
				// ->addParameter($name) FIXME: should be here
				->setBody("
					\$this->$name = \$$name;
					return \$this;
				"),
		);
	}

	private static function isScalarType($type)
	{
		return in_array($type, array('int', 'float', 'string', 'bool'));
	}

}
